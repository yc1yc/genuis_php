<nav class="main-nav">
    <div class="container">
        <div class="logo">
            <a href="index.php">The Genuis</a>
        </div>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
            ☰
        </button>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="index.php?page=reservation">Réservation</a></li>
            <li><a href="index.php?page=about">À propos</a></li>
            <li><a href="index.php?page=contact">Contact</a></li>
            <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <li><a href="index.php?page=cart" class="cart-link">Panier (<?php echo count($_SESSION['cart']); ?>)</a></li>
            <?php endif; ?>
        </ul>
        <div class="auth-links">
            <?php if (isLoggedIn()): ?>
                <div class="dropdown">
                    <button class="dropbtn">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </button>
                    <div class="dropdown-content">
                        <a href="index.php?page=account">Mon compte</a>
                        <?php if (isAdmin()): ?>
                            <a href="index.php?page=admin">Administration</a>
                        <?php endif; ?>
                        <a href="api/auth/logout.php">Déconnexion</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="index.php?page=login" class="btn btn-outline">Connexion</a>
                <a href="index.php?page=register" class="btn btn-primary">Inscription</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menu mobile
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    
    menuToggle.addEventListener('click', function() {
        navLinks.classList.toggle('active');
    });

    // Fermeture du menu au clic en dehors
    document.addEventListener('click', function(e) {
        const isClickInside = navLinks.contains(e.target) || menuToggle.contains(e.target);
        if (!isClickInside && navLinks.classList.contains('active')) {
            navLinks.classList.remove('active');
        }
    });

    // Gestion des dropdowns
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
        });
    });

    // Fermeture des dropdowns au clic en dehors
    document.addEventListener('click', function() {
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    });
});</script>
