<?php
// Minimal SMTP sender over SSL (port 465) with AUTH LOGIN for WAMP
// Relies on smtp_config.php values

function send_smtp_mail($toEmail, $toName, $subject, $bodyHtml) {
    $cfg = require __DIR__ . '/smtp_config.php';

    $host   = $cfg['SMTP_HOST'] ?? '';
    $port   = (int)($cfg['SMTP_PORT'] ?? 465);
    $secure = strtolower($cfg['SMTP_SECURE'] ?? 'ssl');
    $user   = $cfg['SMTP_USER'] ?? '';
    $pass   = $cfg['SMTP_PASS'] ?? '';
    $from   = ($cfg['FROM_EMAIL'] ?? '') ?: $user;
    $fromName = $cfg['FROM_NAME'] ?? 'SLATE FMS';

    $missing = [];
    if (!$host) $missing[] = 'SMTP_HOST';
    if (!$port) $missing[] = 'SMTP_PORT';
    if (!$user) $missing[] = 'SMTP_USER';
    if (!$pass) $missing[] = 'SMTP_PASS';
    if (!$from) $missing[] = 'FROM_EMAIL (or set SMTP_USER)';
    if (!empty($missing)) { return [false, 'SMTP configuration is incomplete: missing ' . implode(', ', $missing)]; }

    $remote = ($secure === 'ssl') ? 'ssl://' . $host . ':' . $port : $host . ':' . $port;
    $contextOptions = [];
    if ($secure === 'ssl') {
        $contextOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
    }

    $socket = @stream_socket_client(
        $secure === 'ssl' ? 'ssl://' . $host . ':' . $port : $host . ':' . $port,
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT,
        stream_context_create($contextOptions)
    );

    if (!$socket) {
        return [false, 'SMTP connection failed: ' . $errstr . ' (' . $errno . ')'];
    }

    stream_set_timeout($socket, 15);

    $read = function() use ($socket) {
        $data = '';
        while ($str = fgets($socket, 515)) {
            $data .= $str;
            if (isset($str[3]) && $str[3] === ' ') break; // End of reply
        }
        return $data;
    };

    $write = function($cmd) use ($socket) {
        fwrite($socket, $cmd . "\r\n");
    };

    $resp = $read();
    if (strpos($resp, '220') !== 0) { fclose($socket); return [false, 'Invalid SMTP banner: ' . trim($resp)]; }

    $localhost = 'localhost';
    $write('EHLO ' . $localhost);
    $resp = $read();
    if (strpos($resp, '250') !== 0) {
        $write('HELO ' . $localhost);
        $resp = $read();
        if (strpos($resp, '250') !== 0) { fclose($socket); return [false, 'HELO/EHLO rejected: ' . trim($resp)]; }
    }

    if ($secure === 'tls') {
        $write('STARTTLS');
        $resp = $read();
        if (strpos($resp, '220') !== 0) { fclose($socket); return [false, 'STARTTLS failed: ' . trim($resp)]; }
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket); return [false, 'TLS negotiation failed'];
        }
        $write('EHLO ' . $localhost);
        $resp = $read();
        if (strpos($resp, '250') !== 0) { fclose($socket); return [false, 'EHLO after STARTTLS failed: ' . trim($resp)]; }
    }

    $write('AUTH LOGIN');
    $resp = $read();
    if (strpos($resp, '334') !== 0) { fclose($socket); return [false, 'AUTH LOGIN rejected: ' . trim($resp)]; }

    $write(base64_encode($user));
    $resp = $read();
    if (strpos($resp, '334') !== 0) { fclose($socket); return [false, 'Username not accepted: ' . trim($resp)]; }

    $write(base64_encode($pass));
    $resp = $read();
    if (strpos($resp, '235') !== 0) { fclose($socket); return [false, 'Password not accepted: ' . trim($resp)]; }

    $write('MAIL FROM: <' . $from . '>');
    $resp = $read();
    if (strpos($resp, '250') !== 0) { fclose($socket); return [false, 'MAIL FROM failed: ' . trim($resp)]; }

    $write('RCPT TO: <' . $toEmail . '>');
    $resp = $read();
    if (strpos($resp, '250') !== 0 && strpos($resp, '251') !== 0) { fclose($socket); return [false, 'RCPT TO failed: ' . trim($resp)]; }

    $write('DATA');
    $resp = $read();
    if (strpos($resp, '354') !== 0) { fclose($socket); return [false, 'DATA command rejected: ' . trim($resp)]; }

    $boundary = 'b_' . bin2hex(random_bytes(8));
    $headers = [];
    $headers[] = 'From: ' . ($fromName ? (encodeHeaderName($fromName) . ' <' . $from . '>') : $from);
    $headers[] = 'To: ' . ($toName ? (encodeHeaderName($toName) . ' <' . $toEmail . '>') : $toEmail);
    $headers[] = 'Subject: ' . encodeHeaderName($subject);
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';

    $message = implode("\r\n", $headers) . "\r\n\r\n" . $bodyHtml . "\r\n.";

    $write($message);
    $resp = $read();
    if (strpos($resp, '250') !== 0) { fclose($socket); return [false, 'Message not accepted: ' . trim($resp)]; }

    $write('QUIT');
    fclose($socket);
    return [true, 'Sent'];
}

function encodeHeaderName($str) {
    // Simplified UTF-8 header encoding
    return '=?UTF-8?B?' . base64_encode($str) . '?=';
}
