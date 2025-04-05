<?php
require_once __DIR__ . '/../includes/auth.php';

// Vérifier que l'utilisateur est connecté et a le rôle admin
$user = requireAuth('admin');
require_once __DIR__ . '/../includes/functions.php';

// Vérification de l'authentification avant tout
$user = requireAuth('admin');

// Récupérer la sous-page d'administration
$adminPage = $_GET['admin_page'] ?? 'index';

// Vérifier si le fichier existe
$adminFile = __DIR__ . '/admin/' . $adminPage . '.php';

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
