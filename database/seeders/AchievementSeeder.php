<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'name' => 'First Step',
                'description' => 'Completed your first study task.',
                'icon_path' => 'icons/first-step.png',
            ],
            [
                'name' => 'Deep Focus',
                'description' => 'Completed a 4-hour pomodoro streak.',
                'icon_path' => 'icons/focus.png',
            ],
            [
                'name' => 'Category King',
                'description' => 'Created over 10 different task categories.',
                'icon_path' => 'icons/king.png',
            ],
        ];

        foreach ($achievements as $ach) {
            Achievement::create($ach);
        }
    }
}
