<?php
// Configuració de la base de dades
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'jardins_llica');
define('DB_CHARSET', 'utf8mb4');

// ─────────────────────────────────────────────────────────────────────────────
// CANVIA AQUESTS VALORS amb les dades del teu hosting
// ─────────────────────────────────────────────────────────────────────────────

// URL del teu lloc web (sense barra final). Exemples:
//   'https://jardinsllica.cat'
//   'https://www.jardinsllica.cat'
//   'http://alumne.escola.cat'
define('SITE_URL', 'https://el-teu-domini.cat');

// ── Configuració SMTP (dades del teu hosting / cPanel) ───────────────────────
// Host SMTP del teu hosting (normalment 'mail.el-teu-domini.cat')
define('SMTP_HOST',     'mail.el-teu-domini.cat');
// Port: 465 → SSL  |  587 → TLS (STARTTLS)
define('SMTP_PORT',     465);
// Tipus de xifrat: 'ssl'  o  'tls'
define('SMTP_SECURE',   'ssl');
// Correu electrònic creat al teu hosting (ha d'existir al cPanel)
define('SMTP_USER',     'info@el-teu-domini.cat');
// Contrasenya d'aquest correu
define('SMTP_PASS',     'la-teva-contrasenya');

// ─────────────────────────────────────────────────────────────────────────────

define('MAIL_FROM',      SMTP_USER);
define('MAIL_FROM_NAME', 'Jardins de Lliçà');
define('SITE_NAME',      'Jardins de Lliçà');
define('ADMIN_EMAIL',    SMTP_USER);

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
