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

    // Validation des données utilisateur
    $firstName = cleanInput($_POST['first_name'] ?? '');
    $lastName = cleanInput($_POST['last_name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $address = cleanInput($_POST['address'] ?? '');

    // Validation des champs obligatoires
    if (!$firstName || !$lastName || !$email) {
        throw new Exception('Veuillez remplir tous les champs obligatoires');
    }

    // Validation du format des données
    if (!validateEmail($email)) {
        throw new Exception('Adresse email invalide');
    }

    if ($phone && !validatePhone($phone)) {
        throw new Exception('Numéro de téléphone invalide');
    }

    // Vérification du panier
    $cart = getCart();
    if (empty($cart)) {
        throw new Exception('Votre panier est vide');
    }

    // Début de la transaction
    $pdo = getPDO();
    $pdo->beginTransaction();

    try {
        // Création ou mise à jour de l'utilisateur
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, phone, address)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            first_name = VALUES(first_name),
            last_name = VALUES(last_name),
            phone = VALUES(phone),
            address = VALUES(address)
        ");
        $stmt->execute([$firstName, $lastName, $email, $phone, $address]);
        
        $userId = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM users WHERE email = '$email'")->fetchColumn();

        // Création des réservations
        $reservations = [];
        foreach ($cart as $item) {
            // Vérification finale de la disponibilité
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM reservations 
                WHERE vehicle_id = ? 
                AND ((start_date BETWEEN ? AND ?) 
                OR (end_date BETWEEN ? AND ?))
            ");
            $stmt->execute([
                $item['vehicle_id'],
                $item['start_date'],
                $item['end_date'],
                $item['start_date'],
                $item['end_date']
            ]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Le véhicule {$item['vehicle_name']} n'est plus disponible pour les dates sélectionnées");
            }

            // Création de la réservation
            $stmt = $pdo->prepare("
                INSERT INTO reservations (
                    user_id, vehicle_id, start_date, end_date,
                    base_price, options_price, total_price, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $userId,
                $item['vehicle_id'],
                $item['start_date'],
                $item['end_date'],
                $item['base_price'],
                $item['options_price'],
                $item['total_price']
            ]);
            
            $reservationId = $pdo->lastInsertId();
            $reservations[] = $reservationId;

            // Ajout des options
            if (!empty($item['options'])) {
                $stmt = $pdo->prepare("
                    INSERT INTO reservation_options (
                        reservation_id, option_id, price_per_day, price
                    ) VALUES (?, ?, ?, ?)
                ");
                foreach ($item['options'] as $option) {
                    $stmt->execute([
                        $reservationId,
                        $option['id'],
                        $option['price_per_day'],
                        $option['price_per_day'] * $item['days']
                    ]);
                }
            }
        }

        // Envoi de l'email de confirmation
        $emailContent = "Bonjour $firstName $lastName,\n\n";
        $emailContent .= "Nous avons bien reçu votre réservation.\n\n";
        $emailContent .= "Récapitulatif de votre commande :\n";
        $emailContent .= formatCartEmailSummary($cart);
        $emailContent .= "\nNous vous contacterons prochainement pour finaliser votre réservation.\n\n";
        $emailContent .= "Cordialement,\n";
        $emailContent .= "L'équipe " . SITE_NAME;

        if (!sendEmail($email, "Confirmation de votre réservation - " . SITE_NAME, $emailContent)) {
            // Log l'erreur mais continue le processus
            logError("Échec de l'envoi de l'email de confirmation à $email");
        }

        // Vider le panier
        clearCart();

        // Valider la transaction
        $pdo->commit();

        echo json_encode(formatSuccess('Réservation effectuée avec succès', [
            'reservationIds' => $reservations,
            'redirect' => SITE_URL . '/confirmation.php?id=' . $reservations[0]
        ]));

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode(formatError($e->getMessage()));
}
