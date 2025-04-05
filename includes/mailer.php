<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Envoie un email avec PHPMailer
 */
function sendEmail($to, $subject, $body, $isHtml = true) {
    try {
        $mail = new PHPMailer(true);

        // Configuration du serveur
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // Expéditeur
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Destinataire
        $mail->addAddress($to);

        // Contenu
        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        // Version texte si HTML
        if ($isHtml) {
            $mail->AltBody = strip_tags($body);
        }

        // Envoi
        $mail->send();
        return true;
    } catch (Exception $e) {
        logError("Erreur d'envoi d'email : {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Génère un email de confirmation de réservation
 */
function generateBookingConfirmationEmail($reservation, $user) {
    $content = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #f8f9fa; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { text-align: center; padding: 20px; font-size: 0.9em; color: #666; }
            .details { margin: 20px 0; }
            .total { font-size: 1.2em; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Confirmation de réservation</h1>
            </div>
            
            <div class='content'>
                <p>Bonjour {$user['first_name']},</p>
                
                <p>Nous vous remercions pour votre réservation chez " . SITE_NAME . ". Voici le récapitulatif :</p>
                
                <div class='details'>
                    <h3>Détails de la réservation :</h3>
                    <p>Véhicule : {$reservation['vehicle_name']}</p>
                    <p>Du : " . formatDate($reservation['start_date']) . "</p>
                    <p>Au : " . formatDate($reservation['end_date']) . "</p>
                    " . (!empty($reservation['options']) ? "<p>Options : " . implode(', ', array_column($reservation['options'], 'name')) . "</p>" : "") . "
                    <p class='total'>Total : " . formatPrice($reservation['total_price']) . "</p>
                </div>
                
                <p>Votre numéro de réservation : <strong>{$reservation['id']}</strong></p>
                
                <p>Pour toute question, n'hésitez pas à nous contacter.</p>
            </div>
            
            <div class='footer'>
                <p>Cordialement,<br>L'équipe " . SITE_NAME . "</p>
            </div>
        </div>
    </body>
    </html>";

    return $content;
}
