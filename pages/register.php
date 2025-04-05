<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    doRedirect('?page=account');
}

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $postalCode = $_POST['postal_code'] ?? '';
    $country = $_POST['country'] ?? '';

    // Validation des champs
    $errors = [];

    if (empty($firstName)) {
        $errors[] = "Le prénom est requis";
    }

    if (empty($lastName)) {
        $errors[] = "Le nom est requis";
    }

    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }

    if (empty($password)) {
        $errors[] = "Le mot de passe est requis";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }

    if (empty($phone)) {
        $errors[] = "Le numéro de téléphone est requis";
    }

    if (empty($address)) {
        $errors[] = "L'adresse est requise";
    }

    if (empty($city)) {
        $errors[] = "La ville est requise";
    }

    if (empty($postalCode)) {
        $errors[] = "Le code postal est requis";
    }

    if (empty($country)) {
        $errors[] = "Le pays est requis";
    }

    // Si pas d'erreurs, on crée l'utilisateur
    if (empty($errors)) {
        try {
            $pdo = getPDO();
            
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $_SESSION['flash_error'] = "Cette adresse email est déjà utilisée";
            } else {
                // Créer l'utilisateur
                $stmt = $pdo->prepare("
                    INSERT INTO users (first_name, last_name, email, password, phone, address, city, postal_code, country, role)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'client')
                ");
                
                $stmt->execute([
                    $firstName,
                    $lastName,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $phone,
                    $address,
                    $city,
                    $postalCode,
                    $country
                ]);

                $_SESSION['flash_success'] = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
                doRedirect('?page=login');
            }
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Une erreur est survenue lors de la création du compte";
        }
    } else {
        $_SESSION['flash_error'] = implode("<br>", $errors);
    }
}
?>

<div class="login-container">
    <div class="login-header">
        <h1>Inscription</h1>
        <p>Créez votre compte pour commencer à louer des véhicules</p>
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
        
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">Prénom</label>
                <input type="text" id="first_name" name="first_name" required
                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                       class="form-control">
            </div>
            
            <div class="form-group">
                <label for="last_name">Nom</label>
                <input type="text" id="last_name" name="last_name" required
                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                       class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                   class="form-control">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required
                       class="form-control">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="phone">Téléphone</label>
            <input type="tel" id="phone" name="phone" required
                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                   class="form-control">
        </div>

        <div class="form-group">
            <label for="address">Adresse</label>
            <input type="text" id="address" name="address" required
                   value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"
                   class="form-control">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="city">Ville</label>
                <input type="text" id="city" name="city" required
                       value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>"
                       class="form-control">
            </div>
            
            <div class="form-group">
                <label for="postal_code">Code postal</label>
                <input type="text" id="postal_code" name="postal_code" required
                       value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>"
                       class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="country">Pays</label>
            <input type="text" id="country" name="country" required
                   value="<?php echo htmlspecialchars($_POST['country'] ?? ''); ?>"
                   class="form-control">
        </div>

        <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>

        <div class="login-footer">
            <p>Déjà inscrit ? <a href="?page=login">Connectez-vous</a></p>
        </div>
    </form>
</div>
