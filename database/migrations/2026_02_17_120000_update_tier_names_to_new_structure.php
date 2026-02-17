<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the courses table enum constraint to allow new tier names
        // For SQLite, we need to recreate the table
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER TABLE for ENUM, so we need to recreate
            DB::statement("PRAGMA foreign_keys=off");
            DB::statement("BEGIN TRANSACTION");
            
            // Create new table with updated enum
            DB::statement("CREATE TABLE courses_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                url VARCHAR(255),
                min_tier VARCHAR(255) NOT NULL DEFAULT 'Consultant',
                created_at TIMESTAMP,
                updated_at TIMESTAMP
            )");
            
            // Copy data with tier name conversion
            DB::statement("INSERT INTO courses_new (id, title, description, url, min_tier, created_at, updated_at)
                SELECT 
                    id, 
                    title, 
                    description, 
                    url,
                    CASE 
                        WHEN min_tier = 'Free' THEN 'Consultant'
                        WHEN min_tier = 'Silver' THEN 'Rainmaker'
                        WHEN min_tier = 'Gold' THEN 'Titan'
                        WHEN min_tier = 'Diamond' THEN 'Titan'
                        ELSE 'Consultant'
                    END as min_tier,
                    created_at,
                    updated_at
                FROM courses");
            
            // Drop old table and rename new one
            DB::statement("DROP TABLE courses");
            DB::statement("ALTER TABLE courses_new RENAME TO courses");
            
            DB::statement("COMMIT");
            DB::statement("PRAGMA foreign_keys=on");
        } else {
            // For MySQL/PostgreSQL, alter the enum
            DB::statement("ALTER TABLE courses MODIFY COLUMN min_tier ENUM('Consultant', 'Rainmaker', 'Titan') DEFAULT 'Consultant'");
        }

        // Update users table - membership_tier
        DB::table('users')
            ->where('membership_tier', 'Free')
            ->update(['membership_tier' => 'Consultant']);
        
        DB::table('users')
            ->where('membership_tier', 'Silver')
            ->update(['membership_tier' => 'Rainmaker']);
        
        DB::table('users')
            ->where('membership_tier', 'Gold')
            ->update(['membership_tier' => 'Titan']);
        
        DB::table('users')
            ->where('membership_tier', 'Diamond')
            ->update(['membership_tier' => 'Titan']);
        
        // Also update any existing "Titan - GOLD" records to just "Titan"
        DB::table('users')
            ->where('membership_tier', 'Titan - GOLD')
            ->orWhere('membership_tier', 'Titan-GOLD')
            ->update(['membership_tier' => 'Titan']);

        // Update subscription_packages table - name and prices
        DB::table('subscription_packages')
            ->where('name', 'Free')
            ->update([
                'name' => 'Consultant',
                'price_monthly' => 0.00
            ]);
        
        DB::table('subscription_packages')
            ->where('name', 'Silver')
            ->update([
                'name' => 'Rainmaker',
                'price_monthly' => 210.00
            ]);
        
        DB::table('subscription_packages')
            ->where('name', 'Gold')
            ->update([
                'name' => 'Titan',
                'price_monthly' => 420.00
            ]);
        
        // Also update any existing "Titan - GOLD" records to just "Titan"
        DB::table('subscription_packages')
            ->where('name', 'Titan - GOLD')
            ->orWhere('name', 'Titan-GOLD')
            ->update([
                'name' => 'Titan'
            ]);
        
        // Delete Diamond package (merged into Titan)
        // First, migrate any users subscribed to Diamond to Titan
        $diamondPackage = DB::table('subscription_packages')->where('name', 'Diamond')->first();
        if ($diamondPackage) {
            $titanGoldPackage = DB::table('subscription_packages')->where('name', 'Titan')->first();
            if ($titanGoldPackage) {
                DB::table('user_subscriptions')
                    ->where('package_id', $diamondPackage->id)
                    ->update(['package_id' => $titanGoldPackage->id]);
            }
            // Delete Diamond package
            DB::table('subscription_packages')->where('name', 'Diamond')->delete();
        }

        // Courses table already updated above (for SQLite) or needs update for MySQL
        if (DB::getDriverName() !== 'sqlite') {
            DB::table('courses')
                ->where('min_tier', 'Free')
                ->update(['min_tier' => 'Consultant']);
            
            DB::table('courses')
                ->where('min_tier', 'Silver')
                ->update(['min_tier' => 'Rainmaker']);
            
            DB::table('courses')
                ->where('min_tier', 'Gold')
                ->update(['min_tier' => 'Titan']);
            
            DB::table('courses')
                ->where('min_tier', 'Diamond')
                ->update(['min_tier' => 'Titan']);
        }

        // Update activity_types table - min_tier
        DB::table('activity_types')
            ->where('min_tier', 'Free')
            ->update(['min_tier' => 'Consultant']);
        
        DB::table('activity_types')
            ->where('min_tier', 'Silver')
            ->update(['min_tier' => 'Rainmaker']);
        
        DB::table('activity_types')
            ->where('min_tier', 'Gold')
            ->update(['min_tier' => 'Titan']);
        
        DB::table('activity_types')
            ->where('min_tier', 'Diamond')
            ->update(['min_tier' => 'Titan']);

        // Update activities table - min_tier
        DB::table('activities')
            ->where('min_tier', 'Free')
            ->update(['min_tier' => 'Consultant']);
        
        DB::table('activities')
            ->where('min_tier', 'Silver')
            ->update(['min_tier' => 'Rainmaker']);
        
        DB::table('activities')
            ->where('min_tier', 'Gold')
            ->update(['min_tier' => 'Titan']);
        
        DB::table('activities')
            ->where('min_tier', 'Diamond')
            ->update(['min_tier' => 'Titan']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert activities table
        DB::table('activities')
            ->where('min_tier', 'Titan')
            ->update(['min_tier' => 'Gold']);
        
        DB::table('activities')
            ->where('min_tier', 'Rainmaker')
            ->update(['min_tier' => 'Silver']);
        
        DB::table('activities')
            ->where('min_tier', 'Consultant')
            ->update(['min_tier' => 'Free']);

        // Revert activity_types table
        DB::table('activity_types')
            ->where('min_tier', 'Titan')
            ->update(['min_tier' => 'Gold']);
        
        DB::table('activity_types')
            ->where('min_tier', 'Rainmaker')
            ->update(['min_tier' => 'Silver']);
        
        DB::table('activity_types')
            ->where('min_tier', 'Consultant')
            ->update(['min_tier' => 'Free']);

        // Revert courses table
        DB::table('courses')
            ->where('min_tier', 'Titan')
            ->update(['min_tier' => 'Gold']);
        
        DB::table('courses')
            ->where('min_tier', 'Rainmaker')
            ->update(['min_tier' => 'Silver']);
        
        DB::table('courses')
            ->where('min_tier', 'Consultant')
            ->update(['min_tier' => 'Free']);

        // Revert users table
        DB::table('users')
            ->where('membership_tier', 'Titan')
            ->update(['membership_tier' => 'Gold']);
        
        DB::table('users')
            ->where('membership_tier', 'Rainmaker')
            ->update(['membership_tier' => 'Silver']);
        
        DB::table('users')
            ->where('membership_tier', 'Consultant')
            ->update(['membership_tier' => 'Free']);

        // Revert subscription_packages table
        DB::table('subscription_packages')
            ->where('name', 'Titan')
            ->update([
                'name' => 'Gold',
                'price_monthly' => 99.00
            ]);
        
        DB::table('subscription_packages')
            ->where('name', 'Rainmaker')
            ->update([
                'name' => 'Silver',
                'price_monthly' => 49.00
            ]);
        
        DB::table('subscription_packages')
            ->where('name', 'Consultant')
            ->update([
                'name' => 'Free',
                'price_monthly' => 0.00
            ]);
    }
};
