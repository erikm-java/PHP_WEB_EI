<?php
/**
 * SmtpMailer – Enviament de correus via SMTP sense dependències externes.
 * Compatible amb SSL (port 465) i STARTTLS (port 587).
 */
class SmtpMailer {
    private string $host;
    private int    $port;
    private string $user;
    private string $pass;
    private string $secure; // 'ssl' | 'tls' | ''
    /** @var resource|false */
    private $conn = false;
    private string $lastResp = '';

    public function __construct(string $host, int $port, string $user, string $pass, string $secure = 'tls') {
        $this->host   = $host;
        $this->port   = $port;
        $this->user   = $user;
        $this->pass   = $pass;
        $this->secure = strtolower($secure);
    }

    private function connect(): bool {
        $ctx = stream_context_create(['ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ]]);

        $prefix = ($this->secure === 'ssl') ? 'ssl://' : '';
        $this->conn = stream_socket_client(
            "{$prefix}{$this->host}:{$this->port}",
            $errno, $errstr, 15,
            STREAM_CLIENT_CONNECT,
            $ctx
        );

        if (!$this->conn) {
            error_log("SmtpMailer: no s'ha pogut connectar a {$this->host}:{$this->port} – {$errstr} ({$errno})");
            return false;
        }
        stream_set_timeout($this->conn, 15);
        $this->read(); // Llegir salutació del servidor (220)
        return true;
    }

    private function cmd(string $command): string {
        fwrite($this->conn, $command . "\r\n");
        return $this->read();
    }

    private function read(): string {
        $resp = '';
        while ($line = fgets($this->conn, 1024)) {
            $resp .= $line;
            // Última línia de la resposta: el 4t caràcter és espai
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        $this->lastResp = $resp;
        return $resp;
    }

    private function code(string $resp = ''): int {
        return (int) substr($resp ?: $this->lastResp, 0, 3);
    }

    /**
     * Envia un correu HTML via SMTP.
     *
     * @return bool  true si s'ha enviat, false en cas d'error
     */
    public function send(string $to, string $subject, string $htmlBody, string $from, string $fromName = ''): bool {
        if (!$this->connect()) return false;

        $hostname = gethostname() ?: 'localhost';
        $this->cmd("EHLO {$hostname}");

        // STARTTLS (per a connexions TLS en port 587)
        if ($this->secure === 'tls') {
            $r = $this->cmd("STARTTLS");
            if ($this->code($r) !== 220) {
                error_log("SmtpMailer: STARTTLS refusat – {$r}");
                fclose($this->conn);
                return false;
            }
            $ctx = stream_context_create(['ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ]]);
            stream_socket_enable_crypto($this->conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->cmd("EHLO {$hostname}"); // Tornar a identificar-se després de TLS
        }

        // Autenticació
        $this->cmd("AUTH LOGIN");
        $this->cmd(base64_encode($this->user));
        $r = $this->cmd(base64_encode($this->pass));
        if ($this->code($r) !== 235) {
            error_log("SmtpMailer: autenticació fallida – {$r}");
            fclose($this->conn);
            return false;
        }

        // Envolupant (envelope)
        $this->cmd("MAIL FROM:<{$from}>");
        $r = $this->cmd("RCPT TO:<{$to}>");
        if ($this->code($r) !== 250) {
            error_log("SmtpMailer: RCPT TO refusat – {$r}");
            fclose($this->conn);
            return false;
        }

        // Inici de dades
        $this->cmd("DATA");

        // Capçaleres del missatge
        $subjectEncoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $fromHeader     = $fromName
            ? ('=?UTF-8?B?' . base64_encode($fromName) . '?= <' . $from . '>')
            : $from;

        $msg  = "From: {$fromHeader}\r\n";
        $msg .= "To: {$to}\r\n";
        $msg .= "Subject: {$subjectEncoded}\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n";
        $msg .= "\r\n";
        $msg .= chunk_split(base64_encode($htmlBody), 76, "\r\n");

        fwrite($this->conn, $msg);
        fwrite($this->conn, ".\r\n"); // Fi de DATA

        $r = $this->read();
        if ($this->code($r) !== 250) {
            error_log("SmtpMailer: enviament fallat – {$r}");
            fclose($this->conn);
            return false;
        }

        $this->cmd("QUIT");
        fclose($this->conn);
        return true;
    }
}
