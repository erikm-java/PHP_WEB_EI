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
// Exemple InfinityFree: 'https://jardinsllica.infinityfreeapp.com'
// Exemple domini propi: 'https://jardinsllica.cat'
define('SITE_URL', 'https://el-teu-domini.infinityfreeapp.com');

// ── Brevo (brevo.com) – servei gratuït, 300 correus/dia ──────────────────────
// Pas 1: Registra't a https://app.brevo.com (gratuït, sense targeta)
// Pas 2: Ves a Settings → SMTP & API → API Keys → "Create a new API key"
// Pas 3: Copia la clau i enganxa-la aquí:
define('BREVO_API_KEY', 'xkeysib-POSA-LA-TEVA-CLAU-AQUI');

// Correu remitent (ha d'estar verificat a Brevo: Settings → Senders & IPs)
// Pots usar el teu Gmail o qualsevol correu al qual tinguis accés
define('MAIL_FROM',      'el-teu-correu@gmail.com');
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
