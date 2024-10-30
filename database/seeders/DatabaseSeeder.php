<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        DB::table('users')->insert([
            'full_name' => 'admin',
            'email' => 'admin@gmail.com',
            'contact_number' => '0123456789',
            'dob' => '1990-01-01',
            'doj' => '2021-01-01',
            'role' => 'Administrator',
            'level' => '5%',
            'password' => Hash::make('admin@gmail.com'),
        ]);

        DB::table('packages')->insert([
            [
                'title' => 'K 100',
                'amount' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'K 500',
                'amount' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'K 1000',
                'amount' => 1000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('payment_periods')->insert([
            [
                'title' => 'FN (25%)',
                'percent' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'FN (50%)',
                'percent' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'FN (75%)',
                'percent' => 75,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
