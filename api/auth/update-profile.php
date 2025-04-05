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
    $firstName = cleanInput($_POST['first_name'] ?? '');
    $lastName = cleanInput($_POST['last_name'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');

    if (!$firstName || !$lastName) {
        throw new Exception('Le nom et le prénom sont obligatoires');
    }

    if ($phone && !validatePhone($phone)) {
        throw new Exception('Numéro de téléphone invalide');
    }

    // Mise à jour du profil
    $pdo = getPDO();
    $stmt = $pdo->prepare("
        UPDATE users 
        SET first_name = ?, last_name = ?, phone = ?
        WHERE id = ?
    ");
    
    if (!$stmt->execute([$firstName, $lastName, $phone, $_SESSION['user_id']])) {
        throw new Exception('Erreur lors de la mise à jour du profil');
    }

    // Mise à jour de la session
    $_SESSION['user_name'] = $firstName . ' ' . $lastName;

    echo json_encode([
        'success' => true,
        'message' => 'Profil mis à jour avec succès'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
