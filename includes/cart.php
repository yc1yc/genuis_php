<?php
// Suppression de session_start() car il est déjà appelé dans index.php

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * Ajouter un véhicule au panier
 */
function addToCart($vehicleId, $startDate, $endDate, $options = []) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Vérifier si les dates ne se chevauchent pas avec une autre réservation
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM reservations 
        WHERE vehicle_id = :vehicle_id 
        AND ((start_date BETWEEN :start_date AND :end_date) 
        OR (end_date BETWEEN :start_date AND :end_date))
        AND status != 'cancelled'
    ");
    
    $stmt->execute([
        'vehicle_id' => $vehicleId,
        'start_date' => $startDate,
        'end_date' => $endDate
    ]);

    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Ce véhicule n'est pas disponible pour ces dates.");
    }

    // Calculer le nombre de jours
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $days = $end->diff($start)->days + 1;

    // Récupérer les informations du véhicule
    $stmt = $pdo->prepare("
        SELECT v.*, c.name as category_name 
        FROM vehicles v 
        LEFT JOIN vehicle_categories c ON v.category_id = c.id 
        WHERE v.id = :id
    ");
    $stmt->execute(['id' => $vehicleId]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicle) {
        throw new Exception("Véhicule non trouvé.");
    }

    // Calculer le prix de base
    $basePrice = $vehicle['price_per_day'] * $days;

    // Calculer le prix des options
    $optionsPrice = 0;
    $selectedOptions = [];
    if (!empty($options)) {
        $stmt = $pdo->prepare("SELECT * FROM options WHERE id IN (" . implode(',', array_fill(0, count($options), '?')) . ")");
        $stmt->execute($options);
        $selectedOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($selectedOptions as $option) {
            $optionsPrice += $option['price_per_day'] * $days;
        }
    }

    // Créer l'élément du panier
    $cartItem = [
        'id' => uniqid(),
        'vehicle_id' => $vehicleId,
        'vehicle_name' => $vehicle['brand'] . ' ' . $vehicle['model'],
        'category' => $vehicle['category_name'],
        'start_date' => $startDate,
        'end_date' => $endDate,
        'days' => $days,
        'base_price' => $basePrice,
        'options' => $selectedOptions,
        'options_price' => $optionsPrice,
        'total_price' => $basePrice + $optionsPrice,
        'image_url' => $vehicle['image_url']
    ];

    $_SESSION['cart'][] = $cartItem;
    return $cartItem;
}

/**
 * Supprimer un élément du panier
 */
function removeFromCart($cartItemId) {
    if (!isset($_SESSION['cart'])) return false;

    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] === $cartItemId) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Réindexer le tableau
            return true;
        }
    }
    return false;
}

/**
 * Vider le panier
 */
function clearCart() {
    $_SESSION['cart'] = [];
}

/**
 * Calculer le total du panier
 */
function getCartTotal() {
    if (!isset($_SESSION['cart'])) return 0;

    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['total_price'];
    }
    return $total;
}

/**
 * Obtenir le contenu du panier
 */
function getCart() {
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}
