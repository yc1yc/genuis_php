<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Header
include 'includes/header.php';

// Main content
echo '<div class="container">';
switch ($page) {
    case 'home':
        include 'pages/home.php';
        break;
    case 'reservation':
        include 'pages/reservation.php';
        break;
    case 'cart':
        include 'pages/cart.php';
        break;
    case 'about':
        include 'pages/about.php';
        break;
    case 'contact':
        include 'pages/contact.php';
        break;
    default:
        include 'pages/404.php';
}
echo '</div>';

// Footer
include 'includes/footer.php';
?>
