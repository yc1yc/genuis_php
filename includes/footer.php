        <?php
if (!defined('FOOTER_INCLUDED')) {
    define('FOOTER_INCLUDED', true);
?>
        <footer class="main-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-section">
                <h3>The Genuis</h3>
                <p>Votre partenaire de confiance pour la location de véhicules. Nous vous offrons une large gamme de véhicules pour tous vos besoins.</p>
                <div class="social-links">
                    <a href="#" class="social-link" aria-label="Facebook">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Contact</h3>
                <ul class="contact-list">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        123 Avenue des Voitures, 75000 Paris
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        +33 1 23 45 67 89
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        contact@thegenuis.com
                    </li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Liens rapides</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="index.php?page=reservation">Réservation</a></li>
                    <li><a href="index.php?page=about">À propos</a></li>
                    <li><a href="index.php?page=contact">Contact</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Horaires</h3>
                <ul class="hours-list">
                    <li>
                        <span>Lundi - Vendredi:</span>
                        <span>8h00 - 19h00</span>
                    </li>
                    <li>
                        <span>Samedi:</span>
                        <span>9h00 - 17h00</span>
                    </li>
                    <li>
                        <span>Dimanche:</span>
                        <span>Fermé</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> The Genuis. Tous droits réservés.</p>
            <div class="footer-bottom-links">
                <a href="#">Mentions légales</a>
                <a href="#">Politique de confidentialité</a>
                <a href="#">CGV</a>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
<?php
}
?>
