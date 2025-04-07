<?php
function getPDO() {
    static $pdo = null;
    
    if ($pdo === null) {
        $host = 'localhost';
        $dbname = 'genuis_rental';
        $username = 'root';
        $password = '';
        
        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
    
    return $pdo;
}

function setFlashMessage($type, $message) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['flash_' . $type] = $message;
}

function getFlashMessage($type) {
    if (!isset($_SESSION)) {
        session_start();
    }
    $message = $_SESSION['flash_' . $type] ?? null;
    unset($_SESSION['flash_' . $type]);
    return $message;
}

function displayFlashMessages() {
    $types = ['success', 'error', 'warning', 'info'];
    $output = '';
    
    foreach ($types as $type) {
        if ($message = getFlashMessage($type)) {
            $class = $type === 'error' ? 'danger' : $type;
            $output .= "<div class='alert alert-{$class}'>" . htmlspecialchars($message) . "</div>";
        }
    }
    
    return $output;
}

function slugify($text) {
    // Remplacer les caractères non alphanumériques par des tirets
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);

    // Translitérer
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // Supprimer les caractères indésirables
    $text = preg_replace('~[^-\w]+~', '', $text);

    // Supprimer les tirets en début et fin
    $text = trim($text, '-');

    // Convertir en minuscules
    $text = strtolower($text);

    return empty($text) ? 'n-a' : $text;
}

function generateUniqueSlug($table, $field, $text) {
    $slug = slugify($text);
    $originalSlug = $slug;
    $counter = 1;
    
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    
    while (true) {
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() == 0) {
            break;
        }
        $slug = $originalSlug . '-' . $counter++;
    }
    
    return $slug;
}

function uploadImage($file, $destination, $maxSize = 5242880, $allowedTypes = ['image/jpeg', 'image/png']) {
    // Vérifier s'il y a une erreur
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erreur lors du téléchargement du fichier.');
    }

    // Vérifier le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Type de fichier non autorisé.');
    }

    // Vérifier la taille
    if ($file['size'] > $maxSize) {
        throw new Exception('Le fichier est trop volumineux.');
    }

    // Générer un nom de fichier unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $targetPath = $destination . '/' . $filename;

    // Créer le dossier de destination s'il n'existe pas
    if (!is_dir($destination)) {
        mkdir($destination, 0777, true);
    }

    // Déplacer le fichier
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Erreur lors de l\'enregistrement du fichier.');
    }

    return $filename;
}

function deleteImage($filename, $directory) {
    $filepath = $directory . '/' . $filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}

function resizeImage($sourcePath, $targetPath, $maxWidth = 800, $maxHeight = 600) {
    list($width, $height) = getimagesize($sourcePath);
    
    // Calculer les nouvelles dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = round($width * $ratio);
    $newHeight = round($height * $ratio);
    
    // Créer une nouvelle image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Charger l'image source
    $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
    switch ($extension) {
        case 'jpeg':
        case 'jpg':
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case 'png':
            $source = imagecreatefrompng($sourcePath);
            break;
        default:
            throw new Exception('Format d\'image non supporté');
    }
    
    // Redimensionner
    imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Sauvegarder
    switch ($extension) {
        case 'jpeg':
        case 'jpg':
            imagejpeg($newImage, $targetPath, 90);
            break;
        case 'png':
            imagepng($newImage, $targetPath, 9);
            break;
    }
    
    // Libérer la mémoire
    imagedestroy($source);
    imagedestroy($newImage);
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

