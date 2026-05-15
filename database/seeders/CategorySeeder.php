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
            ['name' => 'Study', 'color' => '#3490dc', 'icon' => 'book'],
            ['name' => 'Project', 'color' => '#38c172', 'icon' => 'code'],
            ['name' => 'Personal', 'color' => '#f66d9b', 'icon' => 'user'],
            ['name' => 'Urgent', 'color' => '#e3342f', 'icon' => 'exclamation-triangle'],
        ];

        foreach ($categories as $cat) {
            Category::create(array_merge($cat, ['user_id' => $user->id]));
        }
    }
}
