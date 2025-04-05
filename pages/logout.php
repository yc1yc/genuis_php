<?php
require_once __DIR__ . '/../includes/auth.php';

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier que la requête est en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        doRedirect('/genuis_php/index.php');
    }
}

// Déconnecter l'utilisateur dans tous les cas
logout();
