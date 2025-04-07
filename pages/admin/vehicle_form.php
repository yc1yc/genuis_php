<?php
// Démarrer la session si ce n'est pas déjà fait
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

// Récupérer l'ID du véhicule si on est en mode édition
$vehicleId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEdit = $vehicleId > 0;

// Initialiser les variables
$errors = [];
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('Début du traitement du formulaire');
    error_log('POST data: ' . print_r($_POST, true));
    error_log('Session CSRF token: ' . (isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : 'non défini'));
    error_log('POST CSRF token: ' . (isset($_POST['csrf_token']) ? $_POST['csrf_token'] : 'non défini'));
    
    // Vérifier le jeton CSRF
    if (!verifyCsrfToken()) {
        $errors[] = "Erreur de sécurité, veuillez réessayer.";
        error_log('Erreur CSRF');
    } else {
        error_log('CSRF OK');
        // Récupérer et valider les données
        $vehicle = [
            'category_id' => intval($_POST['category_id']),
            'brand' => trim($_POST['brand']),
            'model' => trim($_POST['model']),
            'year' => intval($_POST['year']),
            'registration_number' => trim($_POST['registration_number'] ?? ''),
            'fuel_type' => trim($_POST['fuel_type'] ?? ''),
            'transmission' => trim($_POST['transmission'] ?? ''),
            'seats' => intval($_POST['seats'] ?? 0),
            'price_per_day' => floatval(str_replace(',', '.', $_POST['price_per_day'] ?? 0)),
            'description' => trim($_POST['description'] ?? ''),
            'specifications' => trim($_POST['specifications'] ?? ''),
            'mileage' => intval($_POST['mileage'] ?? 0),
            'doors' => intval($_POST['doors'] ?? 0),
            'air_conditioning' => isset($_POST['air_conditioning']),
            'is_available' => isset($_POST['is_available'])
        ];
        
        // Validation
        if (empty($vehicle['category_id'])) $errors[] = "La catégorie est requise.";
        if (empty($vehicle['brand'])) $errors[] = "La marque est requise.";
        if (empty($vehicle['model'])) $errors[] = "Le modèle est requis.";
        if (empty($vehicle['registration_number'])) $errors[] = "L'immatriculation est requise.";
        if ($vehicle['year'] < 1900 || $vehicle['year'] > date('Y') + 1) $errors[] = "L'année n'est pas valide.";
        if ($vehicle['price_per_day'] <= 0) $errors[] = "Le prix par jour doit être supérieur à 0.";
        if ($vehicle['seats'] <= 0) $errors[] = "Le nombre de places doit être supérieur à 0.";
        if ($vehicle['doors'] < 2 || $vehicle['doors'] > 7) $errors[] = "Le nombre de portes doit être entre 2 et 7.";
        if ($vehicle['mileage'] < 0) $errors[] = "Le kilométrage ne peut pas être négatif.";
        if (!in_array($vehicle['fuel_type'], ['essence', 'diesel', 'électrique', 'hybride'])) $errors[] = "Le type de carburant n'est pas valide.";
        if (!in_array($vehicle['transmission'], ['manuelle', 'automatique'])) $errors[] = "Le type de transmission n'est pas valide.";
    
        if (empty($errors)) {
            try {
                error_log('Tentative de connexion à la base de données');
                $pdo = getPDO();
                error_log('Connexion à la base de données réussie');
            
                if ($isEdit) {
                    error_log('Mode édition');
                    // Mise à jour
                    $stmt = $pdo->prepare("UPDATE vehicles SET category_id = ?, brand = ?, model = ?, year = ?, registration_number = ?, fuel_type = ?, transmission = ?, seats = ?, price_per_day = ?, description = ?, specifications = ?, mileage = ?, doors = ?, air_conditioning = ?, is_available = ?, updated_at = NOW() WHERE id = ?");
                    error_log('Requête préparée: ' . $stmt->queryString);
                    $stmt->execute([
                        $vehicle['category_id'],
                        $vehicle['brand'],
                        $vehicle['model'],
                        $vehicle['year'],
                        $vehicle['registration_number'],
                        $vehicle['fuel_type'],
                        $vehicle['transmission'],
                        $vehicle['seats'],
                        $vehicle['price_per_day'],
                        $vehicle['description'],
                        $vehicle['specifications'],
                        $vehicle['mileage'],
                        $vehicle['doors'],
                        $vehicle['air_conditioning'],
                        $vehicle['is_available'],
                        $vehicleId
                    ]);
                    error_log('Mise à jour effectuée');
                    $success = true;
                    $successMessage = "Le véhicule a été mis à jour avec succès.";
                } else {
                    error_log('Mode création');
                    // Création
                    $stmt = $pdo->prepare("INSERT INTO vehicles (category_id, brand, model, year, registration_number, fuel_type, transmission, seats, price_per_day, description, specifications, mileage, doors, air_conditioning, is_available, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    error_log('Requête préparée: ' . $stmt->queryString);
                    error_log('Valeurs: ' . print_r([
                        $vehicle['category_id'],
                        $vehicle['brand'],
                        $vehicle['model'],
                        $vehicle['year'],
                        $vehicle['registration_number'],
                        $vehicle['fuel_type'],
                        $vehicle['transmission'],
                        $vehicle['seats'],
                        $vehicle['price_per_day'],
                        $vehicle['description'],
                        $vehicle['specifications'],
                        $vehicle['mileage'],
                        $vehicle['doors'],
                        $vehicle['air_conditioning'],
                        $vehicle['is_available']
                    ], true));
                    $stmt->execute([
                        $vehicle['category_id'],
                        $vehicle['brand'],
                        $vehicle['model'],
                        $vehicle['year'],
                        $vehicle['registration_number'],
                        $vehicle['fuel_type'],
                        $vehicle['transmission'],
                        $vehicle['seats'],
                        $vehicle['price_per_day'],
                        $vehicle['description'],
                        $vehicle['specifications'],
                        $vehicle['mileage'],
                        $vehicle['doors'],
                        $vehicle['air_conditioning'],
                        $vehicle['is_available']
                    ]);
                    error_log('Insertion effectuée');
                    $success = true;
                    $successMessage = "Le véhicule a été ajouté avec succès.";
                }
                
                if (!headers_sent()) {
                    error_log('Tentative de redirection');
                    $_SESSION['flash_success'] = $successMessage;
                    header("Location: ?page=admin&admin_page=vehicles");
                    exit;
                } else {
                    error_log('Headers déjà envoyés, impossible de rediriger');
                    $success = true;
                }
            } catch (PDOException $e) {
                error_log('Erreur PDO: ' . $e->getMessage());
                if (strpos($e->getMessage(), 'registration_number') !== false) {
                    $errors[] = "Cette immatriculation est déjà utilisée.";
                } else {
                    $errors[] = "Une erreur est survenue lors de l'enregistrement.";
                }
            }
        } else {
            error_log('Erreurs de validation: ' . print_r($errors, true));
        }
    }
}

// Charger les catégories de véhicules
$pdo = getPDO();
$stmt = $pdo->query("SELECT id, name FROM vehicle_categories ORDER BY name");
$categories = $stmt->fetchAll();

// Initialiser les variables
$vehicle = [
    'category_id' => '',
    'brand' => '',
    'model' => '',
    'year' => date('Y'),
    'registration_number' => '',
    'fuel_type' => '',
    'transmission' => '',
    'seats' => '',
    'price_per_day' => '',
    'description' => '',
    'specifications' => '',
    'mileage' => 0,
    'doors' => 4,
    'air_conditioning' => true,
    'is_available' => true
];

// En mode édition, charger les données du véhicule
if ($isEdit) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
        $stmt->execute([$vehicleId]);
        $loadedVehicle = $stmt->fetch();
        
        if (!$loadedVehicle) {
            $_SESSION['flash_error'] = "Véhicule non trouvé.";
            header("Location: ?page=admin&admin_page=vehicles");
            exit;
        }
        
        $vehicle = array_merge($vehicle, $loadedVehicle);
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Erreur lors du chargement du véhicule.";
        header("Location: ?page=admin&admin_page=vehicles");
        exit;
    }
}
    

?>

<div class="admin-header">
    <div class="header-title">
        <h1>
            <i class="fas fa-car"></i>
            <?php echo $isEdit ? 'Modifier le véhicule' : 'Ajouter un véhicule'; ?>
        </h1>
        <p class="text-muted">Remplissez le formulaire ci-dessous pour <?php echo $isEdit ? 'modifier' : 'ajouter'; ?> un véhicule</p>
    </div>
    <div class="header-actions">
        <a href="?page=admin&admin_page=vehicles" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Retour à la liste
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i>
        <div class="alert-content">
            <h4 class="alert-title">Erreurs de validation</h4>
            <ul class="alert-list mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        <div class="alert-content">
            <h4 class="alert-title">Succès</h4>
            <p class="mb-0"><?php echo htmlspecialchars($successMessage); ?></p>
            <p class="mt-2">
                <a href="?page=admin&admin_page=vehicles" class="btn btn-success btn-sm">Retour à la liste des véhicules</a>
            </p>
        </div>
    </div>
<?php endif; ?>

<form method="POST" action="" class="vehicle-form" enctype="multipart/form-data">
    <?php echo csrfField(); ?>

    <!-- Informations de base -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informations de base</h3>
        </div>
        <div class="card-body">
            <div class="card-section">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="category">Catégorie *</label>
                            <select class="form-control" id="category" name="category_id" required>
                                <option value="">Sélectionnez une catégorie</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo isset($vehicle['category_id']) && $vehicle['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="brand">Marque *</label>
                            <input type="text" class="form-control" id="brand" name="brand" value="<?php echo htmlspecialchars($vehicle['brand'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="model">Modèle *</label>
                            <input type="text" class="form-control" id="model" name="model" value="<?php echo htmlspecialchars($vehicle['model'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="registration_number">Immatriculation *</label>
                            <input type="text" class="form-control" id="registration_number" name="registration_number" value="<?php echo htmlspecialchars($vehicle['registration_number'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Caractéristiques techniques -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Caractéristiques techniques</h3>
        </div>
        <div class="card-body">
            <div class="card-section">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="year">Année *</label>
                            <input type="number" class="form-control" id="year" name="year" value="<?php echo htmlspecialchars($vehicle['year'] ?? date('Y')); ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="mileage">Kilométrage</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="mileage" name="mileage" value="<?php echo htmlspecialchars($vehicle['mileage'] ?? ''); ?>">
                                <div class="input-group-append">
                                    <span class="input-group-text">km</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="fuel_type">Type de carburant</label>
                            <select class="form-control" id="fuel_type" name="fuel_type">
                                <option value="">Sélectionnez un type</option>
                                <?php foreach (['essence', 'diesel', 'électrique', 'hybride'] as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo isset($vehicle['fuel_type']) && $vehicle['fuel_type'] == $type ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="transmission">Transmission</label>
                            <select class="form-control" id="transmission" name="transmission">
                                <option value="">Sélectionnez une transmission</option>
                                <?php foreach (['manuelle', 'automatique'] as $trans): ?>
                                    <option value="<?php echo $trans; ?>" <?php echo isset($vehicle['transmission']) && $vehicle['transmission'] == $trans ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($trans); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="seats">Nombre de places *</label>
                            <input type="number" class="form-control" id="seats" name="seats" value="<?php echo htmlspecialchars($vehicle['seats'] ?? '5'); ?>" required min="1" max="9">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="doors">Nombre de portes</label>
                            <input type="number" class="form-control" id="doors" name="doors" value="<?php echo htmlspecialchars($vehicle['doors'] ?? '4'); ?>" min="2" max="7">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prix et disponibilité -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Prix et disponibilité</h3>
        </div>
        <div class="card-body">
            <div class="card-section">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="price_per_day">Prix par jour *</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="price_per_day" name="price_per_day" value="<?php echo htmlspecialchars($vehicle['price_per_day'] ?? ''); ?>" required step="0.01">
                                <div class="input-group-append">
                                    <span class="input-group-text">€</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="d-block">&nbsp;</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_available" name="is_available" <?php echo (!isset($vehicle['is_available']) || $vehicle['is_available']) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="is_available">Disponible à la location</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="d-block">&nbsp;</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="air_conditioning" name="air_conditioning" <?php echo (!isset($vehicle['air_conditioning']) || $vehicle['air_conditioning']) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="air_conditioning">Climatisation</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Description et spécifications -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Description et spécifications</h3>
        </div>
        <div class="card-body">
            <div class="card-section">
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($vehicle['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="specifications">Spécifications techniques</label>
                    <textarea class="form-control" id="specifications" name="specifications" rows="4"><?php echo htmlspecialchars($vehicle['specifications'] ?? ''); ?></textarea>
                    <small class="form-text text-muted">Entrez les spécifications techniques détaillées du véhicule (motorisation, équipements, etc.)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Images -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Images du véhicule</h3>
        </div>
        <div class="card-body">
            <div class="card-section">
                <div class="dropzone-container">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="vehicle_images" name="images[]" multiple accept="image/*">
                        <label class="custom-file-label" for="vehicle_images">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Déposez vos images ici ou cliquez pour parcourir</span>
                        </label>
                    </div>
                    <div id="preview-container" class="preview-container d-none">
                        <div class="preview-list"></div>
                    </div>
                </div>
                <small class="form-text text-muted">
                    <i class="fas fa-info-circle"></i>
                    Formats acceptés : JPG, PNG, GIF. Taille maximale : 5 MB par image.
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
                        <a href="?page=admin&admin_page=vehicles" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times"></i>
                            Annuler
                        </a>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-<?php echo $isEdit ? 'save' : 'plus'; ?>"></i>
                            <?php echo $isEdit ? 'Enregistrer les modifications' : 'Ajouter le véhicule'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Mise à jour du label du champ de fichiers
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    var files = Array.from(this.files).map(f => f.name);
    document.querySelector('.custom-file-label').textContent = files.length > 0 ? files.join(', ') : 'Choisir des images...';
});
</script>
