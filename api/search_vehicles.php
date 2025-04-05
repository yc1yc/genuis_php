<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    // Validation des données
    $startDate = isset($_POST['startDate']) ? cleanInput($_POST['startDate']) : null;
    $endDate = isset($_POST['endDate']) ? cleanInput($_POST['endDate']) : null;
    $category = isset($_POST['category']) ? (int)$_POST['category'] : null;

    // Validation des dates
    if ($startDate && $endDate) {
        if (!validateDate($startDate) || !validateDate($endDate)) {
            throw new Exception('Dates invalides');
        }

        $start = new DateTime($startDate);
        $end = new DateTime($endDate);

        if ($start > $end) {
            throw new Exception('La date de début doit être antérieure à la date de fin');
        }
    }

    // Construction de la requête
    $query = "
        SELECT v.*, c.name as category_name 
        FROM vehicles v 
        LEFT JOIN vehicle_categories c ON v.category_id = c.id 
        WHERE v.is_available = true
    ";
    $params = [];

    // Ajout des filtres
    if ($startDate && $endDate) {
        $query .= " AND v.id NOT IN (
            SELECT vehicle_id 
            FROM reservations 
            WHERE status != 'cancelled'
            AND (
                (start_date BETWEEN ? AND ?)
                OR (end_date BETWEEN ? AND ?)
                OR (start_date <= ? AND end_date >= ?)
            )
        )";
        array_push($params, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate);
    }

    if ($category) {
        $query .= " AND v.category_id = ?";
        $params[] = $category;
    }

    $query .= " ORDER BY v.price_per_day ASC";

    // Exécution de la requête
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatage des résultats
    foreach ($vehicles as &$vehicle) {
        $vehicle['price_per_day'] = (float)$vehicle['price_per_day'];
        $vehicle['image_url'] = $vehicle['image_url'] ?? 'assets/images/default-vehicle.jpg';
    }

    echo json_encode(formatSuccess('Véhicules trouvés', [
        'vehicles' => $vehicles,
        'count' => count($vehicles)
    ]));

} catch (Exception $e) {
    echo json_encode(formatError($e->getMessage()));
}
