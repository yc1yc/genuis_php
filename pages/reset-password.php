<?php
require_once 'includes/auth.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect('account');
}

// Vérifier le token
$token = $_GET['token'] ?? '';
if (!$token || !verifyPasswordResetToken($token)) {
    setFlashMessage('error', 'Le lien de réinitialisation est invalide ou a expiré.');
    redirect('login');
}
?>

<main class="auth-page">
    <div class="container">
        <div class="auth-form">
            <h1>Réinitialisation du mot de passe</h1>
            
            <?php displayFlashMessage(); ?>

            <form id="resetPasswordForm" method="POST" action="api/auth/reset-password.php">
                <?php echo csrfField(); ?>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password" required
                           pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$"
                           title="Le mot de passe doit contenir au moins 8 caractères, dont une lettre et un chiffre">
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Réinitialiser le mot de passe</button>
            </form>

            <div class="auth-links">
                <a href="index.php?page=login">Retour à la connexion</a>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');

    resetPasswordForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Vérification des mots de passe
        if (password.value !== passwordConfirm.value) {
            alert('Les mots de passe ne correspondent pas');
            return;
        }

        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = 'Réinitialisation en cours...';

        try {
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(new FormData(this))
            });

            const data = await response.json();

            if (data.success) {
                alert('Votre mot de passe a été réinitialisé avec succès.');
                window.location.href = 'index.php?page=login';
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            alert(error.message || 'Une erreur est survenue');
            submitButton.disabled = false;
            submitButton.innerHTML = 'Réinitialiser le mot de passe';
        }
    });

    // Validation en temps réel des mots de passe
    passwordConfirm.addEventListener('input', function() {
        if (password.value !== this.value) {
            this.setCustomValidity('Les mots de passe ne correspondent pas');
        } else {
            this.setCustomValidity('');
        }
    });
});</script>
