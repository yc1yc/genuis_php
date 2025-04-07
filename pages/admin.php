<?php
require_once __DIR__ . '/../includes/auth.php';

// Vérifier que l'utilisateur est connecté  rôle admin
$user = requireAuth('admin');
require_once __DIR__ . '/../includes/functions.php';

// Vérification de l'authentification avant tout
$user = requireAuth('admin');

// Récupérer la sous-page d'administration et l'action
$adminPage = $_GET['admin_page'] ?? 'index';
$action = $_GET['action'] ?? 'list';

// Déterminer le fichier à inclure
$adminFile = __DIR__ . '/admin/' . $adminPage . '.php';

// Pour les actions spéciales comme add/edit, utiliser le fichier approprié
if ($adminPage === 'vehicles' && in_array($action, ['add', 'edit'])) {
    $formFile = __DIR__ . '/admin/vehicle_form.php';
    if (file_exists($formFile)) {
        $adminFile = $formFile;
    }
} elseif ($adminPage === 'categories' && in_array($action, ['add', 'edit'])) {
    $formFile = __DIR__ . '/admin/categorie.php';
    if (file_exists($formFile)) {
        $adminFile = $formFile;
    }
}

// Vérification du fichier avant l'inclusion du header
if (!file_exists($adminFile)) {
    doRedirect('?page=admin&admin_page=index');
}

// Inclusion du header après les vérifications
require_once __DIR__ . '/../includes/header.php';

// Inclure la page d'administration
require $adminFile;

// Le footer sera inclus par le fichier principal index.php
?>
