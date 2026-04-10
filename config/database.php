<?php
// Configuració de la base de dades
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'jardins_llica');
define('DB_CHARSET', 'utf8mb4');

// Configuració del correu
define('MAIL_FROM', 'noreply@jardinsllica.cat');
define('MAIL_FROM_NAME', 'Jardins de Lliçà');
define('SITE_NAME', 'Jardins de Lliçà');
define('SITE_URL', 'http://localhost/PHP_WEB_EI');
define('ADMIN_EMAIL', 'admin@jardinsllica.cat');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div class="alert alert-danger m-4">Error de connexió a la base de dades. Si us plau, comproveu la configuració.<br><small>' . htmlspecialchars($e->getMessage()) . '</small></div>');
        }
    }
    return $pdo;
}
