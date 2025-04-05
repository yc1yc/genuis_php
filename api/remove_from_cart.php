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
    $cartIndex = isset($_POST['cart_index']) ? (int)$_POST['cart_index'] : null;

    if ($cartIndex === null) {
        throw new Exception('Index du panier manquant');
    }

    // Suppression de l'élément
    $result = removeFromCart($cartIndex);
    echo json_encode(formatSuccess('Article supprimé du panier', $result));

} catch (Exception $e) {
    echo json_encode(formatError($e->getMessage()));
}
