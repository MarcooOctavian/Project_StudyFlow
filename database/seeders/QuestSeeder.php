<?php

namespace Database\Seeders;

use App\Models\Quest;
use App\Models\Achievement;
use Illuminate\Database\Seeder;

class QuestSeeder extends Seeder
{
    public function run(): void
    {
        $quests = [
            [
                'title' => 'The Early Bird',
                'type' => 'daily',
                'goal_value' => 3,
                'points_reward' => 50,
            ],
            [
                'title' => 'Weekly Scholar',
                'type' => 'weekly',
                'goal_value' => 20,
                'points_reward' => 200,
            ],
            [
                'title' => 'Pomodoro Master',
                'type' => 'milestone',
                'goal_value' => 50,
                'points_reward' => 500,
            ],
        ];

        foreach ($quests as $quest) {
            Quest::create($quest);
        }
    }
}
