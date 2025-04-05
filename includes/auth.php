<?php
if (!defined('AUTH_INCLUDED')) {
    define('AUTH_INCLUDED', true);

    require_once __DIR__ . '/functions.php';

    // Démarrer la session si ce n'est pas déjà fait
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    function doRedirect($url) {
        // Ajouter le préfixe /genuis_php/ si l'URL commence par ?
        if (strpos($url, '?') === 0) {
            $url = '/genuis_php/' . $url;
        }
        // Ajouter le préfixe /genuis_php si l'URL ne commence pas par /genuis_php/ ou http
        elseif (strpos($url, '/genuis_php/') !== 0 && strpos($url, 'http') !== 0) {
            $url = '/genuis_php/' . ltrim($url, '/');
        }

        if (!headers_sent()) {
            header('Location: ' . $url);
            exit;
        }
        
        echo '<script>window.location.href="' . htmlspecialchars($url) . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url) . '"></noscript>';
        exit;
    }

    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    function checkRememberMe() {
        if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
            $pdo = getPDO();
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? AND is_active = 1");
            $stmt->execute([$_COOKIE['remember_token']]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['role'] = $user['role'];

                // Renouvellement du token
                $newToken = bin2hex(random_bytes(32));
                
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->execute([$newToken, $user['id']]);
                
                setcookie('remember_token', $newToken, time() + (30 * 24 * 60 * 60), '/');
                return true;
            }
            
            // Si le token n'est pas valide, le supprimer
            setcookie('remember_token', '', time() - 3600, '/');
        }
        return false;
    }

    function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            logout();
            return null;
        }
        
        return $user;
    }

    function getUserAvatar() {
        $user = getCurrentUser();
        if (!$user) {
            return '/genuis_php/assets/img/default-avatar.png';
        }
        
        if (!empty($user['avatar'])) {
            return '/genuis_php/uploads/avatars/' . $user['avatar'];
        }
        
        // Générer un avatar avec les initiales
        $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
        $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEEAD', '#D4A5A5', '#9B59B6', '#3498DB'];
        $colorIndex = abs(crc32($user['email'])) % count($colors);
        $bgColor = $colors[$colorIndex];
        
        return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&background=" . substr($bgColor, 1) . "&color=fff&size=256";
    }

    function requireAuth($role = null) {
        if (!isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            doRedirect('/genuis_php/?page=login');
        }

        $user = getCurrentUser();
        if (!$user) {
            doRedirect('/genuis_php/?page=login');
        }
        
        if ($role !== null && $user['role'] !== $role) {
            doRedirect('/genuis_php/?page=403');
        }
        
        return $user;
    }

    function authenticateUser($email, $password, $remember = false) {
        $pdo = getPDO();
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        // Mise à jour de la dernière connexion
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Création de la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['role'] = $user['role'];

        // Gestion du "Se souvenir de moi"
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$token, $user['id']]);
            
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
        }

        return true;
    }

    function logout() {
        // Détruire toutes les variables de session
        $_SESSION = array();

        // Détruire la session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        // Supprimer le cookie de session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        // Supprimer le cookie "remember me" s'il existe
        if (isset($_COOKIE['remember_token'])) {
            $pdo = getPDO();
            $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
            if (isset($_SESSION['user_id'])) {
                $stmt->execute([$_SESSION['user_id']]);
            }
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // Forcer l'expiration de tous les cookies
        foreach ($_COOKIE as $name => $value) {
            setcookie($name, '', time() - 3600, '/');
        }

        doRedirect('/genuis_php/index.php');
    }

    function redirect($page, $message = null) {
        if ($message) {
            setFlashMessage($message);
        }
        doRedirect("/genuis_php/?page=$page");
    }

    function csrfField() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }

    function verifyCsrfToken() {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
            $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            header('HTTP/1.0 403 Forbidden');
            exit('Invalid CSRF token');
        }
    }

    function formatDate($date) {
        return date('d/m/Y', strtotime($date));
    }

    function formatDateTime($date) {
        return date('d/m/Y \à h:i A', strtotime($date));
    }

    function formatPrice($price) {
        return number_format($price, 2, ',', ' ') . ' €';
    }
}
