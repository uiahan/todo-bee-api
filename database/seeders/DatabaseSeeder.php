<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => env('ADMIN_NAME'),
            'email' => env('ADMIN_EMAIL'),
            'password' => bcrypt(env('ADMIN_PASSWORD')),
            'role' => env('ADMIN_ROLE')
        ]);

        User::create([
            'name' => env('CLIENT_NAME'),
            'email' => env('CLIENT_EMAIL'),
            'password' => bcrypt(env('CLIENT_PASSWORD')),
            'role' => env('CLIENT_ROLE')
        ]);
    }
}
