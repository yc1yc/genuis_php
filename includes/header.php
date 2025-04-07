<?php 
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Genuis - Location de voitures</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/genuis_php/assets/img/favicon.png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/genuis_php/assets/css/style.css">
    <link rel="stylesheet" href="/genuis_php/assets/css/slider.css">
    <link rel="stylesheet" href="/genuis_php/assets/css/modal.css">
    <link rel="stylesheet" href="/genuis_php/assets/css/luxury.css">
    <?php if ($page === 'admin'): ?>
    <link rel="stylesheet" href="/genuis_php/assets/css/admin.css">
    <?php endif; ?>
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js" defer></script>
    
    <!-- Custom JS -->
    <script src="/genuis_php/assets/js/slider.js" defer></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/genuis_php/" class="navbar-brand">
                <!-- <img src="/genuis_php/assets/img/logo.png" alt="The Genuis"> -->
                The Genuis
            </a>

            <button class="navbar-toggle" id="navbarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <div class="navbar-nav" id="navbarMenu">
                <a href="/genuis_php/?page=home" class="nav-link <?php echo isset($page) && $page === 'home' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    Accueil
                </a>
                <a href="/genuis_php/?page=vehicles" class="nav-link <?php echo isset($page) && $page === 'vehicles' ? 'active' : ''; ?>">
                    <i class="fas fa-car"></i>
                    Véhicules
                </a>
                <a href="/genuis_php/?page=about" class="nav-link <?php echo isset($page) && $page === 'about' ? 'active' : ''; ?>">
                    <i class="fas fa-info-circle"></i>
                    À propos
                </a>
                <a href="/genuis_php/?page=contact" class="nav-link <?php echo isset($page) && $page === 'contact' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i>
                    Contact
                </a>
            </div>

            <div class="navbar-auth">
                <?php if ($currentUser): ?>
                    <div class="user-menu">
                        <button class="user-menu-button" id="userMenuButton">
                            <img src="<?php echo getUserAvatar(); ?>" alt="Avatar" class="user-avatar">
                            <span class="user-name"><?php echo htmlspecialchars($currentUser['first_name']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <div class="user-info">
                                <img src="<?php echo getUserAvatar(); ?>" alt="Avatar" class="user-avatar">
                                <div>
                                    <p class="user-fullname">
                                        <?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>
                                    </p>
                                    <p class="user-email"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="/genuis_php/?page=account" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                Mon compte
                            </a>
                            <?php if ($currentUser['role'] !== 'admin'): ?>
                            <a href="/genuis_php/?page=reservations" class="dropdown-item">
                                <i class="fas fa-calendar"></i>
                                Mes réservations
                            </a>
                            <?php endif; ?>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <a href="/genuis_php/?page=admin" class="dropdown-item">
                                    <i class="fas fa-cog"></i>
                                    Administration
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <button onclick="showLogoutModal()" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i>
                                Déconnexion
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/genuis_php/?page=login" class="btn btn-outline">Connexion</a>
                    <a href="/genuis_php/?page=register" class="btn btn-primary">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Modal de déconnexion -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirmation de déconnexion</h2>
                <button type="button" class="close" onclick="closeLogoutModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir vous déconnecter ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeLogoutModal()">Annuler</button>
                <form action="/genuis_php/pages/logout.php" method="POST" style="display: inline;">
                    <?php echo csrfField(); ?>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="/genuis_php/assets/js/modal.js" defer></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Menu mobile
        const navbarToggle = document.getElementById('navbarToggle');
        const navbarMenu = document.getElementById('navbarMenu');
        
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('show');
        });

        // Menu utilisateur
        const userMenuButton = document.getElementById('userMenuButton');
        const userDropdown = document.getElementById('userDropdown');
        
        if (userMenuButton) {
            userMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('show');
            });

            // Fermer le menu quand on clique ailleurs
            document.addEventListener('click', function(e) {
                if (!userMenuButton.contains(e.target)) {
                    userDropdown.classList.remove('show');
                }
            });
        }
    });

    function showLogoutModal() {
        document.getElementById('logoutModal').style.display = 'block';
        document.getElementById('userDropdown').classList.remove('show');
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    // Fermer la modale quand on clique en dehors
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('logoutModal');
        if (event.target === modal) {
            closeLogoutModal();
        }
    });
    </script>

    <?php echo displayFlashMessages(); ?>
