<?php
/**
 * FITXER DE DIAGNÒSTIC – Esborra'l del hosting un cop fet el test!
 */
require_once 'config/database.php';
require_once 'includes/smtp_mailer.php';

echo '<pre style="font-family:monospace; background:#1e1e1e; color:#d4d4d4; padding:20px; font-size:14px;">';
echo "=== TEST D'ENVIAMENT DE CORREU (SMTP Brevo) ===\n\n";

echo "── CONFIG ──────────────────────────────\n";
echo "SITE_URL      : " . SITE_URL . "\n";
echo "SMTP_HOST     : " . SMTP_HOST . "\n";
echo "SMTP_PORT     : " . SMTP_PORT . "\n";
echo "SMTP_SECURE   : " . SMTP_SECURE . "\n";
echo "SMTP_USER     : " . SMTP_USER . "\n";
echo "SMTP_PASS     : " . substr(SMTP_PASS, 0, 4) . str_repeat('*', max(0, strlen(SMTP_PASS) - 4)) . "\n";
echo "MAIL_FROM     : " . MAIL_FROM . "\n\n";

echo "── TEST ENVIAMENT ───────────────────────\n";
echo "Enviant correu de prova a: " . MAIL_FROM . "\n";

$mailer = new SmtpMailer(SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE);
$ok = $mailer->send(
    MAIL_FROM,
    '[TEST] Correu de prova - Jardins de Lliçà',
    '<h2>Test correcte!</h2><p>Si veus aquest correu, l\'enviament SMTP funciona.</p>',
    MAIL_FROM,
    MAIL_FROM_NAME
);

if ($ok) {
    echo "[OK] Correu enviat correctament!\n";
    echo "     Comprova la safata de " . MAIL_FROM . " (i la carpeta de spam)\n";
} else {
    echo "[ERROR] No s'ha pogut enviar. Comprova el log d'errors del hosting.\n";
    echo "        Revisa que SMTP_USER i SMTP_PASS siguin correctes.\n";
}

echo "\n\n⚠️  RECORDA ESBORRAR AQUEST FITXER DEL HOSTING!\n";
echo '</pre>';
