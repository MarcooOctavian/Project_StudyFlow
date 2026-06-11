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
        $firstQuoteId = DailyQuote::first()->id ?? null;

        // Create primary test users
        User::create([
            'name' => 'Marco Octavian',
            'email' => 'marco@gmail.com',
            'password' => Hash::make('password'),
            'theme_mode' => 'dark',
            'current_quote_id' => $firstQuoteId,
        ]);

        User::create([
            'name' => 'Jonathan Jojo',
            'email' => 'jojo@gmail.com',
            'password' => Hash::make('password'),
            'theme_mode' => 'light',
            'current_quote_id' => $firstQuoteId,
        ]);

        User::create([
            'name' => 'Gearald Hunter',
            'email' => 'gearald@gmail.com',
            'password' => Hash::make('password'),
            'theme_mode' => 'dark',
            'current_quote_id' => $firstQuoteId,
        ]);

        // Create 5 random testing users via Factory
        User::factory()->count(5)->create([
            'password' => Hash::make('password'),
            'current_quote_id' => $firstQuoteId,
        ]);
    }
}
