<?php

namespace Database\Seeders;

use App\Models\DailyQuote;
use Illuminate\Database\Seeder;

class DailyQuoteSeeder extends Seeder
{
    public function run(): void
    {
        $quotes = [
            [
                'quote_text' => 'The only way to do great work is to love what you do.',
                'author' => 'Steve Jobs',
            ],
            [
                'quote_text' => 'Success is not final, failure is not fatal: it is the courage to continue that counts.',
                'author' => 'Winston Churchill',
            ],
            [
                'quote_text' => 'It always seems impossible until it\'s done.',
                'author' => 'Nelson Mandela',
            ],
            [
                'quote_text' => 'Don\'t let yesterday take up too much of today.',
                'author' => 'Will Rogers',
            ],
            [
                'quote_text' => 'Focus on being productive instead of busy.',
                'author' => 'Tim Ferriss',
            ],
        ];

        foreach ($quotes as $quote) {
            DailyQuote::create($quote);
        }
    }
}
