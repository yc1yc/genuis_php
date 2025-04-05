<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
$user = requireAuth('admin');

$pdo = getPDO();

// Traitement des actions avant toute sortie HTML
$action = $_GET['action'] ?? '';
$reservationId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$status = $_GET['status'] ?? '';

if ($action && $reservationId) {
    switch ($action) {
        case 'status':
            if (in_array($status, ['confirmed', 'cancelled', 'completed'])) {
                try {
                    $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $reservationId]);

                    if ($status === 'confirmed') {
                        // Récupérer les informations pour l'email
                        $stmt = $pdo->prepare("
                            SELECT r.*, 
                                   u.email, u.first_name, u.last_name,
                                   CONCAT(v.brand, ' ', v.model) as vehicle_name
                            FROM reservations r
                            JOIN users u ON r.user_id = u.id
                            JOIN vehicles v ON r.vehicle_id = v.id
                            WHERE r.id = ?
                        ");
                        $stmt->execute([$reservationId]);
                        $reservation = $stmt->fetch();
                        
                        if ($reservation) {
                            sendReservationConfirmationEmail($reservation);
                            $_SESSION['flash_success'] = 'Réservation confirmée et email envoyé au client';
                        }
                    } else {
                        $_SESSION['flash_success'] = 'Statut de la réservation mis à jour';
                    }
                } catch (PDOException $e) {
                    $_SESSION['flash_error'] = 'Erreur lors de la mise à jour de la réservation';
                }
            }
            break;
    }
    
    // Rediriger après le traitement
    doRedirect('?page=admin&admin_page=reservations');
}

// Filtres
$status = isset($_GET['status']) ? $_GET['status'] : '';
$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construction de la requête
$whereConditions = [];
$params = [];
$types = [];

if ($status) {
    $whereConditions[] = "r.status = ?";
    $params[] = $status;
    $types[] = PDO::PARAM_STR;
}

if ($dateRange) {
    switch ($dateRange) {
        case 'today':
            $whereConditions[] = "DATE(r.start_date) = CURDATE()";
            break;
        case 'tomorrow':
            $whereConditions[] = "DATE(r.start_date) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'week':
            $whereConditions[] = "r.start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $whereConditions[] = "r.start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)";
            break;
    }
}

if ($search) {
    $whereConditions[] = "(u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR CONCAT(v.brand, ' ', v.model) LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types = array_merge($types, [PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR, PDO::PARAM_STR]);
}

$whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Pagination
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Nombre total de réservations
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN vehicles v ON r.vehicle_id = v.id
    LEFT JOIN vehicle_categories vc ON v.category_id = vc.id
    $whereClause
");

foreach ($params as $i => $param) {
    $stmt->bindValue($i + 1, $param, $types[$i]);
}
$stmt->execute();
$total = $stmt->fetchColumn();
$totalPages = ceil($total / $perPage);

// Liste des réservations
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.first_name, u.last_name, u.email,
           CONCAT(v.brand, ' ', v.model) as vehicle_name, vc.name as vehicle_category
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN vehicles v ON r.vehicle_id = v.id
    LEFT JOIN vehicle_categories vc ON v.category_id = vc.id
    $whereClause
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
");

// Lier d'abord les paramètres de recherche
foreach ($params as $i => $param) {
    $stmt->bindValue($i + 1, $param, $types[$i]);
}

// Puis lier les paramètres de pagination avec les types appropriés
$paramIndex = count($params);
$stmt->bindValue($paramIndex + 1, $perPage, PDO::PARAM_INT);
$stmt->bindValue($paramIndex + 2, $offset, PDO::PARAM_INT);

$stmt->execute();
$reservations = $stmt->fetchAll();

// Statistiques
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status = 'completed' THEN total_price ELSE 0 END) as revenue
    FROM reservations
");
$stats = $stmt->fetch();

function formatNumber($value) {
    return is_null($value) ? '0' : number_format($value);
}

function getStatusClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'confirmed':
            return 'info';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getStatusLabel($status) {
    $labels = [
        'pending' => 'En attente',
        'confirmed' => 'Confirmée',
        'completed' => 'Terminée',
        'cancelled' => 'Annulée'
    ];
    return $labels[$status] ?? $status;
}

?>

<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1>
            <i class="fas fa-calendar-check"></i>
            Gestion des réservations
        </h1>
        
        <div class="header-actions">
            <form class="filters-form" action="" method="GET">
                <input type="hidden" name="page" value="admin/reservations">
                
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Rechercher..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <select name="status" class="filter-select">
                    <option value="">Tous les statuts</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>En attente</option>
                    <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmée</option>
                    <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Terminée</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                </select>
                
                <select name="date_range" class="filter-select">
                    <option value="">Toutes les dates</option>
                    <option value="today" <?php echo $dateRange === 'today' ? 'selected' : ''; ?>>Aujourd'hui</option>
                    <option value="tomorrow" <?php echo $dateRange === 'tomorrow' ? 'selected' : ''; ?>>Demain</option>
                    <option value="week" <?php echo $dateRange === 'week' ? 'selected' : ''; ?>>7 prochains jours</option>
                    <option value="month" <?php echo $dateRange === 'month' ? 'selected' : ''; ?>>30 prochains jours</option>
                </select>
                
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </form>
        </div>
    </div>

    <?php 
    // Afficher les messages flash s'il y en a
    if (isset($_SESSION['flash_success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['flash_success']) . '</div>';
        unset($_SESSION['flash_success']);
    }
    if (isset($_SESSION['flash_error'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
    ?>

    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-clock"></i>
            <div class="stat-content">
                <h3>En attente</h3>
                <p class="stat-value"><?php echo formatNumber($stats['pending']); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-check-circle"></i>
            <div class="stat-content">
                <h3>Confirmées</h3>
                <p class="stat-value"><?php echo formatNumber($stats['confirmed']); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-check-double"></i>
            <div class="stat-content">
                <h3>Terminées</h3>
                <p class="stat-value"><?php echo formatNumber($stats['completed']); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <i class="fas fa-euro-sign"></i>
            <div class="stat-content">
                <h3>Chiffre d'affaires</h3>
                <p class="stat-value"><?php echo formatNumber($stats['revenue'], 2); ?> €</p>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Véhicule</th>
                    <th>Dates</th>
                    <th>Durée</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td>#<?php echo $reservation['id']; ?></td>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-details">
                                <div><?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?></div>
                                <div class="user-email"><?php echo htmlspecialchars($reservation['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="vehicle-info">
                            <strong><?php echo htmlspecialchars($reservation['vehicle_name']); ?></strong>
                            <span class="vehicle-category"><?php echo htmlspecialchars($reservation['vehicle_category']); ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="date-range">
                            <div>
                                <i class="fas fa-calendar"></i>
                                <?php echo formatDate($reservation['start_date']); ?>
                            </div>
                            <div>
                                <i class="fas fa-calendar-check"></i>
                                <?php echo formatDate($reservation['end_date']); ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php
                        $start = new DateTime($reservation['start_date']);
                        $end = new DateTime($reservation['end_date']);
                        $duration = $start->diff($end);
                        echo $duration->days . ' jours';
                        ?>
                    </td>
                    <td><?php echo formatNumber($reservation['total_price'], 2); ?> €</td>
                    <td>
                        <span class="status-badge status-<?php echo getStatusClass($reservation['status']); ?>">
                            <?php echo getStatusLabel($reservation['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($reservation['status'] === 'pending'): ?>
                            <a href="?page=admin/reservations&action=status&id=<?php echo $reservation['id']; ?>&status=confirmed" 
                               class="btn-icon" title="Confirmer">
                                <i class="fas fa-check"></i>
                            </a>
                            <?php endif; ?>

                            <?php if (in_array($reservation['status'], ['pending', 'confirmed'])): ?>
                            <a href="?page=admin/reservations&action=status&id=<?php echo $reservation['id']; ?>&status=cancelled" 
                               class="btn-icon delete" title="Annuler">
                                <i class="fas fa-times"></i>
                            </a>
                            <?php endif; ?>

                            <?php if ($reservation['status'] === 'confirmed'): ?>
                            <a href="?page=admin/reservations&action=status&id=<?php echo $reservation['id']; ?>&status=completed" 
                               class="btn-icon" title="Marquer comme terminée">
                                <i class="fas fa-check-double"></i>
                            </a>
                            <?php endif; ?>

                            <a href="#" class="btn-icon" title="Voir les détails" 
                               onclick="showReservationDetails(<?php echo $reservation['id']; ?>)">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?page=admin/reservations&p=<?php echo ($page - 1); ?>&status=<?php echo urlencode($status); ?>&date_range=<?php echo urlencode($dateRange); ?>&search=<?php echo urlencode($search); ?>" 
           class="btn btn-outline">
            <i class="fas fa-chevron-left"></i>
            Précédent
        </a>
        <?php endif; ?>

        <span class="pagination-info">
            Page <?php echo $page; ?> sur <?php echo $totalPages; ?>
        </span>

        <?php if ($page < $totalPages): ?>
        <a href="?page=admin/reservations&p=<?php echo ($page + 1); ?>&status=<?php echo urlencode($status); ?>&date_range=<?php echo urlencode($dateRange); ?>&search=<?php echo urlencode($search); ?>" 
           class="btn btn-outline">
            Suivant
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function showReservationDetails(id) {
    // TODO: Implémenter une modal pour afficher les détails de la réservation
    alert('Fonctionnalité à venir : Affichage des détails de la réservation #' + id);
}
</script>
