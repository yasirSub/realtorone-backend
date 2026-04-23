<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$testEmail = $argv[1] ?? getenv('TEST_EMAIL') ?? '';

if ($testEmail === '') {
    echo "Usage: php test_mail_brevo.php yasir.subhani123@gmail.com\n";
    echo "Or set TEST_EMAIL in environment.\n";
    exit(1);
}

echo "Attempting to send test email to: $testEmail...\n";

try {
    $sentAt = date('Y-m-d H:i:s');
    $subject = 'SMTP Connection Test - ' . $sentAt;
    $body = "This is a test email from RealtorOne to verify Brevo SMTP settings.\n\nSent at: {$sentAt}\nFrom: " . config('mail.from.address');

    Mail::raw($body, function ($message) use ($testEmail, $subject) {
        $message->to($testEmail)
                ->subject($subject);
    });
    echo "SUCCESS: Test email sent! Please check your inbox.\n";
} catch (\Exception $e) {
    echo "ERROR: Failed to send email.\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Mailer: " . config('mail.default') . "\n";
    echo "Host: " . config('mail.mailers.smtp.host') . "\n";
    echo "Port: " . config('mail.mailers.smtp.port') . "\n";
}
