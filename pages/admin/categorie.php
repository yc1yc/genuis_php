<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
$user = requireAuth('admin');

// Récupérer l'ID de la catégorie si on est en mode édition
$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEdit = $categoryId > 0;

// Initialiser les variables
$errors = [];
$success = false;
$category = [
    'name' => '',
    'description' => '',
    'image_url' => ''
];

// Si on est en mode édition, charger les données de la catégorie
if ($isEdit) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM vehicle_categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            $_SESSION['flash_error'] = "Catégorie non trouvée.";
            header("Location: ?page=admin&admin_page=categories");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Erreur lors du chargement de la catégorie.";
        header("Location: ?page=admin&admin_page=categories");
        exit;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le jeton CSRF
    if (!verifyCsrfToken()) {
        $errors[] = "Erreur de sécurité, veuillez réessayer.";
    } else {
        // Récupérer et valider les données
        $category = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description'])
        ];
        
        // Validation
        if (empty($category['name'])) {
            $errors[] = "Le nom de la catégorie est requis.";
        }
    
        // Gérer l'upload d'image si un fichier est fourni
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = "Le type de fichier n'est pas autorisé. Extensions acceptées : " . implode(', ', $allowedExtensions);
            } else {
                $newFilename = uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $newFilename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $category['image_url'] = '/uploads/categories/' . $newFilename;
                } else {
                    $errors[] = "Erreur lors de l'upload de l'image.";
                }
            }
        }

        if (empty($errors)) {
            try {
                $pdo = getPDO();
                
                if ($isEdit) {
                    // Mise à jour
                    $sql = "UPDATE vehicle_categories SET name = ?, description = ?"
                         . (isset($category['image_url']) ? ", image_url = ?" : "")
                         . " WHERE id = ?";
                    
                    $params = [
                        $category['name'],
                        $category['description']
                    ];
                    
                    if (isset($category['image_url'])) {
                        $params[] = $category['image_url'];
                    }
                    $params[] = $categoryId;
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $successMessage = "La catégorie a été mise à jour avec succès.";
                } else {
                    // Création
                    $stmt = $pdo->prepare("INSERT INTO vehicle_categories (name, description, image_url) VALUES (?, ?, ?)");
                    $stmt->execute([
                        $category['name'],
                        $category['description'],
                        $category['image_url'] ?? null
                    ]);
                    $successMessage = "La catégorie a été créée avec succès.";
                }
                
                $_SESSION['flash_success'] = $successMessage;
                header("Location: ?page=admin&admin_page=categories");
                exit;
                
            } catch (PDOException $e) {
                $errors[] = "Une erreur est survenue lors de l'enregistrement.";
                error_log($e->getMessage());
            }
        }
    }
}

?>

<div class="admin-header">
    <div class="header-title">
        <h1>
            <i class="fas fa-tags"></i>
            <?php echo $isEdit ? 'Modifier la catégorie' : 'Ajouter une catégorie'; ?>
        </h1>
    </div>
    <div class="header-actions">
        <a href="?page=admin&admin_page=categories" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
            Retour à la liste
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <h4 class="alert-heading">
            <i class="fas fa-exclamation-triangle"></i>
            Des erreurs ont été détectées
        </h4>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="vehicle-form">
    <?php echo csrfField(); ?>


    <!-- Informations générales -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informations générales</h3>
        </div>git 
        <div class="card-body">
            <div class="card-section">
                <div class="form-group">
                    <label for="name">Nom de la catégorie *</label>
                    <input type="text" class="form-control" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>">
                    <small class="form-text text-muted">Le nom qui sera affiché pour cette catégorie de véhicules</small>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                    <small class="form-text text-muted">Une description détaillée de cette catégorie de véhicules</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Image -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Image de la catégorie</h3>
        </div>
        <div class="card-body">
            <div class="card-section">
                <?php if (!empty($category['image_url'])): ?>
                    <div class="current-image mb-3">
                        <h4>Image actuelle</h4>
                        <img src="<?php echo htmlspecialchars($category['image_url']); ?>" 
                             alt="Image actuelle" 
                             class="img-thumbnail" 
                             style="max-height: 200px;">
                    </div>
                <?php endif; ?>

                <div class="dropzone-container">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                        <label class="custom-file-label" for="image">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Déposez votre image ici ou cliquez pour parcourir</span>
                        </label>
                    </div>
                </div>
                <small class="form-text text-muted">
                    <i class="fas fa-info-circle"></i>
                    Formats acceptés : JPG, PNG, GIF. Taille maximale : 5 MB
                </small>
            </div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="card">
        <div class="card-body">
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-6">
                        <a href="?page=admin&admin_page=categories" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times"></i>
                            Annuler
                        </a>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-<?php echo $isEdit ? 'save' : 'plus'; ?>"></i>
                            <?php echo $isEdit ? 'Enregistrer les modifications' : 'Ajouter la catégorie'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Update file input label with selected filename
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    var fileName = this.files[0].name;
    this.nextElementSibling.textContent = fileName;
});
</script>
