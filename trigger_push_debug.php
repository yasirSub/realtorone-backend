<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Jobs\SendPushBroadcastJob;
use App\Models\NotificationBroadcast;

$broadcastId = 19; // Change this to a valid broadcast ID
echo "Handling broadcast $broadcastId via Job...\n";

$broadcast = NotificationBroadcast::find($broadcastId);
if (!$broadcast) {
    echo "Broadcast not found.\n";
    exit(1);
}

// Reset status so it runs
$broadcast->status = 'scheduled';
$broadcast->save();

// Now instantiate the job and call handle
try {
    $job = new SendPushBroadcastJob($broadcastId);
    // Laravel will inject dependencies into handle
    app()->call([$job, 'handle']);
    
    $broadcast->refresh();
    echo "Done. Status: " . $broadcast->status . "\n";
    echo "Last Error: " . $broadcast->last_error . "\n";
    echo "Last Sent Count: " . $broadcast->last_sent_count . "\n";
    echo "Last Run At: " . $broadcast->last_run_at . "\n";
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
