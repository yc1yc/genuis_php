<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireAuth('admin');

// Statistiques générales
$pdo = getPDO();

// Nombre total d'utilisateurs
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'");
$totalUsers = $stmt->fetchColumn();

// Nombre total de véhicules
$stmt = $pdo->query("SELECT COUNT(*) FROM vehicles");
$totalVehicles = $stmt->fetchColumn();

// Nombre total de réservations
$stmt = $pdo->query("SELECT COUNT(*) FROM reservations");
$totalReservations = $stmt->fetchColumn();

// Chiffre d'affaires total
$stmt = $pdo->query("SELECT SUM(total_price) FROM reservations WHERE status = 'completed'");
$totalRevenue = $stmt->fetchColumn() ?: 0;

// Réservations récentes
$stmt = $pdo->query("
    SELECT r.*, u.first_name, u.last_name, v.brand, v.model
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN vehicles v ON r.vehicle_id = v.id
    ORDER BY r.created_at DESC
    LIMIT 5
");
$recentReservations = $stmt->fetchAll();

// Derniers utilisateurs inscrits
$stmt = $pdo->query("
    SELECT *
    FROM users
    WHERE role = 'client'
    ORDER BY created_at DESC
    LIMIT 5
");
$recentUsers = $stmt->fetchAll();
?>

<div class="admin-header">
    <h1>
        <i class="fas fa-tachometer-alt"></i>
        Tableau de bord
    </h1>
</div>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-users"></i>
        <div class="stat-content">
            <h3>Utilisateurs</h3>
            <p class="stat-value"><?php echo number_format($totalUsers); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <i class="fas fa-car"></i>
        <div class="stat-content">
            <h3>Véhicules</h3>
            <p class="stat-value"><?php echo number_format($totalVehicles); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <i class="fas fa-calendar-check"></i>
        <div class="stat-content">
            <h3>Réservations</h3>
            <p class="stat-value"><?php echo number_format($totalReservations); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <i class="fas fa-euro-sign"></i>
        <div class="stat-content">
            <h3>Chiffre d'affaires</h3>
            <p class="stat-value"><?php echo number_format($totalRevenue, 2); ?> €</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Réservations récentes -->
    <section class="dashboard-section">
        <h2>
            <i class="fas fa-clock"></i>
            Réservations récentes
        </h2>
        <?php if (empty($recentReservations)): ?>
            <p class="empty-state">Aucune réservation pour le moment</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Véhicule</th>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentReservations as $reservation): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($reservation['brand'] . ' ' . $reservation['model']); ?>
                                </td>
                                <td>
                                    <?php echo formatDate($reservation['start_date']); ?>
                                </td>
                                <td>
                                    <?php echo formatPrice($reservation['total_price']); ?>
                                </td>
                                <td>
                                    <span class="status status-<?php echo $reservation['status']; ?>">
                                        <?php echo ucfirst($reservation['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?page=admin&admin_page=reservations&action=view&id=<?php echo $reservation['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="dashboard-actions">
                <a href="?page=admin&admin_page=reservations" class="btn btn-primary">
                    <i class="fas fa-list"></i>
                    Voir toutes les réservations
                </a>
            </div>
        <?php endif; ?>
    </section>

    <!-- Derniers utilisateurs -->
    <section class="dashboard-section">
        <h2>
            <i class="fas fa-user-clock"></i>
            Derniers utilisateurs
        </h2>
        <?php if (empty($recentUsers)): ?>
            <p class="empty-state">Aucun utilisateur pour le moment</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Date d'inscription</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <img src="<?php echo getUserAvatar($user['avatar']); ?>" alt="Avatar" class="user-avatar-small">
                                        <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td>
                                    <span class="status status-<?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $user['is_active'] ? 'Actif' : 'Inactif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?page=admin&admin_page=users&action=view&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="dashboard-actions">
                <a href="?page=admin&admin_page=users" class="btn btn-primary">
                    <i class="fas fa-users"></i>
                    Voir tous les utilisateurs
                </a>
            </div>
        <?php endif; ?>
    </section>
</div>
