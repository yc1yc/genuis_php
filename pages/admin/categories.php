<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireAuth('admin');
require_once __DIR__ . '/../../includes/functions.php';

try {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM vehicle_categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des catégories.";
}
?>

<div class="admin-header">
    <div class="header-title">
        <h1>
            <i class="fas fa-tags"></i>
            Gestion des catégories
        </h1>
    </div>
    <div class="header-actions">
        <a href="?page=admin&admin_page=categories&action=add" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Ajouter une catégorie
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if ($message = getFlashMessage('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="vehicles-grid">
    <?php foreach ($categories as $category): ?>
        <div class="vehicle-card">
            <div class="vehicle-image">
                <?php if (!empty($category['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($category['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($category['name']); ?>">
                <?php else: ?>
                    <div class="no-image">
                        <i class="fas fa-image"></i>
                        <span>Aucune image</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="vehicle-info">
                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                <?php if (!empty($category['description'])): ?>
                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                <?php endif; ?>
            </div>
            <div class="vehicle-actions">
                <a href="?page=admin&admin_page=categories&action=edit&id=<?php echo $category['id']; ?>" 
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit"></i>
                    Modifier
                </a>
                <button type="button" 
                        class="btn btn-sm btn-outline-danger"
                        onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>')">
                    <i class="fas fa-trash"></i>
                    Supprimer
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
function deleteCategory(id, name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la catégorie "${name}" ?`)) {
        window.location.href = `?page=admin&admin_page=categories&action=delete&id=${id}`;
    }
}
</script>
