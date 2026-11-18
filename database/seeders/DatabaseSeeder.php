<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Gmail Farmer Admin',
            'username' => '@admin',
            'email' => 'admin@gmailfarmer.test',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'referral_code' => 'ADMINMAX01',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'username' => '@testuser',
            'email' => 'test@gmailfarmer.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'referral_code' => 'TESTUSER01',
        ]);
    }
}
