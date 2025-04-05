<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    // Récupération et nettoyage des données
    $name = cleanInput($_POST['name']);
    $email = cleanInput($_POST['email']);
    $phone = cleanInput($_POST['phone']);
    $subject = cleanInput($_POST['subject']);
    $message = cleanInput($_POST['message']);

    // Validation basique
    if (empty($name) || empty($email) || empty($message)) {
        throw new Exception('Veuillez remplir tous les champs obligatoires');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Adresse email invalide');
    }

    // Préparation du message pour l'envoi
    $to = "contact@thegenuis.com";
    $emailSubject = "Nouveau message de contact - " . $subject;
    $emailBody = "Nom: $name\n";
    $emailBody .= "Email: $email\n";
    $emailBody .= "Téléphone: $phone\n";
    $emailBody .= "Sujet: $subject\n\n";
    $emailBody .= "Message:\n$message";

    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Envoi de l'email
    if (mail($to, $emailSubject, $emailBody, $headers)) {
        // Enregistrement dans la base de données si nécessaire
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) 
                              VALUES (:name, :email, :phone, :subject, :message)");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message
        ]);

        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Erreur lors de l\'envoi du message');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
