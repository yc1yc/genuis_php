<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/stripe.php';
require_once '../includes/functions.php';

// Récupération du payload
$payload = @file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'];

try {
    // Vérification de la signature
    $event = \Stripe\Webhook::constructEvent(
        $payload,
        $sigHeader,
        STRIPE_WEBHOOK_SECRET
    );

    // Récupération de la session de paiement
    $session = $event->data->object;
    $pdo = getPDO();

    switch ($event->type) {
        case 'checkout.session.completed':
            // Le paiement a réussi
            $cartId = $session->metadata->cart_id;
            $amount = $session->metadata->total_amount;

            // Mise à jour des réservations
            $stmt = $pdo->prepare("
                UPDATE reservations 
                SET payment_status = 'paid',
                    stripe_session_id = ?,
                    payment_date = NOW()
                WHERE cart_id = ?
            ");
            $stmt->execute([$session->id, $cartId]);

            // Envoi de l'email de confirmation
            $stmt = $pdo->prepare("
                SELECT r.*, u.email, u.first_name, u.last_name
                FROM reservations r
                JOIN users u ON r.user_id = u.id
                WHERE r.cart_id = ?
            ");
            $stmt->execute([$cartId]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($reservation) {
                $emailContent = "Bonjour {$reservation['first_name']},\n\n";
                $emailContent .= "Votre paiement a été confirmé pour la réservation suivante :\n\n";
                $emailContent .= "Montant total : " . formatPrice($amount) . "\n";
                $emailContent .= "Numéro de transaction : " . $session->payment_intent . "\n\n";
                $emailContent .= "Nous vous remercions de votre confiance.\n\n";
                $emailContent .= "Cordialement,\n";
                $emailContent .= "L'équipe " . SITE_NAME;

                sendEmail(
                    $reservation['email'],
                    "Confirmation de paiement - " . SITE_NAME,
                    $emailContent
                );
            }
            break;

        case 'checkout.session.expired':
            // La session de paiement a expiré
            $cartId = $session->metadata->cart_id;

            // Annulation des réservations
            $stmt = $pdo->prepare("
                UPDATE reservations 
                SET status = 'cancelled',
                    payment_status = 'expired'
                WHERE cart_id = ?
            ");
            $stmt->execute([$cartId]);
            break;
    }

    http_response_code(200);
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    logError("Erreur webhook Stripe : " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
