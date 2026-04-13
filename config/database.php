<?php
// Configuració de la base de dades
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'jardins_llica');
define('DB_CHARSET', 'utf8mb4');

// ═════════════════════════════════════════════════════════════════════════════
// CANVIA AQUESTS VALORS amb les teves dades
// ═════════════════════════════════════════════════════════════════════════════

// URL del teu lloc web (sense barra final)
define('SITE_URL', 'https://el-teu-domini.infinityfreeapp.com');

// ── SMTP de Brevo (brevo.com) ─────────────────────────────────────────────────
// A Brevo: Settings → SMTP & API → pestanya SMTP → "Generate a new SMTP Key"
//
// SMTP_HOST   → sempre: smtp-relay.brevo.com
// SMTP_PORT   → sempre: 587
// SMTP_SECURE → sempre: tls
// SMTP_USER   → el teu correu de registre a Brevo (ex: erikmunuera@gmail.com)
// SMTP_PASS   → la clau SMTP generada a Brevo (NO la contrasenya del compte)
define('SMTP_HOST',   'smtp-relay.brevo.com');
define('SMTP_PORT',   587);
define('SMTP_SECURE', 'tls');
define('SMTP_USER',   'el-teu-correu-de-brevo@gmail.com');  // ← canvia això
define('SMTP_PASS',   'LA-TEVA-CLAU-SMTP-DE-BREVO');        // ← canvia això

// Correu remitent (ha d'estar verificat a Brevo: Settings → Senders & IPs)
define('MAIL_FROM',      'noreplyjardinsdellica@gmail.com'); // ← el que tens verificat
define('MAIL_FROM_NAME', 'Jardins de Lliçà');

// ═════════════════════════════════════════════════════════════════════════════

define('SITE_NAME',   'Jardins de Lliçà');
define('ADMIN_EMAIL', MAIL_FROM);

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
