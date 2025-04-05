<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $startDate = isset($_POST['startDate']) ? cleanInput($_POST['startDate']) : null;
    $endDate = isset($_POST['endDate']) ? cleanInput($_POST['endDate']) : null;
    $category = isset($_POST['category']) ? (int)$_POST['category'] : null;

    // Construction de la requête de base
    $query = "SELECT v.*, c.name as category_name 
              FROM vehicles v 
              LEFT JOIN vehicle_categories c ON v.category_id = c.id 
              WHERE v.is_available = true";
    $params = [];

    // Ajout des filtres
    if ($startDate && $endDate) {
        $query .= " AND v.id NOT IN (
            SELECT vehicle_id FROM reservations 
            WHERE (start_date <= :endDate AND end_date >= :startDate)
            AND status != 'cancelled'
        )";
        $params['startDate'] = $startDate;
        $params['endDate'] = $endDate;
    }

    if ($category) {
        $query .= " AND v.category_id = :category";
        $params['category'] = $category;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $vehicles]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
