<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE results MODIFY COLUMN `type` ENUM('hot_lead', 'deal_closed', 'commission', 'revenue_action') NOT NULL DEFAULT 'hot_lead'");
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE results MODIFY COLUMN `type` ENUM('hot_lead', 'deal_closed', 'commission') NOT NULL DEFAULT 'hot_lead'");
        }
    }
};
