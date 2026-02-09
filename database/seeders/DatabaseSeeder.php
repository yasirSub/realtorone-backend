<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->delete();

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@realtorone.com',
            'password' => 'password123',
        ]);

        User::factory()->create([
            'name' => 'Realtor One',
            'email' => 'realtorone@example.com',
            'password' => 'password123',
        ]);
    }
}
