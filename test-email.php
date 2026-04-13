<?php
/**
 * FITXER DE DIAGNÒSTIC – Esborra'l del hosting un cop fet el test!
 * Accedeix-hi al navegador: https://el-teu-domini/test-email.php
 */
require_once 'config/database.php';

echo '<pre style="font-family:monospace; background:#1e1e1e; color:#d4d4d4; padding:20px; font-size:14px;">';
echo "=== TEST D'ENVIAMENT DE CORREU ===\n\n";

// 1. Comprovació de constants
echo "── CONFIG ──────────────────────────────\n";
echo "SITE_URL      : " . SITE_URL . "\n";
echo "MAIL_FROM     : " . MAIL_FROM . "\n";
echo "MAIL_FROM_NAME: " . MAIL_FROM_NAME . "\n";
$keyMasked = substr(BREVO_API_KEY, 0, 12) . str_repeat('*', max(0, strlen(BREVO_API_KEY) - 12));
echo "BREVO_API_KEY : " . $keyMasked . "\n\n";

// 2. Comprovació de curl
echo "── CURL ─────────────────────────────────\n";
if (!function_exists('curl_init')) {
    echo "[ERROR] curl NO disponible en aquest hosting.\n\n";
} else {
    echo "[OK] curl disponible\n";
    echo "     versió: " . curl_version()['version'] . "\n\n";
}

// 3. Test de connexió a Brevo
echo "── TEST CONNEXIÓ A BREVO ────────────────\n";
$ch = curl_init('https://api.brevo.com/v3/account');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['api-key: ' . BREVO_API_KEY],
    CURLOPT_TIMEOUT        => 10,
]);
$resp     = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo "[ERROR] No s'ha pogut connectar a Brevo: {$curlErr}\n";
    echo "        → InfinityFree probablement bloqueja les connexions sortints.\n\n";
} elseif ($httpCode === 200) {
    $data = json_decode($resp, true);
    echo "[OK] Connexió a Brevo correcta\n";
    echo "     Compte: " . ($data['email'] ?? '?') . "\n";
    echo "     Pla: "    . ($data['plan'][0]['type'] ?? '?') . "\n\n";
} elseif ($httpCode === 401) {
    echo "[ERROR] API Key incorrecta o invàlida (HTTP 401)\n";
    echo "        Resposta: {$resp}\n\n";
} else {
    echo "[ERROR] HTTP {$httpCode}\n";
    echo "        Resposta: {$resp}\n\n";
}

// 4. Enviament de correu de prova
if ($httpCode === 200 && !$curlErr) {
    $destinatari = MAIL_FROM; // S'envia al mateix remitent com a prova

    echo "── TEST ENVIAMENT ───────────────────────\n";
    echo "Enviant correu de prova a: {$destinatari}\n";

    $payload = json_encode([
        'sender'      => ['name' => MAIL_FROM_NAME, 'email' => MAIL_FROM],
        'to'          => [['email' => $destinatari]],
        'subject'     => '[TEST] Correu de prova - Jardins de Lliçà',
        'htmlContent' => '<h2>Test correcte!</h2><p>Si veus aquest correu, l\'enviament funciona perfectament.</p>',
    ]);

    $ch2 = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'accept: application/json',
            'content-type: application/json',
            'api-key: ' . BREVO_API_KEY,
        ],
        CURLOPT_TIMEOUT => 15,
    ]);
    $resp2     = curl_exec($ch2);
    $code2     = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    $curlErr2  = curl_error($ch2);
    curl_close($ch2);

    if ($curlErr2) {
        echo "[ERROR] curl: {$curlErr2}\n";
    } elseif ($code2 >= 200 && $code2 < 300) {
        echo "[OK] Correu enviat! HTTP {$code2}\n";
        echo "     Comprova la safata de {$destinatari} (i la carpeta de spam)\n";
    } else {
        echo "[ERROR] HTTP {$code2}\n";
        echo "        Resposta Brevo: {$resp2}\n";
    }
}

echo "\n\n⚠️  RECORDA ESBORRAR AQUEST FITXER DEL HOSTING!\n";
echo '</pre>';
