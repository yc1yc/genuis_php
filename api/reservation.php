<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// Vérifier la session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Vous devez être connecté pour faire une réservation']);
    exit;
}

// Vérifier les données requises
if (empty($_POST['vehicle_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID du véhicule manquant']);
    exit;
}

if (empty($_POST['start_date'])) {
    echo json_encode(['success' => false, 'error' => 'Date de début manquante']);
    exit;
}

if (empty($_POST['end_date'])) {
    echo json_encode(['success' => false, 'error' => 'Date de fin manquante']);
    exit;
}

if (!verifyCsrfToken()) {
    echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
    exit;
}

try {
    $pdo = getPDO();
    
    // Vérifier si le véhicule existe et récupérer son prix
    $stmt = $pdo->prepare('SELECT id, price_per_day FROM vehicles WHERE id = ?');
    $stmt->execute([$_POST['vehicle_id']]);
    $vehicle = $stmt->fetch();
    
    if (!$vehicle) {
        echo json_encode(['success' => false, 'error' => 'Véhicule non trouvé']);
        exit;
    }

    // Vérifier si les dates sont valides
    $start_date = new DateTime($_POST['start_date']);
    $end_date = new DateTime($_POST['end_date']);
    $today = new DateTime();

    if ($start_date < $today) {
        echo json_encode(['success' => false, 'error' => 'La date de début doit être ultérieure à aujourd\'hui']);
        exit;
    }

    if ($end_date <= $start_date) {
        echo json_encode(['success' => false, 'error' => 'La date de fin doit être ultérieure à la date de début']);
        exit;
    }

    // Calculer le nombre de jours et le prix total
    $interval = $start_date->diff($end_date);
    $total_days = $interval->days + 1;
    $base_price = $vehicle['price_per_day'];
    $total_price = $base_price * $total_days;

    // Vérifier la disponibilité
    $stmt = $pdo->prepare('
        SELECT COUNT(*) FROM reservations 
        WHERE vehicle_id = ? 
        AND ((start_date BETWEEN ? AND ?) 
        OR (end_date BETWEEN ? AND ?)
        OR (start_date <= ? AND end_date >= ?))
        AND status != "cancelled"
    ');
    
    $stmt->execute([
        $_POST['vehicle_id'],
        $_POST['start_date'],
        $_POST['end_date'],
        $_POST['start_date'],
        $_POST['end_date'],
        $_POST['start_date'],
        $_POST['end_date']
    ]);

    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'Le véhicule n\'est pas disponible pour ces dates']);
        exit;
    }

    // Créer la réservation avec tous les champs requis
    $stmt = $pdo->prepare('
        INSERT INTO reservations (
            vehicle_id, 
            user_id, 
            start_date, 
            end_date, 
            pickup_time,
            return_time,
            total_days,
            base_price,
            total_price,
            status,
            payment_status,
            created_at
        ) VALUES (
            ?, ?, ?, ?, 
            "10:00:00",
            "18:00:00",
            ?,
            ?,
            ?,
            "pending",
            "pending",
            NOW()
        )
    ');

    $stmt->execute([
        $_POST['vehicle_id'],
        $_SESSION['user_id'],
        $_POST['start_date'],
        $_POST['end_date'],
        $total_days,
        $base_price,
        $total_price
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Réservation créée avec succès',
        'reservation' => [
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'total_days' => $total_days,
            'total_price' => $total_price
        ]
    ]);

} catch (Exception $e) {
    error_log('Reservation error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Une erreur est survenue lors de la réservation : ' . $e->getMessage()]);
}
