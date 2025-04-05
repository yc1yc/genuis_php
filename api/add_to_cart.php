<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/cart.php';

ensureSessionStarted();
header('Content-Type: application/json');

try {
    // Vérification du jeton CSRF
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        throw new Exception('Token de sécurité invalide');
    }

    // Validation des données
    $vehicleId = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null;
    $startDate = isset($_POST['start_date']) ? cleanInput($_POST['start_date']) : null;
    $endDate = isset($_POST['end_date']) ? cleanInput($_POST['end_date']) : null;
    $options = isset($_POST['options']) ? $_POST['options'] : [];

    if (!$vehicleId || !$startDate || !$endDate) {
        throw new Exception('Données manquantes');
    }

    if (!validateDate($startDate) || !validateDate($endDate)) {
        throw new Exception('Dates invalides');
    }

    // Vérification de la disponibilité
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM reservations 
        WHERE vehicle_id = ? 
        AND ((start_date BETWEEN ? AND ?) 
        OR (end_date BETWEEN ? AND ?))
    ");
    $stmt->execute([$vehicleId, $startDate, $endDate, $startDate, $endDate]);
    
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ce véhicule n\'est pas disponible pour ces dates');
    }

    // Ajout au panier
    $result = addToCart($vehicleId, $startDate, $endDate, $options);
    echo json_encode(formatSuccess('Véhicule ajouté au panier', $result));

} catch (Exception $e) {
    echo json_encode(formatError($e->getMessage()));
}
