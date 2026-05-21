<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $studyCategory = Category::where('name', 'Study')->first();
        $projectCategory = Category::where('name', 'Project')->first();

        if (!$user) return;

        $tasks = [
            [
                'user_id' => $user->id,
                'category_id' => $studyCategory->id ?? null,
                'title' => 'Read Chapter 5 of OS Book',
                'description' => 'Focus on memory management and paging.',
                'priority_level' => 'high',
                'status' => 'todo',
                'due_date' => Carbon::now()->addDays(2),
            ],
            [
                'user_id' => $user->id,
                'category_id' => $projectCategory->id ?? null,
                'title' => 'Implement Database Seeders',
                'description' => 'Create dummy data for the StudyFlow app.',
                'priority_level' => 'medium',
                'status' => 'in_progress',
                'due_date' => Carbon::now()->addDay(),
            ],
            [
                'user_id' => $user->id,
                'category_id' => null,
                'title' => 'Buy Coffee Beans',
                'description' => 'Need more fuel for coding sessions.',
                'priority_level' => 'low',
                'status' => 'todo',
                'due_date' => Carbon::now(),
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
