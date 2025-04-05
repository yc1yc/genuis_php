<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireAuth('admin');

// Paramètres de pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "WHERE role = 'client'";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Récupérer le nombre total d'utilisateurs
$pdo = getPDO();
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereClause");
$countStmt->execute($params);
$totalUsers = $countStmt->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);

// Récupérer les utilisateurs pour la page actuelle
$stmt = $pdo->prepare("
    SELECT u.*, 
           COUNT(r.id) as total_reservations,
           SUM(CASE WHEN r.status = 'completed' THEN r.total_price ELSE 0 END) as total_spent
    FROM users u
    LEFT JOIN reservations r ON u.id = r.user_id
    $whereClause
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="admin-header">
    <h1>
        <i class="fas fa-users"></i>
        Gestion des utilisateurs
    </h1>
    <div class="header-actions">
        <form action="" method="GET" class="search-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="admin_page" value="users">
            <div class="search-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Rechercher un utilisateur..." class="form-control">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
        <a href="?page=admin&admin_page=users&action=add" class="btn btn-primary">
            <i class="fas fa-user-plus"></i>
            Ajouter un utilisateur
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Email</th>
                <th>Réservations</th>
                <th>Total dépensé</th>
                <th>Date d'inscription</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <?php if (!empty($search)): ?>
                            Aucun utilisateur trouvé pour "<?php echo htmlspecialchars($search); ?>"
                        <?php else: ?>
                            Aucun utilisateur trouvé
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $userData): ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <img src="<?php echo getUserAvatar($userData['avatar']); ?>" alt="Avatar" class="user-avatar-small">
                                <div>
                                    <div class="user-name">
                                        <?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?>
                                    </div>
                                    <div class="user-role">Client</div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($userData['email']); ?></td>
                        <td class="text-center"><?php echo number_format($userData['total_reservations']); ?></td>
                        <td class="text-right"><?php echo formatPrice($userData['total_spent']); ?></td>
                        <td><?php echo formatDate($userData['created_at']); ?></td>
                        <td>
                            <span class="status status-<?php echo $userData['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $userData['is_active'] ? 'Actif' : 'Inactif'; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="?page=admin&admin_page=users&action=edit&id=<?php echo $userData['id']; ?>" 
                               class="btn btn-sm btn-info" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?page=admin&admin_page=users&action=view&id=<?php echo $userData['id']; ?>" 
                               class="btn btn-sm btn-secondary" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($userData['is_active']): ?>
                                <button onclick="deactivateUser(<?php echo $userData['id']; ?>)" 
                                        class="btn btn-sm btn-warning" title="Désactiver">
                                    <i class="fas fa-user-slash"></i>
                                </button>
                            <?php else: ?>
                                <button onclick="activateUser(<?php echo $userData['id']; ?>)" 
                                        class="btn btn-sm btn-success" title="Activer">
                                    <i class="fas fa-user-check"></i>
                                </button>
                            <?php endif; ?>
                            <button onclick="deleteUser(<?php echo $userData['id']; ?>)" 
                                    class="btn btn-sm btn-danger" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=admin&admin_page=users&p=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>" 
               class="btn btn-outline">
                <i class="fas fa-chevron-left"></i>
                Précédent
            </a>
        <?php endif; ?>

        <div class="pagination-info">
            Page <?php echo $page; ?> sur <?php echo $totalPages; ?>
        </div>

        <?php if ($page < $totalPages): ?>
            <a href="?page=admin&admin_page=users&p=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>" 
               class="btn btn-outline">
                Suivant
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
function activateUser(userId) {
    if (confirm('Voulez-vous vraiment activer cet utilisateur ?')) {
        window.location.href = `?page=admin&admin_page=users&action=activate&id=${userId}`;
    }
}

function deactivateUser(userId) {
    if (confirm('Voulez-vous vraiment désactiver cet utilisateur ?')) {
        window.location.href = `?page=admin&admin_page=users&action=deactivate&id=${userId}`;
    }
}

function deleteUser(userId) {
    if (confirm('Voulez-vous vraiment supprimer cet utilisateur ? Cette action est irréversible.')) {
        window.location.href = `?page=admin&admin_page=users&action=delete&id=${userId}`;
    }
}
</script>
