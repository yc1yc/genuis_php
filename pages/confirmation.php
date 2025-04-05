<?php
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$reservationId = (int)$_GET['id'];

// Récupérer les détails de la réservation
$stmt = $pdo->prepare("
    SELECT r.*, v.brand, v.model, v.image_url, u.first_name, u.last_name, u.email
    FROM reservations r
    JOIN vehicles v ON r.vehicle_id = v.id
    JOIN users u ON r.user_id = u.id
    WHERE r.id = :id
");
$stmt->execute(['id' => $reservationId]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    header('Location: index.php');
    exit;
}

// Récupérer les options de la réservation
$stmt = $pdo->prepare("
    SELECT o.name, o.price_per_day, ro.price
    FROM reservation_options ro
    JOIN options o ON ro.option_id = o.id
    WHERE ro.reservation_id = :id
");
$stmt->execute(['id' => $reservationId]);
$options = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="confirmation-page">
    <div class="hero success">
        <div class="hero-content">
            <i class="fas fa-check-circle"></i>
            <h1>Réservation confirmée</h1>
            <p>Merci de votre confiance !</p>
        </div>
    </div>

    <div class="main-content">
        <div class="confirmation-details">
            <div class="reservation-summary">
                <h2>Récapitulatif de votre réservation</h2>
                <div class="summary-card">
                    <div class="vehicle-info">
                        <img src="<?php echo htmlspecialchars($reservation['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']); ?>">
                        <div class="vehicle-details">
                            <h3><?php echo htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']); ?></h3>
                            <div class="dates">
                                <p>
                                    <strong>Du :</strong> 
                                    <?php echo date('d/m/Y', strtotime($reservation['start_date'])); ?>
                                </p>
                                <p>
                                    <strong>Au :</strong> 
                                    <?php echo date('d/m/Y', strtotime($reservation['end_date'])); ?>
                                </p>
                                <p>
                                    <strong>Durée :</strong> 
                                    <?php echo $reservation['total_days']; ?> jours
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($options)): ?>
                        <div class="options-info">
                            <h4>Options sélectionnées</h4>
                            <ul>
                                <?php foreach ($options as $option): ?>
                                    <li>
                                        <?php echo htmlspecialchars($option['name']); ?> 
                                        (<?php echo number_format($option['price'], 2); ?>€)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="price-summary">
                        <div class="price-line">
                            <span>Location du véhicule</span>
                            <span><?php echo number_format($reservation['base_price'], 2); ?>€</span>
                        </div>
                        <?php if ($reservation['options_price'] > 0): ?>
                            <div class="price-line">
                                <span>Options</span>
                                <span><?php echo number_format($reservation['options_price'], 2); ?>€</span>
                            </div>
                        <?php endif; ?>
                        <div class="price-line total">
                            <span>Total</span>
                            <span><?php echo number_format($reservation['total_price'], 2); ?>€</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="next-steps">
                <h2>Prochaines étapes</h2>
                <div class="steps-grid">
                    <div class="step">
                        <i class="fas fa-envelope"></i>
                        <h3>Email de confirmation</h3>
                        <p>Un email de confirmation a été envoyé à <?php echo htmlspecialchars($reservation['email']); ?></p>
                    </div>
                    <div class="step">
                        <i class="fas fa-credit-card"></i>
                        <h3>Paiement</h3>
                        <p>Le paiement sera traité selon le mode choisi lors de la réservation.</p>
                    </div>
                    <div class="step">
                        <i class="fas fa-phone"></i>
                        <h3>Contact</h3>
                        <p>Notre équipe vous contactera prochainement pour confirmer les détails.</p>
                    </div>
                </div>
            </div>

            <div class="actions">
                <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Imprimer la confirmation
                </button>
            </div>
        </div>
    </div>
</main>

<style>
@media print {
    .header, .footer, .actions {
        display: none;
    }
    .confirmation-details {
        padding: 0;
    }
    .hero {
        background: none;
        color: #000;
    }
}
</style>
