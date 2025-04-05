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
    </div>
</nav>
