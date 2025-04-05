<?php
require_once '../../includes/auth.php';

header('Content-Type: application/json');

try {
    // Vérification de l'authentification
    if (!isLoggedIn()) {
        throw new Exception('Vous devez être connecté');
    }

    // Vérification du jeton CSRF
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        throw new Exception('Token de sécurité invalide');
    }

    // Validation des données
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $newPasswordConfirm = $_POST['new_password_confirm'] ?? '';

    if (!$currentPassword || !$newPassword || !$newPasswordConfirm) {
        throw new Exception('Tous les champs sont obligatoires');
    }

    if ($newPassword !== $newPasswordConfirm) {
        throw new Exception('Les nouveaux mots de passe ne correspondent pas');
    }

    if (strlen($newPassword) < 8) {
        throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
    }

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $newPassword)) {
        throw new Exception('Le mot de passe doit contenir au moins une lettre et un chiffre');
    }

    // Vérification du mot de passe actuel
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        throw new Exception('Mot de passe actuel incorrect');
    }

    // Mise à jour du mot de passe
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    
    if (!$stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
        throw new Exception('Erreur lors de la mise à jour du mot de passe');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Mot de passe modifié avec succès'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
