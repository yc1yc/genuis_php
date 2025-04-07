<?php
require_once __DIR__ . '/../includes/auth.php';

// Vérifier que l'utilisateur est connecté
requireAuth();

// Récupérer l'ID de l'utilisateur connecté
$userId = $_SESSION['user_id'];

try {
    $pdo = getPDO();
    
    // Récupérer toutes les réservations de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT r.*, v.brand, v.model, v.year
        FROM reservations r
        JOIN vehicles v ON r.vehicle_id = v.id
        WHERE r.user_id = ?
        ORDER BY r.start_date DESC
    ");
    $stmt->execute([$userId]);
    $reservations = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['flash_error'] = "Erreur lors de la récupération des réservations.";
    $reservations = [];
}
?>

<div class="reservations-container">
    <div class="reservations-header">
        <h1><i class="fas fa-calendar-alt"></i> Mes réservations</h1>
        <a href="?page=vehicles" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle réservation
        </a>
    </div>

    <?php echo displayFlashMessages(); ?>

    <?php if (empty($reservations)): ?>
        <div class="alert alert-info">
            <p>Vous n'avez pas encore de réservations. Cliquez sur "Nouvelle réservation" pour commencer.</p>
        </div>
    <?php else: ?>
        <div class="reservations-grid">
            <?php foreach ($reservations as $reservation): ?>
                <div class="reservation-card">
                    <div class="reservation-header">
                        <h3><?php echo htmlspecialchars($reservation['brand'] . ' ' . $reservation['model'] . ' ' . $reservation['year']); ?></h3>
                        <span class="reservation-status status-<?php echo htmlspecialchars($reservation['status']); ?>">
                            <?php echo ucfirst(htmlspecialchars($reservation['status'])); ?>
                        </span>
                    </div>
                    <div class="reservation-details">
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-calendar"></i> Début :</span>
                            <span class="value"><?php echo date('d/m/Y', strtotime($reservation['start_date'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-calendar-check"></i> Fin :</span>
                            <span class="value"><?php echo date('d/m/Y', strtotime($reservation['end_date'])); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-users"></i> Nombre de jours :</span>
                            <span class="value"><?php echo htmlspecialchars($reservation['total_days']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label"><i class="fas fa-euro-sign"></i> Total :</span>
                            <span class="value"><?php echo number_format($reservation['total_price'], 2, ',', ' '); ?> €</span>
                        </div>
                    </div>
                    <?php if ($reservation['status'] === 'pending'): ?>
                        <div class="reservation-actions">
                            <form action="?page=reservation&action=cancel" method="POST" class="d-inline">
                                <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                <?php echo csrfField(); ?>
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
