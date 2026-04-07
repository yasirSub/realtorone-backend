<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\DealRoomExcelImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncExcelLeads extends Command
{
    protected $signature = 'leads:sync-excel';

    protected $description = 'Sync leads from the master Excel sheet in the research folder (Native PHP implementation)';

    public function handle(): int
    {
        $filePath = 'f:\\xcode\\office wrok\\realtorone\\realtorone-research\\Deal Room Data.xlsx';

        if (! file_exists($filePath)) {
            $this->error("Excel file not found at: $filePath");

            return self::FAILURE;
        }

        $this->info("Parsing Excel: $filePath");
        $rows = DealRoomExcelImport::parseXlsx($filePath);

        if (empty($rows)) {
            $this->warn('The Excel file is empty or missing data rows.');

            return self::SUCCESS;
        }

        Log::info('Excel Sync Header: '.json_encode($rows[0] ?? []));
        Log::info('Excel Sync Total Rows: '.max(0, count($rows) - 1));

        $userId = User::first()->id ?? 1;
        $stats = DealRoomExcelImport::importFromRows($userId, $rows);

        $this->info("Sync completed! Created: {$stats['created']}, Updated: {$stats['updated']}, Skipped: {$stats['skipped']}.");

        return self::SUCCESS;
    }
}
