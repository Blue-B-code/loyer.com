<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gestion_loyers');

// Configuration de l'application
define('SITE_NAME', 'Gestion des Loyers');
define('SITE_URL', 'http://localhost/loyer.com');

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Démarrer la session
session_start();

// Fonctions utilitaires
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($page) {
    header("Location: " . SITE_URL . "/$page");
    exit();
}
?>
