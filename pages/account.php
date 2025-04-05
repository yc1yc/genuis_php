<?php
require_once __DIR__ . '/../includes/auth.php';
$user = requireAuth();
$pdo = getPDO();

// Récupérer les réservations seulement pour les clients
$reservations = [];
if ($user['role'] !== 'admin') {
    $stmt = $pdo->prepare("
        SELECT r.*, v.brand as vehicle_brand, v.model as vehicle_model
        FROM reservations r
        JOIN vehicles v ON r.vehicle_id = v.id
        WHERE r.user_id = ?
        ORDER BY r.start_date DESC
    ");
    $stmt->execute([$user['id']]);
    $reservations = $stmt->fetchAll();
}
?>

<div class="account-container">
    <?php displayFlashMessages(); ?>

    <div class="profile-header">
        <div class="profile-cover"></div>
        <div class="profile-avatar-wrapper">
            <img src="<?php echo getUserAvatar(); ?>" alt="Avatar" class="profile-avatar">
            <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
            <span class="profile-role"><?php echo ucfirst($user['role']); ?></span>
        </div>
    </div>

    <div class="profile-content">
        <div class="profile-card">
            <div class="card-header">
                <h2><i class="fas fa-user-circle"></i> Informations personnelles</h2>
                <a href="?page=edit-profile" class="btn btn-outline">
                    <i class="fas fa-edit"></i> Modifier
                </a>
            </div>
            <div class="profile-info-grid">
                <div class="info-group">
                    <label>Email</label>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="info-group">
                    <label>Téléphone</label>
                    <p><?php echo $user['phone'] ? htmlspecialchars($user['phone']) : '—'; ?></p>
                </div>
                <div class="info-group">
                    <label>Adresse</label>
                    <p><?php echo $user['address'] ? htmlspecialchars($user['address']) : '—'; ?></p>
                </div>
                <div class="info-group">
                    <label>Ville</label>
                    <p><?php echo $user['city'] ? htmlspecialchars($user['city']) : '—'; ?></p>
                </div>
                <div class="info-group">
                    <label>Code postal</label>
                    <p><?php echo $user['postal_code'] ? htmlspecialchars($user['postal_code']) : '—'; ?></p>
                </div>
                <div class="info-group">
                    <label>Pays</label>
                    <p><?php echo $user['country'] ? htmlspecialchars($user['country']) : '—'; ?></p>
                </div>
                <div class="info-group">
                    <label>Permis de conduire</label>
                    <p><?php echo $user['driving_license'] ? htmlspecialchars($user['driving_license']) : '—'; ?></p>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <div class="card-header">
                <h2><i class="fas fa-clock"></i> Activité du compte</h2>
            </div>
            <div class="profile-info-grid">
                <div class="info-group">
                    <label>Membre depuis</label>
                    <p><?php echo formatDateTime($user['created_at']); ?></p>
                </div>
                <div class="info-group">
                    <label>Dernière connexion</label>
                    <p><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Première connexion'; ?></p>
                </div>
            </div>
        </div>

        <?php if ($user['role'] !== 'admin' && !empty($reservations)): ?>
        <div class="profile-card">
            <div class="card-header">
                <h2><i class="fas fa-car"></i> Mes réservations</h2>
                <a href="?page=reservations" class="btn btn-outline">
                    <i class="fas fa-list"></i> Voir tout
                </a>
            </div>
            <div class="reservations-grid">
                <?php foreach (array_slice($reservations, 0, 3) as $reservation): ?>
                <div class="reservation-card">
                    <div class="reservation-header">
                        <h3><?php echo htmlspecialchars($reservation['vehicle_brand'] . ' ' . $reservation['vehicle_model']); ?></h3>
                        <span class="reservation-status status-<?php echo $reservation['status']; ?>">
                            <?php echo ucfirst($reservation['status']); ?>
                        </span>
                    </div>
                    <div class="reservation-dates">
                        <div>
                            <i class="fas fa-calendar-alt"></i>
                            Du <?php echo formatDate($reservation['start_date']); ?>
                            au <?php echo formatDate($reservation['end_date']); ?>
                        </div>
                        <div>
                            <i class="fas fa-money-bill-wave"></i>
                            <?php echo formatPrice($reservation['total_price']); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
