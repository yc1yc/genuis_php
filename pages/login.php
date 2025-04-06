<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    $role = $_SESSION['user_role'] ?? 'client';
    if ($role === 'admin') {
        doRedirect('?page=admin');
    } else {
        doRedirect('?page=reservations');
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validation
    if (empty($email) || empty($password)) {
        $_SESSION['flash_error'] = "Veuillez remplir tous les champs";
    } else {
        try {
            $pdo = getPDO();
            
            // Rechercher l'utilisateur
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

                // Redirection selon le rôle
                if ($user['role'] === 'admin') {
                    doRedirect('?page=admin');
                } else {
                    doRedirect('?page=reservations');
                }

                // Gérer "Se souvenir de moi"
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                    
                    // Sauvegarder le token en base
                    $stmt = $pdo->prepare("
                        INSERT INTO remember_tokens (user_id, token, expires_at)
                        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
                    ");
                    $stmt->execute([$user['id'], $hashedToken]);
                    
                    // Définir le cookie
                    setcookie(
                        'remember_token',
                        $user['id'] . ':' . $token,
                        time() + (30 * 24 * 60 * 60), // 30 jours
                        '/',
                        '',
                        true, // Secure
                        true  // HttpOnly
                    );
                }

                // Mettre à jour last_login
                $stmt = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$user['id']]);

                $_SESSION['flash_success'] = "Connexion réussie";
                
                // Redirection basée sur le rôle
                if ($user['role'] === 'admin') {
                    doRedirect('?page=admin');
                } else {
                    // Si une redirection était prévue, l'utiliser, sinon aller à la page account
                    $redirectTo = $_SESSION['redirect_after_login'] ?? '?page=account';
                    unset($_SESSION['redirect_after_login']);
                    doRedirect($redirectTo);
                }
            } else {
                $_SESSION['flash_error'] = "Email ou mot de passe incorrect";
            }
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Une erreur est survenue lors de la connexion";
        }
    }
}
?>

<div class="login-container">
    <div class="login-header">
        <h1>Connexion</h1>
        <p>Connectez-vous pour accéder à votre compte</p>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo htmlspecialchars($_SESSION['flash_error']);
            unset($_SESSION['flash_error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo htmlspecialchars($_SESSION['flash_success']);
            unset($_SESSION['flash_success']);
            ?>
        </div>
    <?php endif; ?>

    <form class="login-form" action="" method="POST">
        <?php echo csrfField(); ?>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required 
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                   class="form-control"
                   placeholder="Votre adresse email">
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required
                   class="form-control"
                   placeholder="Votre mot de passe">
        </div>

        <div class="form-check">
            <input type="checkbox" id="remember" name="remember" class="form-check-input">
            <label for="remember" class="form-check-label">Se souvenir de moi</label>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Se connecter</button>

        <div class="login-footer">
            <p>Pas encore de compte ? <a href="?page=register">Inscrivez-vous</a></p>
            <p><a href="?page=forgot-password">Mot de passe oublié ?</a></p>
        </div>
    </form>
</div>
