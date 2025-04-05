<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Vérification de la session "Se souvenir de moi"
checkRememberMe();

// La session est déjà démarrée dans config.php
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Header
include 'includes/header.php';

// Main content
echo '<div class="container">';
switch ($page) {
    case 'home':
        include 'pages/home.php';
        break;
    case 'vehicles':
        include 'pages/vehicles.php';
        break;
    case 'reservation':
        include 'pages/reservation.php';
        break;
    case 'cart':
        include 'pages/cart.php';
        break;
    case 'confirmation':
        include 'pages/confirmation.php';
        break;
    case 'login':
        include 'pages/login.php';
        break;
    case 'register':
        include 'pages/register.php';
        break;
    case 'account':
        include 'pages/account.php';
        break;
    case 'logout':
        logout();
        break;
    case 'admin':
        include 'pages/admin.php';
        break;
    case 'about':
        include 'pages/about.php';
        break;
    case 'contact':
        include 'pages/contact.php';
        break;
    case 'terms':
        include 'pages/terms.php';
        break;
    case 'privacy':
        include 'pages/privacy.php';
        break;
    default:
        include 'pages/404.php';
}
echo '</div>';

// Footer
include 'includes/footer.php';
?>
