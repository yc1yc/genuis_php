<?php
require_once '../../includes/auth.php';

header('Content-Type: application/json');

try {
    // Vérification du jeton CSRF
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        throw new Exception('Token de sécurité invalide');
    }

    // Validation des données
    $firstName = cleanInput($_POST['first_name'] ?? '');
    $lastName = cleanInput($_POST['last_name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $terms = isset($_POST['terms']);

    // Vérification des champs obligatoires
    if (!$firstName || !$lastName || !$email || !$password) {
        throw new Exception('Veuillez remplir tous les champs obligatoires');
    }

    // Validation de l'email
    if (!validateEmail($email)) {
        throw new Exception('Email invalide');
    }

    // Validation du téléphone si fourni
    if ($phone && !validatePhone($phone)) {
        throw new Exception('Numéro de téléphone invalide');
    }

    // Validation du mot de passe
    if (strlen($password) < 8) {
        throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
    }

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        throw new Exception('Le mot de passe doit contenir au moins une lettre et un chiffre');
    }

    if ($password !== $passwordConfirm) {
        throw new Exception('Les mots de passe ne correspondent pas');
    }

    // Vérification des conditions générales
    if (!$terms) {
        throw new Exception('Vous devez accepter les conditions générales');
    }

    $pdo = getPDO();

    // Vérification si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Cet email est déjà utilisé');
    }

    // Hashage du mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Création de l'utilisateur
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, phone, password, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$firstName, $lastName, $email, $phone, $hashedPassword]);
    $userId = $pdo->lastInsertId();

    // Attribution du rôle client
    $stmt = $pdo->prepare("
        INSERT INTO user_roles (user_id, role_id)
        SELECT ?, id FROM roles WHERE name = 'client'
    ");
    $stmt->execute([$userId]);

    // Connexion automatique
    authenticateUser($email, $password);

    // Envoi de l'email de bienvenue
    $emailContent = "
        Bonjour $firstName,<br><br>
        
        Bienvenue sur " . SITE_NAME . " !<br><br>
        
        Votre compte a été créé avec succès. Vous pouvez dès maintenant vous connecter 
        et profiter de nos services de location de véhicules.<br><br>
        
        Cordialement,<br>
        L'équipe " . SITE_NAME;

    sendEmail($email, "Bienvenue sur " . SITE_NAME, $emailContent);

    echo json_encode([
        'success' => true,
        'message' => 'Inscription réussie',
        'redirect' => 'index.php?page=account'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
