<?php

namespace App\Http\Controllers;

use App\Models\DailyQuote;
use App\Models\Category;
use App\Models\Task;
use App\Models\Note;
use App\Models\Quest;
use App\Models\UserQuestProgress;
use App\Models\PomodoroSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Fetch Daily Quote
        $quote = $user->dailyQuote ?? DailyQuote::where('is_active', true)->first();

        // 2. Fetch Categories
        $categories = $user->categories()->get();

        // 3. Fetch Tasks (with categories)
        $tasksQuery = $user->tasks()->with('category');

        // Apply filters if any
        if ($request->has('category_id') && $request->category_id != '') {
            $tasksQuery->where('category_id', $request->category_id);
        }

        if ($request->has('status') && $request->status != '') {
            $tasksQuery->where('status', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
            $tasksQuery->where('title', 'like', '%' . $request->search . '%');
        }

        // Apply Sorting Strategy (due_date vs priority)
        $sort = $request->get('sort', 'due_date');
        if ($sort === 'priority') {
            // Sort by priority level (high, medium, low)
            // We can order by a raw case when or similar, or just basic order
            $tasksQuery->orderByRaw("CASE 
                WHEN priority_level = 'high' THEN 1 
                WHEN priority_level = 'medium' THEN 2 
                ELSE 3 
            END ASC");
        } else {
            $tasksQuery->orderBy('due_date', 'asc');
        }

        $tasks = $tasksQuery->get();

        // 4. Progress Counter Calculations
        $totalTasksCount = $tasks->count();
        $completedTasksCount = $tasks->where('status', 'done')->count();

        // 5. Fetch Notes
        $notes = $user->notes()->get();

        // 6. Fetch Quests with User Progress
        // Eagerly insert progress if not existing (Factory/Observer-like helper)
        $allQuests = Quest::all();
        foreach ($allQuests as $quest) {
            UserQuestProgress::firstOrCreate([
                'user_id' => $user->id,
                'quest_id' => $quest->id,
            ], [
                'current_value' => 0,
                'completed' => false,
            ]);
        }

        $quests = Quest::join('user_quest_progress', 'quests.id', '=', 'user_quest_progress.quest_id')
            ->where('user_quest_progress.user_id', $user->id)
            ->select('quests.*', 'user_quest_progress.current_value', 'user_quest_progress.completed', 'user_quest_progress.id as progress_id')
            ->get();

        // 7. Mini Calendar Tasks
        // Find tasks due this month to mark on calendar
        $calendarTasks = $user->tasks()
            ->whereNotNull('due_date')
            ->whereMonth('due_date', now()->month)
            ->whereYear('due_date', now()->year)
            ->get()
            ->groupBy(function($date) {
                return $date->due_date->format('j'); // group by day of month
            });

        return view('dashboard', compact(
            'user',
            'quote',
            'categories',
            'tasks',
            'notes',
            'quests',
            'totalTasksCount',
            'completedTasksCount',
            'calendarTasks'
        ));
    }

    public function toggleTask(Request $request, $id)
    {
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);
        $oldStatus = $task->status;
        $newStatus = $request->input('status');

        if (!in_array($newStatus, ['todo', 'in_progress', 'done'])) {
            return response()->json(['error' => 'Invalid status'], 400);
        }

        $task->status = $newStatus;

        if ($newStatus === 'done' && $oldStatus !== 'done') {
            $task->completed_at = now();
            $user->total_points += 25;
            $user->save();
        } else if ($oldStatus === 'done' && $newStatus !== 'done') {
            $task->completed_at = null;
            $user->total_points = max(0, $user->total_points - 25);
            $user->save();
        }

        $task->save();

        // Update quests related to task completion
        $this->updateQuestProgress($user, 'task_completion');

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority_level,
                'category_id' => $task->category_id,
                'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
            ],
            'points' => $user->total_points,
            'quests' => $this->getQuests($user)
        ]);
    }

    public function createTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'priority_level' => 'required|in:low,medium,high',
            'category_id' => 'nullable|exists:categories,id',
            'due_date' => 'nullable|date',
        ]);

        $user = Auth::user();
        $task = $user->tasks()->create([
            'title' => $request->title,
            'priority_level' => $request->priority_level,
            'category_id' => $request->category_id,
            'status' => 'todo',
            'due_date' => $request->due_date,
        ]);

        // Earning 10 XP just for creating a task to motivate users!
        $user->total_points += 10;
        $user->save();

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority_level,
                'category_id' => $task->category_id,
                'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
            ],
            'points' => $user->total_points,
            'tasks' => $user->tasks()->get()->map(function($t) {
                return [
                    'id' => $t->id,
                    'title' => $t->title,
                    'description' => $t->description,
                    'status' => $t->status,
                    'priority' => $t->priority_level,
                    'category_id' => $t->category_id,
                    'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : null,
                ];
            })
        ]);
    }

    public function saveNote(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        $user = Auth::user();

        if ($request->filled('id')) {
            $note = $user->notes()->findOrFail($request->id);
            $note->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        } else {
            $note = $user->notes()->create([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }

        return response()->json([
            'success' => true,
            'note' => $note,
            'notes' => $user->notes()->get()
        ]);
    }

    public function deleteNote($id)
    {
        $user = Auth::user();
        $note = $user->notes()->findOrFail($id);
        $note->delete();

        return response()->json([
            'success' => true,
            'notes' => $user->notes()->get()
        ]);
    }

    public function deleteTask($id)
    {
        $user = Auth::user();
        $task = $user->tasks()->findOrFail($id);
        $task->delete();

        $this->updateQuestProgress($user, 'task_completion');

        return response()->json([
            'success' => true,
            'points' => $user->total_points,
            'tasks' => $user->tasks()->get()->map(function($t) {
                return [
                    'id' => $t->id,
                    'title' => $t->title,
                    'description' => $t->description,
                    'status' => $t->status,
                    'priority' => $t->priority_level,
                    'category_id' => $t->category_id,
                    'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : null,
                ];
            }),
            'quests' => $this->getQuests($user)
        ]);
    }

    public function completePomodoro(Request $request)
    {
        $user = Auth::user();

        // Create pomodoro session
        $session = $user->pomodoroSessions()->create([
            'start_time' => now()->subMinutes(25),
            'end_time' => now(),
            'duration_minutes' => 25,
        ]);

        // Award +50 XP
        $user->total_points += 50;
        $user->save();

        // Update Quest Progress
        $this->updateQuestProgress($user, 'pomodoro');

        return response()->json([
            'success' => true,
            'points' => $user->total_points,
            'quests' => $this->getQuests($user)
        ]);
    }

    private function updateQuestProgress($user, $actionType)
    {
        if ($actionType === 'task_completion') {
            $completedToday = $user->tasks()->where('status', 'done')->whereDate('completed_at', today())->count();
            $completedThisWeek = $user->tasks()->where('status', 'done')->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

            $earlyBird = Quest::where('title', 'The Early Bird')->first();
            if ($earlyBird) {
                $prog = UserQuestProgress::where('user_id', $user->id)->where('quest_id', $earlyBird->id)->first();
                if ($prog) {
                    $prog->current_value = min($earlyBird->goal_value, $completedToday);
                    if ($prog->current_value >= $earlyBird->goal_value && !$prog->completed) {
                        $prog->completed = true;
                        $user->total_points += $earlyBird->points_reward;
                        $user->save();
                    }
                    $prog->save();
                }
            }

            $weeklyScholar = Quest::where('title', 'Weekly Scholar')->first();
            if ($weeklyScholar) {
                $prog = UserQuestProgress::where('user_id', $user->id)->where('quest_id', $weeklyScholar->id)->first();
                if ($prog) {
                    $prog->current_value = min($weeklyScholar->goal_value, $completedThisWeek);
                    if ($prog->current_value >= $weeklyScholar->goal_value && !$prog->completed) {
                        $prog->completed = true;
                        $user->total_points += $weeklyScholar->points_reward;
                        $user->save();
                    }
                    $prog->save();
                }
            }
        } else if ($actionType === 'pomodoro') {
            $completedPomodoros = $user->pomodoroSessions()->whereNotNull('end_time')->count();
            $pomodoroMaster = Quest::where('title', 'Pomodoro Master')->first();
            if ($pomodoroMaster) {
                $prog = UserQuestProgress::where('user_id', $user->id)->where('quest_id', $pomodoroMaster->id)->first();
                if ($prog) {
                    $prog->current_value = min($pomodoroMaster->goal_value, $completedPomodoros);
                    if ($prog->current_value >= $pomodoroMaster->goal_value && !$prog->completed) {
                        $prog->completed = true;
                        $user->total_points += $pomodoroMaster->points_reward;
                        $user->save();
                    }
                    $prog->save();
                }
            }
        }
    }

    private function getQuests($user)
    {
        return Quest::join('user_quest_progress', 'quests.id', '=', 'user_quest_progress.quest_id')
            ->where('user_quest_progress.user_id', $user->id)
            ->select('quests.*', 'user_quest_progress.current_value', 'user_quest_progress.completed', 'user_quest_progress.id as progress_id')
            ->get();
    }
}
