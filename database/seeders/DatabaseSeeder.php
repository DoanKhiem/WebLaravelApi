<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);

        DB::table('packages')->insert([
            ['title' => 'K 100', 'amount' => 100],
            ['title' => 'K 500', 'amount' => 500],
            ['title' => 'K 1000', 'amount' => 1000],
        ]);

        DB::table('payment_periods')->insert([
            ['title' => '1 FN (25%)', 'amount' => 25],
            ['title' => '2 FN (50%)', 'amount' => 50],
            ['title' => '3 FN (75%)', 'amount' => 75],
        ]);
    }
}
