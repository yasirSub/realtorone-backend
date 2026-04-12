<?php
$path = '/var/www/html/realtor-one-firebase-adminsdk-fbsvc-4bdaeea92a.json';
$content = file_get_contents($path);
if (str_starts_with($content, "\xEF\xBB\xBF")) {
    $content = substr($content, 3);
}
$keyFile = json_decode($content, true);
$pk = $keyFile['private_key'];

// Normalize
$pk = trim($pk);
$pk = str_replace(["\r\n", "\r"], "\n", $pk);
$pk = str_replace('\\n', "\n", $pk);

echo "Key length: " . strlen($pk) . "\n";
echo "Prefix: " . substr($pk, 0, 30) . "\n";
echo "Suffix: " . substr($pk, -30) . "\n";

$res = openssl_pkey_get_private($pk);
if ($res === false) {
    echo "OpenSSL ERROR: " . openssl_error_string() . "\n";
} else {
    echo "OpenSSL VALID!\n";
    $details = openssl_pkey_get_details($res);
    echo "Type: " . $details['type'] . "\n";
    echo "Bits: " . $details['bits'] . "\n";
}
