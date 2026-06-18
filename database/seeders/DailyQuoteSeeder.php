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
            [
                'quote_text' => 'Your mind is for having ideas, not holding them.',
                'author' => 'David Allen',
            ],
            [
                'quote_text' => 'Deep work is the superpower of the 21st century.',
                'author' => 'Cal Newport',
            ],
            [
                'quote_text' => 'You do not rise to the level of your goals. You fall to the level of your systems.',
                'author' => 'James Clear',
            ],
            [
                'quote_text' => 'Make each day your masterpiece.',
                'author' => 'John Wooden',
            ],
            [
                'quote_text' => 'Start where you are. Use what you have. Do what you can.',
                'author' => 'Arthur Ashe',
            ],
            [
                'quote_text' => 'Small daily improvements over time lead to stunning results.',
                'author' => 'Robin Sharma',
            ],
            [
                'quote_text' => 'Action is the foundational key to all success.',
                'author' => 'Pablo Picasso',
            ],
            [
                'quote_text' => 'Do the best you can until you know better. Then when you know better, do better.',
                'author' => 'Maya Angelou',
            ],
            [
                'quote_text' => 'Energy and persistence conquer all things.',
                'author' => 'Benjamin Franklin',
            ],
            [
                'quote_text' => 'Believe you can and you\'re halfway there.',
                'author' => 'Theodore Roosevelt',
            ],
            [
                'quote_text' => 'It is during our darkest moments that we must focus to see the light.',
                'author' => 'Aristotle',
            ],
            [
                'quote_text' => 'The best way to predict the future is to create it.',
                'author' => 'Peter Drucker',
            ],
            [
                'quote_text' => 'Quality is not an act, it is a habit.',
                'author' => 'Aristotle',
            ],
        ];

        foreach ($quotes as $quote) {
            DailyQuote::firstOrCreate(
                ['quote_text' => $quote['quote_text']],
                ['author' => $quote['author']]
            );
        }
    }
}
