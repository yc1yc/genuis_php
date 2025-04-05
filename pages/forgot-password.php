<?php
require_once 'includes/auth.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect('account');
}
?>

<main class="auth-page">
    <div class="container">
        <div class="auth-form">
            <h1>Mot de passe oublié</h1>
            
            <?php displayFlashMessage(); ?>

            <form id="forgotPasswordForm" method="POST" action="api/auth/forgot-password.php">
                <?php echo csrfField(); ?>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
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
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');

    forgotPasswordForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = 'Envoi en cours...';

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
                alert('Un email de réinitialisation vous a été envoyé.');
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
});</script>
