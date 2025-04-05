<?php
// Environnement
define('ENV', 'development');

// Configuration du site
define('SITE_NAME', 'The Genuis');
define('SITE_URL', 'http://localhost/genuis_php');

// Configuration des sessions
$sessionConfig = [
    'cookie_httponly' => 1,
    'use_only_cookies' => 1,
    'cookie_secure' => ENV === 'production',
    'cookie_samesite' => 'Lax',
    'gc_maxlifetime' => 3600
];

foreach ($sessionConfig as $key => $value) {
    ini_set("session.$key", $value);
}

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Configuration SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'votre-email@gmail.com');
define('SMTP_PASSWORD', 'votre-mot-de-passe-app');
define('SMTP_FROM_EMAIL', 'noreply@genuis-rental.com');
define('SMTP_FROM_NAME', SITE_NAME);

// En-têtes de sécurité
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
if (ENV === 'production') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Gestion des erreurs
if (ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Dossiers du projet
$dirs = ['logs', 'uploads', 'uploads/vehicles'];
foreach ($dirs as $dir) {
    $dir = __DIR__ . '/../' . $dir;
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}
