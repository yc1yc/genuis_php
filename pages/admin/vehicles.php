<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireAuth('admin');

// Paramètres de pagination
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (brand LIKE ? OR model LIKE ? OR registration_number LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Récupérer le nombre total de véhicules
$pdo = getPDO();
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles $whereClause");
$countStmt->execute($params);
$totalVehicles = $countStmt->fetchColumn();
$totalPages = ceil($totalVehicles / $perPage);

// Récupérer les véhicules pour la page actuelle
$stmt = $pdo->prepare("
    SELECT v.*, 
           COUNT(r.id) as total_reservations,
           SUM(CASE WHEN r.status = 'completed' THEN r.total_price ELSE 0 END) as total_revenue,
           (SELECT image_path FROM vehicle_images WHERE vehicle_id = v.id LIMIT 1) as main_image
    FROM vehicles v
    LEFT JOIN reservations r ON v.id = r.vehicle_id
    $whereClause
    GROUP BY v.id
    ORDER BY v.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$vehicles = $stmt->fetchAll();
?>

<div class="admin-header">
    <h1>
        <i class="fas fa-car"></i>
        Gestion des véhicules
    </h1>
    <div class="header-actions">
        <form action="" method="GET" class="search-form">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="admin_page" value="vehicles">
            <div class="search-group">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Rechercher un véhicule..." class="form-control">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
        <a href="?page=admin&admin_page=vehicles&action=add" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Ajouter un véhicule
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Véhicule</th>
                <th>Immatriculation</th>
                <th>Prix/jour</th>
                <th>Réservations</th>
                <th>Revenus</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vehicles)): ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <?php if (!empty($search)): ?>
                            Aucun véhicule trouvé pour "<?php echo htmlspecialchars($search); ?>"
                        <?php else: ?>
                            Aucun véhicule disponible
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td>
                            <div class="vehicle-info">
                                <?php if ($vehicle['main_image']): ?>
                                    <img src="<?php echo htmlspecialchars($vehicle['main_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>" 
                                         class="vehicle-thumbnail">
                                <?php else: ?>
                                    <div class="vehicle-thumbnail-placeholder">
                                        <i class="fas fa-car"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="vehicle-name">
                                        <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>
                                    </div>
                                    <div class="vehicle-year">
                                        <?php echo htmlspecialchars($vehicle['year']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($vehicle['registration_number']); ?></td>
                        <td class="text-right"><?php echo formatPrice($vehicle['price_per_day']); ?></td>
                        <td class="text-center"><?php echo number_format($vehicle['total_reservations']); ?></td>
                        <td class="text-right"><?php echo formatPrice($vehicle['total_revenue']); ?></td>
                        <td>
                            <span class="status status-<?php echo $vehicle['is_available'] ? 'active' : 'inactive'; ?>">
                                <?php echo $vehicle['is_available'] ? 'Disponible' : 'Indisponible'; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="?page=admin&admin_page=vehicles&action=edit&id=<?php echo $vehicle['id']; ?>" 
                               class="btn btn-sm btn-info" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?page=admin&admin_page=vehicles&action=view&id=<?php echo $vehicle['id']; ?>" 
                               class="btn btn-sm btn-secondary" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($vehicle['is_available']): ?>
                                <button onclick="setVehicleAvailability(<?php echo $vehicle['id']; ?>, false)" 
                                        class="btn btn-sm btn-warning" title="Marquer comme indisponible">
                                    <i class="fas fa-ban"></i>
                                </button>
                            <?php else: ?>
                                <button onclick="setVehicleAvailability(<?php echo $vehicle['id']; ?>, true)" 
                                        class="btn btn-sm btn-success" title="Marquer comme disponible">
                                    <i class="fas fa-check"></i>
                                </button>
                            <?php endif; ?>
                            <button onclick="deleteVehicle(<?php echo $vehicle['id']; ?>)" 
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
            <a href="?page=admin&admin_page=vehicles&p=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>" 
               class="btn btn-outline">
                <i class="fas fa-chevron-left"></i>
                Précédent
            </a>
        <?php endif; ?>

        <div class="pagination-info">
            Page <?php echo $page; ?> sur <?php echo $totalPages; ?>
        </div>

        <?php if ($page < $totalPages): ?>
            <a href="?page=admin&admin_page=vehicles&p=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>" 
               class="btn btn-outline">
                Suivant
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
function setVehicleAvailability(vehicleId, available) {
    const action = available ? 'activer' : 'désactiver';
    if (confirm(`Voulez-vous vraiment ${action} ce véhicule ?`)) {
        window.location.href = `?page=admin&admin_page=vehicles&action=${available ? 'activate' : 'deactivate'}&id=${vehicleId}`;
    }
}

function deleteVehicle(vehicleId) {
    if (confirm('Voulez-vous vraiment supprimer ce véhicule ? Cette action est irréversible.')) {
        window.location.href = `?page=admin&admin_page=vehicles&action=delete&id=${vehicleId}`;
    }
}
</script>
