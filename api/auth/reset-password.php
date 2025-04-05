<?php
require_once '../../includes/auth.php';

header('Content-Type: application/json');

try {
    // Vérification du jeton CSRF
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        throw new Exception('Token de sécurité invalide');
    }

    // Validation des données
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (!$token || !$password || !$passwordConfirm) {
        throw new Exception('Tous les champs sont obligatoires');
    }

    if ($password !== $passwordConfirm) {
        throw new Exception('Les mots de passe ne correspondent pas');
    }

    if (strlen($password) < 8) {
        throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
    }

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        throw new Exception('Le mot de passe doit contenir au moins une lettre et un chiffre');
    }

    // Réinitialisation du mot de passe
    if (!resetPassword($token, $password)) {
        throw new Exception('Le lien de réinitialisation est invalide ou a expiré');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Mot de passe réinitialisé avec succès'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
