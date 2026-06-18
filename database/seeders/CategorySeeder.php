<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $categories = [
            ['name' => 'Work', 'color' => '#38c172', 'icon' => 'briefcase'],
            ['name' => 'Habit', 'color' => '#f66d9b', 'icon' => 'check-circle'],
            ['name' => 'Study', 'color' => '#3490dc', 'icon' => 'academic-cap'],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, ['user_id' => $user->id]));
        }
    }
}
