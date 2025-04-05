<?php
require_once '../../includes/auth.php';

header('Content-Type: application/json');

try {
    // Vérification du jeton CSRF
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        throw new Exception('Token de sécurité invalide');
    }

    // Validation des données
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (!$email || !$password) {
        throw new Exception('Veuillez remplir tous les champs');
    }

    if (!validateEmail($email)) {
        throw new Exception('Email invalide');
    }

    // Tentative de connexion
    if (!authenticateUser($email, $password, $remember)) {
        throw new Exception('Email ou mot de passe incorrect');
    }

    // Redirection après connexion
    $redirect = $_SESSION['redirect_after_login'] ?? 'index.php?page=account';
    unset($_SESSION['redirect_after_login']);

    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'redirect' => $redirect
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
