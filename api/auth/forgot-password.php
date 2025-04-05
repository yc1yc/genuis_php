<?php
require_once '../../includes/auth.php';
require_once '../../includes/mailer.php';

header('Content-Type: application/json');

try {
    // Vérification du jeton CSRF
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        throw new Exception('Token de sécurité invalide');
    }

    // Validation de l'email
    $email = cleanInput($_POST['email'] ?? '');
    if (!$email || !validateEmail($email)) {
        throw new Exception('Email invalide');
    }

    // Génération du token
    $token = generatePasswordResetToken($email);
    if (!$token) {
        throw new Exception('Aucun compte trouvé avec cet email');
    }

    // Envoi de l'email
    $resetLink = SITE_URL . '/index.php?page=reset-password&token=' . $token;
    $emailContent = "
        Bonjour,<br><br>
        
        Vous avez demandé la réinitialisation de votre mot de passe sur " . SITE_NAME . ".<br>
        Cliquez sur le lien ci-dessous pour définir un nouveau mot de passe :<br><br>
        
        <a href='$resetLink'>Réinitialiser mon mot de passe</a><br><br>
        
        Ce lien est valable pendant 1 heure.<br>
        Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.<br><br>
        
        Cordialement,<br>
        L'équipe " . SITE_NAME;

    if (!sendEmail($email, "Réinitialisation de votre mot de passe", $emailContent)) {
        throw new Exception('Erreur lors de l\'envoi de l\'email');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Un email de réinitialisation vous a été envoyé'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
