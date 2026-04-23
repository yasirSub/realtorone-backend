<?php

require __DIR__ . '/vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->load();

$host = $_ENV['MAIL_HOST'] ?? 'smtp-relay.brevo.com';
$port = (int) ($_ENV['MAIL_PORT'] ?? 587);
$user = $_ENV['MAIL_USERNAME'] ?? '';
$pass = $_ENV['MAIL_PASSWORD'] ?? '';
$from = $_ENV['MAIL_FROM_ADDRESS'] ?? $user;
$to = $argv[1] ?? 'yasir.subhani123@gmail.com';

$fp = stream_socket_client('tcp://' . $host . ':' . $port, $errno, $errstr, 20);
if (!$fp) {
    fwrite(STDERR, "CONNECT_FAIL {$errstr}\n");
    exit(1);
}

$read = static function ($fp): string {
    $out = '';
    while (($line = fgets($fp, 515)) !== false) {
        $out .= $line;
        if (preg_match('/^\d{3} /', $line)) {
            break;
        }
    }
    echo 'S: ' . trim(str_replace(PHP_EOL, ' | ', $out)) . PHP_EOL;
    return $out;
};

$send = static function ($fp, string $cmd): void {
    fwrite($fp, $cmd . "\r\n");
    echo 'C: ' . $cmd . PHP_EOL;
};

$read($fp);
$send($fp, 'EHLO realtorone.local');
$read($fp);
$send($fp, 'STARTTLS');
$read($fp);
stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
$send($fp, 'EHLO realtorone.local');
$read($fp);
$send($fp, 'AUTH LOGIN');
$read($fp);
$send($fp, base64_encode($user));
$read($fp);
$send($fp, base64_encode($pass));
$read($fp);

$subject = 'SMTP manual probe ' . date('Y-m-d H:i:s');
$body = 'Manual SMTP probe from RealtorOne backend.';

$send($fp, 'MAIL FROM:<' . $from . '>');
$read($fp);
$send($fp, 'RCPT TO:<' . $to . '>');
$read($fp);
$send($fp, 'DATA');
$read($fp);

$msg = 'Subject: ' . $subject . "\r\n"
    . 'From: ' . $from . "\r\n"
    . 'To: ' . $to . "\r\n"
    . 'Content-Type: text/plain; charset=UTF-8' . "\r\n\r\n"
    . $body
    . "\r\n.";

fwrite($fp, $msg . "\r\n");
echo "C: [message body sent]\n";
$read($fp);
$send($fp, 'QUIT');
$read($fp);
fclose($fp);
