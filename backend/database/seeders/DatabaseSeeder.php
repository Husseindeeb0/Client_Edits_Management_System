<?php

namespace Database\Seeders;

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
        $this->call([
            AdminUserSeeder::class,
        ]);

        // Sample data for trying the app; never put it into a real database
        if (! app()->isProduction()) {
            $this->call([
                ClientSeeder::class,
                CompanySeeder::class,
                ProjectSeeder::class,
                EditSeeder::class,
                AttachmentSeeder::class,
            ]);
        }
    }
}
