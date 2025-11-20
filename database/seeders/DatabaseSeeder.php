<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\ChatbotSeeder;
use Database\Seeders\AdminUserSeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            ChatbotSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
