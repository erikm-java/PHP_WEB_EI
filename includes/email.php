<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Envia un correu electrònic amb capçaleres HTML.
 * En un entorn de producció caldria configurar un servidor SMTP
 * o usar PHPMailer/SendGrid/etc.
 */
function sendEmail(string $to, string $subject, string $body): bool {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
    $headers .= "Reply-To: " . MAIL_FROM . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    $fullBody = '<!DOCTYPE html>
<html lang="ca">
<head><meta charset="UTF-8"><style>
body { font-family: Arial, sans-serif; color: #333; background: #f5f5f5; }
.container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,.1); }
.header { background: #2e7d32; color: #fff; padding: 24px 32px; }
.header h1 { margin: 0; font-size: 22px; }
.content { padding: 32px; }
.footer { background: #f0f0f0; padding: 16px 32px; text-align: center; font-size: 12px; color: #777; }
.btn { display: inline-block; background: #2e7d32; color: #fff; padding: 12px 24px; border-radius: 4px; text-decoration: none; margin-top: 16px; }
table { width: 100%; border-collapse: collapse; margin: 16px 0; }
th { background: #e8f5e9; text-align: left; padding: 8px; }
td { padding: 8px; border-bottom: 1px solid #eee; }
.total { font-weight: bold; font-size: 18px; }
</style></head>
<body>
<div class="container">
  <div class="header"><h1>🌿 Jardins de Lliçà</h1></div>
  <div class="content">' . $body . '</div>
  <div class="footer">Jardins de Lliçà · Carrer Major, 15, Lliçà d\'Amunt · Tel: 938 428 000<br>
  Aquest correu és automàtic, si us plau no respongueu.</div>
</div>
</body></html>';

    return @mail($to, $subject, $fullBody, $headers);
}

/** Correu de benvinguda / confirmació de registre */
function sendWelcomeEmail(string $to, string $username): void {
    $subject = 'Benvingut/da a Jardins de Lliçà!';
    $body = '<h2>Hola, ' . htmlspecialchars($username) . '!</h2>
<p>El teu compte s\'ha creat correctament a <strong>Jardins de Lliçà</strong>.</p>
<p>Ja pots iniciar sessió i descobrir tot el nostre catàleg de plantes, llavors i productes de jardineria.</p>
<a class="btn" href="' . SITE_URL . '/login.php">Inicia sessió</a>
<p style="margin-top:24px; font-size:13px; color:#666;">Si no has creat aquest compte, ignora aquest missatge.</p>';
    sendEmail($to, $subject, $body);
}

/** Correu de confirmació de compra */
function sendOrderEmail(string $to, string $name, int $orderId, array $items, float $total): void {
    $subject = 'Confirmació de la teva comanda #' . $orderId . ' - Jardins de Lliçà';
    $rows = '';
    foreach ($items as $item) {
        $rows .= '<tr>
            <td>' . htmlspecialchars($item['product_name']) . '</td>
            <td style="text-align:center">' . $item['quantity'] . '</td>
            <td style="text-align:right">' . number_format($item['price'], 2) . ' €</td>
            <td style="text-align:right">' . number_format($item['price'] * $item['quantity'], 2) . ' €</td>
        </tr>';
    }
    $body = '<h2>Gràcies per la teva compra, ' . htmlspecialchars($name) . '!</h2>
<p>Hem rebut la teva comanda i la processarem el més aviat possible.</p>
<p><strong>Número de comanda: #' . $orderId . '</strong></p>
<table>
  <thead><tr><th>Producte</th><th>Quantitat</th><th>Preu unit.</th><th>Total</th></tr></thead>
  <tbody>' . $rows . '</tbody>
  <tfoot><tr><td colspan="3" style="text-align:right"><strong>TOTAL:</strong></td>
  <td style="text-align:right" class="total">' . number_format($total, 2) . ' €</td></tr></tfoot>
</table>
<p>En breu rebràs un correu amb la informació d\'enviament.</p>
<a class="btn" href="' . SITE_URL . '">Visita la nostra botiga</a>';
    sendEmail($to, $subject, $body);
}

/** Correu de recuperació de clau */
function sendPasswordResetEmail(string $to, string $token): void {
    $link = SITE_URL . '/recover-password.php?token=' . urlencode($token);
    $subject = 'Recuperació de clau - Jardins de Lliçà';
    $body = '<h2>Recuperació de la clau d\'accés</h2>
<p>Hem rebut una sol·licitud per a restablir la clau del teu compte a Jardins de Lliçà.</p>
<p>Fes clic al botó per a crear una nova clau. L\'enllaç és vàlid durant <strong>1 hora</strong>.</p>
<a class="btn" href="' . $link . '">Restablir la clau</a>
<p style="margin-top:24px; font-size:13px; color:#666;">Si no has sol·licitat la recuperació de la clau, ignora aquest missatge.</p>';
    sendEmail($to, $subject, $body);
}
