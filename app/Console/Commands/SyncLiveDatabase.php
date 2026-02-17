<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Activity;

class SyncLiveDatabase extends Command
{
    protected $signature = 'db:sync-live {--table= : Specific table to sync}';
    protected $description = 'Sync data from Live (PostgreSQL) to Local (MySQL)';

    public function handle()
    {
        $this->info('Starting database sync from Live...');

        if (!config('database.connections.live.host')) {
            $this->error('Live database credentials missing in .env');
            return 1;
        }

        $tables = ['users', 'activities', 'performance_metrics', 'learning_content'];
        $table = $this->option('table');
        
        if ($table) {
            $tables = [$table];
        }

        foreach ($tables as $tableName) {
            $this->syncTable($tableName);
        }

        $this->info('Sync completed!');
    }

    protected function syncTable($tableName)
    {
        $this->comment("Syncing table: {$tableName}...");

        try {
            $liveData = DB::connection('live')->table($tableName)->get();
            
            if ($liveData->isEmpty()) {
                $this->warn("No data found in live table: {$tableName}");
                return;
            }

            foreach ($liveData as $row) {
                $data = (array) $row;
                
                // Remove or map fields if necessary
                // MySQL vs PostgreSQL might have different boolean/timestamp formats
                
                DB::table($tableName)->updateOrInsert(
                    ['id' => $data['id']],
                    $data
                );
            }
            
            $this->info("Successfully synced " . count($liveData) . " rows for {$tableName}");
        } catch (\Exception $e) {
            $this->error("Failed to sync {$tableName}: " . $e->getMessage());
        }
    }
}
