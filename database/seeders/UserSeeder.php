<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DailyQuote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create a primary test user
        User::create([
            'name' => 'Marco Octavian',
            'email' => 'marco@gmail.com',
            'password' => Hash::make('password'),
            'theme_mode' => 'dark',
            'current_quote_id' => DailyQuote::first()->id ?? null,
        ]);
    }
}
