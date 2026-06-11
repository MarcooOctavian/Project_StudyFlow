<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ 
    darkTheme: true,
    lampOn: true,
    mugSteam: true,
    activeCategory: '',
    activeStatus: 'todo',
    searchQuery: '',
    showNoteModal: false,
    selectedNote: { id: null, title: '', content: '' },
    isTimerRunning: false,
    timerDuration: {{ ($user->pomodoro_duration ?? 25) * 60 }},
    timerMinutes: {{ $user->pomodoro_duration ?? 25 }},
    timerSeconds: 0,
    customPomodoroDuration: {{ $user->pomodoro_duration ?? 25 }},
    customBreakDuration: {{ $user->break_duration ?? 5 }},
    isBreakMode: false,
    timerInterval: null,
    timerLoops: 3,
    currentLoop: 1,
    timerGlow: false,
    timerStatusText: '',
    audioPlaying: false,
    audioSource: 'https://stream.zeno.fm/0r0xa792kwzuv', // Lofi radio
    audioVolume: 0.5,
    activeTrack: 'lofi',
    points: {{ $user->total_points ?? 0 }},
    tasks: {{ json_encode($tasks->map(function($t) {
        return [
            'id' => $t->id,
            'title' => $t->title,
            'description' => $t->description,
            'status' => $t->status,
            'priority' => $t->priority_level,
            'category_id' => $t->category_id,
            'due_date' => $t->due_date ? $t->due_date->format('Y-m-d') : null,
        ];
    })) }},
    notes: {{ json_encode($notes->map(function($n) {
        return [
            'id' => $n->id,
            'title' => $n->title,
            'content' => $n->content,
        ];
    })) }},
    quests: {{ json_encode($quests->map(function($q) {
        return [
            'id' => $q->id,
            'title' => $q->title,
            'type' => $q->type,
            'goal_value' => $q->goal_value,
            'points_reward' => $q->points_reward,
            'current_value' => $q->current_value,
            'completed' => (bool)$q->completed,
        ];
    })) }},
    showAddTaskModal: false,
    newTask: { title: '', priority: 'low', category_id: '', due_date: '' },
    draggedNoteIndex: null,
    reorderNotes(from, to) {
        if (from === null || to === null || from === to) return;
        const updatedNotes = [...this.notes];
        const [movedNote] = updatedNotes.splice(from, 1);
        updatedNotes.splice(to, 0, movedNote);
        this.notes = updatedNotes;
    },
    calendarYear: new Date().getFullYear(),
    calendarMonth: new Date().getMonth(),
    calendarMonthName() {
        const names = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        return names[this.calendarMonth] + ' ' + this.calendarYear;
    },
    getDaysInMonth() {
        const year = this.calendarYear;
        const month = this.calendarMonth;
        const firstDayOfWeek = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        let daysList = [];
        
        for (let i = 0; i < firstDayOfWeek; i++) {
            daysList.push({ day: null, dateString: null, hasDue: false, isToday: false });
        }
        
        const today = new Date();
        const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        
        for (let day = 1; day <= daysInMonth; day++) {
            const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const hasDue = this.tasks.some(t => t.due_date === dateString);
            const isToday = dateString === todayStr;
            daysList.push({ day, dateString, hasDue, isToday });
        }
        
        return daysList;
    },
    prevMonth() {
        if (this.calendarMonth === 0) {
            this.calendarMonth = 11;
            this.calendarYear--;
        } else {
            this.calendarMonth--;
        }
    },
    nextMonth() {
        if (this.calendarMonth === 11) {
            this.calendarMonth = 0;
            this.calendarYear++;
        } else {
            this.calendarMonth++;
        }
    },
    showCalendarDayModal: false,
    selectedCalendarDate: '',
    selectedCalendarDay: 0,
    newCalendarTask: { title: '', priority: 'low', category_id: '' },
    showNotesWindow: false,
    notesPosition: { x: (window.innerWidth ? window.innerWidth - 380 : 800), y: 120 },
    isDraggingNotes: false,
    dragStart: { x: 0, y: 0 },

    async deleteTaskDb(id) {
        if (!confirm('Are you sure you want to delete this task?')) return;
        try {
            const res = await fetch(`/dashboard/tasks/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                this.tasks = data.tasks;
                this.points = data.points;
                this.quests = data.quests.map(q => ({
                    id: q.id,
                    title: q.title,
                    type: q.type,
                    goal_value: q.goal_value,
                    points_reward: q.points_reward,
                    current_value: q.current_value,
                    completed: !!q.completed,
                }));
            }
        } catch (err) {
            console.error(err);
        }
    },

    async addCalendarTaskDb() {
        if (!this.newCalendarTask.title) return;
        try {
            const res = await fetch('/dashboard/tasks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    title: this.newCalendarTask.title,
                    priority_level: this.newCalendarTask.priority,
                    category_id: this.newCalendarTask.category_id || null,
                    due_date: this.selectedCalendarDate
                })
            });
            const data = await res.json();
            if (data.success) {
                this.tasks = data.tasks;
                this.points = data.points;
                this.newCalendarTask = { title: '', priority: 'low', category_id: '' };
            }
        } catch (err) {
            console.error(err);
        }
    },

    async toggleTaskStatus(task, newStatus) {
        const oldStatus = task.status;
        task.status = newStatus;
        try {
            const res = await fetch(`/dashboard/tasks/${task.id}/toggle`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: newStatus })
            });
            const data = await res.json();
            if (data.success) {
                this.points = data.points;
                this.quests = data.quests.map(q => ({
                    id: q.id,
                    title: q.title,
                    type: q.type,
                    goal_value: q.goal_value,
                    points_reward: q.points_reward,
                    current_value: q.current_value,
                    completed: !!q.completed,
                }));
                task.status = data.task.status;
            } else {
                task.status = oldStatus;
            }
        } catch (err) {
            task.status = oldStatus;
            console.error(err);
        }
    },

    async saveTaskDb() {
        if (!this.newTask.title) return;
        try {
            const res = await fetch('/dashboard/tasks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    title: this.newTask.title,
                    priority_level: this.newTask.priority,
                    category_id: this.newTask.category_id || null,
                    due_date: this.newTask.due_date || null
                })
            });
            const data = await res.json();
            if (data.success) {
                this.tasks = data.tasks;
                this.points = data.points;
                this.newTask = { title: '', priority: 'low', category_id: '', due_date: '' };
                this.showAddTaskModal = false;
            }
        } catch (err) {
            console.error(err);
        }
    },

    async saveNoteDb() {
        if (!this.selectedNote.title) return;
        try {
            const res = await fetch('/dashboard/notes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id: this.selectedNote.id,
                    title: this.selectedNote.title,
                    content: this.selectedNote.content
                })
            });
            const data = await res.json();
            if (data.success) {
                this.notes = data.notes;
                this.showNoteModal = false;
            }
        } catch (err) {
            console.error(err);
        }
    },

    async deleteNoteDb(id) {
        if (!confirm('Are you sure you want to delete this note?')) return;
        try {
            const res = await fetch(`/dashboard/notes/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                this.notes = data.notes;
                this.showNoteModal = false;
            }
        } catch (err) {
            console.error(err);
        }
    },

    async completePomodoroDb() {
        try {
            const res = await fetch('/dashboard/pomodoro/complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                this.points = data.points;
                this.quests = data.quests.map(q => ({
                    id: q.id,
                    title: q.title,
                    type: q.type,
                    goal_value: q.goal_value,
                    points_reward: q.points_reward,
                    current_value: q.current_value,
                    completed: !!q.completed,
                }));
            }
        } catch (err) {
            console.error(err);
        }
    },

    async savePomodoroSettingsDb() {
        try {
            await fetch('/dashboard/pomodoro/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    pomodoro_duration: this.customPomodoroDuration,
                    break_duration: this.customBreakDuration
                })
            });
        } catch (err) {
            console.error(err);
        }
    },
    incrementFocus() {
        this.customPomodoroDuration = Math.min(180, this.customPomodoroDuration + 1);
        if (!this.isTimerRunning && !this.isBreakMode) {
            this.timerMinutes = this.customPomodoroDuration;
            this.timerSeconds = 0;
        }
        this.savePomodoroSettingsDb();
    },
    decrementFocus() {
        this.customPomodoroDuration = Math.max(1, this.customPomodoroDuration - 1);
        if (!this.isTimerRunning && !this.isBreakMode) {
            this.timerMinutes = this.customPomodoroDuration;
            this.timerSeconds = 0;
        }
        this.savePomodoroSettingsDb();
    },
    incrementBreak() {
        this.customBreakDuration = Math.min(60, this.customBreakDuration + 1);
        if (!this.isTimerRunning && this.isBreakMode) {
            this.timerMinutes = this.customBreakDuration;
            this.timerSeconds = 0;
        }
        this.savePomodoroSettingsDb();
    },
    decrementBreak() {
        this.customBreakDuration = Math.max(1, this.customBreakDuration - 1);
        if (!this.isTimerRunning && this.isBreakMode) {
            this.timerMinutes = this.customBreakDuration;
            this.timerSeconds = 0;
        }
        this.savePomodoroSettingsDb();
    },
    incrementLoops() {
        this.timerLoops = Math.min(10, this.timerLoops + 1);
    },
    decrementLoops() {
        this.timerLoops = Math.max(1, this.timerLoops - 1);
    },
    triggerGlow() {
        this.timerGlow = true;
        setTimeout(() => { this.timerGlow = false; }, 3500);
    }
}" :class="darkTheme ? 'dark' : ''">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>StudyFlow - Cozy Dashboard</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Outfit:300,400,500,600,700|Quicksand:400,500,600,700&display=swap" rel="stylesheet">

    <!-- CSS / JS Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Family Styling Override -->
    <style>
        /* Custom scrollbar styling */
        /* Firefox */
        body.dark * {
            scrollbar-width: thin;
            scrollbar-color: rgba(99, 102, 241, 0.35) rgba(25, 18, 42, 0.2);
        }

        body.light * {
            scrollbar-width: thin;
            scrollbar-color: rgba(217, 119, 6, 0.35) rgba(255, 255, 255, 0.2);
        }

        /* Chrome, Edge, and Safari */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(25, 18, 42, 0.25);
            border-radius: 9999px;
        }

        body.light ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.35);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.35);
            border-radius: 9999px;
            transition: background 0.2s ease;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.65);
        }

        body.light ::-webkit-scrollbar-thumb {
            background: rgba(217, 119, 6, 0.35);
        }

        body.light ::-webkit-scrollbar-thumb:hover {
            background: rgba(217, 119, 6, 0.65);
        }

        body {
            font-family: 'Outfit', 'Quicksand', sans-serif;
            transition: background-color 0.8s ease, color 0.8s ease;
        }

        /* Glassmorphism Styles */
        .glass-panel {
            background: rgba(25, 18, 42, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .light .glass-panel {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        /* Steam animation for Coffee Cup */
        @keyframes steam {
            0% { transform: translateY(0) scaleX(1); opacity: 0; }
            15% { opacity: 0.5; }
            50% { transform: translateY(-10px) scaleX(1.2); opacity: 0.3; }
            95% { opacity: 0; }
            100% { transform: translateY(-20px) scaleX(1.5); opacity: 0; }
        }
        .steam-line {
            animation: steam 3s infinite ease-out;
        }
        .steam-line:nth-child(2) { animation-delay: 1s; }
        .steam-line:nth-child(3) { animation-delay: 2s; }

        /* Rain animation in window */
        @keyframes rain {
            0% { transform: translateY(-20px); opacity: 0.8; }
            100% { transform: translateY(120px); opacity: 0.2; }
        }
        .rain-drop {
            animation: rain 0.8s infinite linear;
        }

        /* Ambient neon glows */
        .neon-glow-indigo {
            box-shadow: 0 0 25px rgba(99, 102, 241, 0.35);
        }
        .neon-glow-pink {
            box-shadow: 0 0 25px rgba(236, 72, 153, 0.35);
        }

        /* Circular progress ring */
        .progress-ring__circle {
            transition: stroke-dashoffset 0.35s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col justify-between overflow-x-hidden lg:h-screen lg:overflow-hidden transition-colors duration-500"
    :class="darkTheme ? 'dark' : 'light'"
    :style="darkTheme ? 'background: linear-gradient(135deg, #090514 0%, #150d2a 50%, #05020a 100%)' : 'background: linear-gradient(135deg, #fef4e8 0%, #f7d2bc 50%, #fbd5c6 100%)'"
    @mousemove="if (isDraggingNotes) { notesPosition.x = $event.clientX - dragStart.x; notesPosition.y = $event.clientY - dragStart.y; }"
    @mouseup="isDraggingNotes = false">

    <!-- Immersive Header Panel -->
    <header class="w-full px-6 py-4 flex justify-between items-center z-10">
        <!-- Logo / Brand -->
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300"
                 :class="darkTheme ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' : 'bg-amber-500/20 text-amber-700 border border-amber-500/30'">
                <x-heroicon-o-sparkles class="w-6 h-6 animate-pulse" />
            </div>
            <div>
                <span class="text-xl font-bold tracking-wider uppercase block" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">StudyFlow</span>
                <span class="text-xs tracking-widest block uppercase font-semibold" :class="darkTheme ? 'text-slate-400' : 'text-amber-600/80'">Lo-Fi Study Space</span>
            </div>
        </div>

        <!-- Quote Area -->
        <div class="hidden md:flex flex-col items-center max-w-lg text-center px-4">
            <span class="text-xs uppercase tracking-widest font-bold" :class="darkTheme ? 'text-indigo-400/80' : 'text-amber-700/80'">Daily Motivation</span>
            <p class="text-sm italic font-medium transition-all duration-300" :class="darkTheme ? 'text-slate-300 hover:text-indigo-300' : 'text-slate-700 hover:text-amber-800'">
                "{{ $quote->quote_text ?? 'Make today worth remembering.' }}"
            </p>
            <span class="text-xs font-semibold mt-1" :class="darkTheme ? 'text-indigo-300/60' : 'text-amber-600'">— {{ $quote->author ?? 'StudyFlow' }}</span>
        </div>

        <!-- User Info / Actions -->
        <div class="flex items-center space-x-4">
            <!-- Points Panel -->
            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-lg border text-sm font-semibold transition-all duration-300"
                 :class="darkTheme ? 'bg-indigo-950/40 border-indigo-500/30 text-indigo-200' : 'bg-amber-50 border-amber-200 text-amber-800'">
                <x-heroicon-o-trophy class="w-4 h-4 text-yellow-400" />
                <span><span x-text="points"></span> XP</span>
            </div>

            <!-- Floating Notes Toggle -->
            <button @click="showNotesWindow = !showNotesWindow" 
                    class="relative p-2 rounded-xl border transition-all duration-300 hover:scale-105"
                    :class="showNotesWindow ? (darkTheme ? 'bg-indigo-500/20 border-indigo-400 text-indigo-300' : 'bg-amber-200 border-amber-400 text-amber-950') : (darkTheme ? 'bg-slate-800/60 border-slate-700 text-slate-300 hover:bg-slate-800' : 'bg-white border-amber-200 text-amber-800 hover:bg-amber-50')"
                    title="Study Notes">
                <x-heroicon-o-document-text class="w-5 h-5" />
                <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded-full" x-text="notes.length"></span>
            </button>

            <!-- Dark Theme Toggle -->
            <button @click="darkTheme = !darkTheme" 
                    class="p-2 rounded-xl border transition-all duration-300 hover:scale-105"
                    :class="darkTheme ? 'bg-slate-800/60 border-slate-700 text-yellow-400 hover:bg-slate-800' : 'bg-white border-amber-200 text-indigo-900 hover:bg-amber-50'">
                <template x-if="darkTheme">
                    <x-heroicon-o-sun class="w-5 h-5" />
                </template>
                <template x-if="!darkTheme">
                    <x-heroicon-o-moon class="w-5 h-5" />
                </template>
            </button>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" 
                        class="p-2 rounded-xl border transition-all duration-300 hover:scale-105"
                        :class="darkTheme ? 'bg-red-500/10 border-red-500/30 text-red-400 hover:bg-red-500/20' : 'bg-red-50 border-red-200 text-red-700 hover:bg-red-100'">
                    <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5" />
                </button>
            </form>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="w-full flex-grow px-6 py-2 grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch lg:h-[calc(100vh-140px)] lg:min-h-0">
        
        <!-- Left Side: Tasks & Progress -->
        <section class="lg:col-span-4 flex flex-col space-y-4 lg:h-full lg:min-h-0 overflow-hidden">
            <!-- Progress Panel -->
            <div class="glass-panel rounded-2xl p-5 flex items-center justify-between transition-all duration-300">
                <div class="space-y-1">
                    <h3 class="text-sm font-bold uppercase tracking-wider" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Your Focus Progress</h3>
                    <div class="text-2xl font-bold flex items-baseline space-x-1" :class="darkTheme ? 'text-slate-100' : 'text-slate-800'">
                        <span x-text="tasks.filter(t => t.status === 'done').length"></span>
                        <span class="text-sm font-normal text-slate-400">/</span>
                        <span class="text-sm font-normal text-slate-400" x-text="tasks.length"></span>
                    </div>
                </div>
                <div class="relative w-16 h-16">
                    <svg class="w-full h-full">
                        <circle class="text-slate-700/30" stroke="currentColor" stroke-width="4" fill="transparent" r="28" cx="32" cy="32"/>
                        <circle class="progress-ring__circle"
                                :class="darkTheme ? 'text-indigo-400' : 'text-amber-600'" 
                                stroke="currentColor" 
                                stroke-width="4" 
                                fill="transparent" 
                                r="28" cx="32" cy="32"
                                :stroke-dasharray="2 * Math.PI * 28"
                                :stroke-dashoffset="(2 * Math.PI * 28) - (tasks.length ? (tasks.filter(t => t.status === 'done').length / tasks.length) : 0) * (2 * Math.PI * 28)"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center text-xs font-bold" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">
                        <span x-text="tasks.length ? Math.round((tasks.filter(t => t.status === 'done').length / tasks.length) * 100) : 0"></span>%
                    </div>
                </div>
            </div>

            <!-- Tasks Manager Panel -->
            <div class="glass-panel rounded-2xl p-5 flex-grow flex flex-col space-y-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <h3 class="font-bold tracking-wide text-lg" :class="darkTheme ? 'text-indigo-200' : 'text-amber-900'">Task Flow</h3>
                        <button @click="showAddTaskModal = true"
                                class="p-1 rounded-lg border transition-all duration-300 hover:scale-110"
                                :class="darkTheme ? 'bg-indigo-500/10 border-indigo-500/20 text-indigo-400 hover:bg-indigo-500/20' : 'bg-amber-100 border-amber-200 text-amber-850 hover:bg-amber-200'">
                            <x-heroicon-o-plus class="w-4 h-4" />
                        </button>
                    </div>
                    <!-- Simple Search -->
                    <div class="relative w-40">
                        <input x-model="searchQuery" 
                               type="text" 
                               placeholder="Search..." 
                               class="w-full text-xs pl-8 pr-3 py-1.5 rounded-lg border outline-none transition-all duration-300"
                               :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500'" />
                        <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-slate-400" />
                    </div>
                </div>

                <!-- Category Folders -->
                <div class="flex space-x-2 overflow-x-auto pb-1">
                    <button @click="activeCategory = ''"
                            class="px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-300"
                            :class="activeCategory === '' ? (darkTheme ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40' : 'bg-amber-200 text-amber-900') : (darkTheme ? 'bg-slate-800/40 border border-transparent text-slate-400 hover:text-slate-200' : 'bg-amber-50 border border-transparent text-amber-700/80')">
                        All
                    </button>
                    @foreach($categories as $category)
                    <button @click="activeCategory = '{{ $category->id }}'"
                            class="px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-300 flex items-center space-x-1"
                            :class="activeCategory === '{{ $category->id }}' ? (darkTheme ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40' : 'bg-amber-200 text-amber-900') : (darkTheme ? 'bg-slate-800/40 border border-transparent text-slate-400 hover:text-slate-200' : 'bg-amber-50 border border-transparent text-amber-700/80')">
                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $category->color ?? '#6366f1' }}"></span>
                        <span>{{ $category->name }}</span>
                    </button>
                    @endforeach
                </div>

                <!-- Status Tracker Tabs -->
                <div class="grid grid-cols-3 gap-1 p-1 rounded-xl bg-slate-900/40 border border-slate-700/30">
                    <button @click="activeStatus = 'todo'" 
                            class="py-1.5 text-xs font-semibold rounded-lg transition-all duration-300"
                            :class="activeStatus === 'todo' ? (darkTheme ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/20' : 'bg-amber-100 text-amber-950') : 'text-slate-400 hover:text-slate-200'">
                        To Do
                    </button>
                    <button @click="activeStatus = 'in_progress'"
                            class="py-1.5 text-xs font-semibold rounded-lg transition-all duration-300"
                            :class="activeStatus === 'in_progress' ? (darkTheme ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/20' : 'bg-amber-100 text-amber-950') : 'text-slate-400 hover:text-slate-200'">
                        In Progress
                    </button>
                    <button @click="activeStatus = 'done'"
                            class="py-1.5 text-xs font-semibold rounded-lg transition-all duration-300"
                            :class="activeStatus === 'done' ? (darkTheme ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/20' : 'bg-amber-100 text-amber-950') : 'text-slate-400 hover:text-slate-200'">
                        Done
                    </button>
                </div>

                <!-- Task List Visual -->
                <div class="flex-grow overflow-y-auto space-y-2.5 max-h-[380px] lg:max-h-none pr-1 min-h-0">
                    <template x-for="task in tasks.filter(t => t.status === activeStatus && (activeCategory === '' || t.category_id == activeCategory) && (searchQuery === '' || t.title.toLowerCase().includes(searchQuery.toLowerCase())))" :key="task.id">
                        <div class="p-3 rounded-xl flex items-center justify-between border transition-all duration-300 hover:scale-[1.01]"
                             :class="darkTheme ? 'bg-slate-900/30 border-slate-800/80 hover:bg-slate-900/50' : 'bg-white border-amber-100 hover:bg-amber-50/40'">
                            
                            <div class="flex items-center space-x-3 flex-grow">
                                <!-- Interactive Circular Checkbox -->
                                <button @click="toggleTaskStatus(task, task.status === 'done' ? 'todo' : 'done')" 
                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all duration-300 hover:scale-110"
                                        :class="task.status === 'done' ? (darkTheme ? 'bg-indigo-500 border-indigo-500 text-slate-950' : 'bg-amber-600 border-amber-600 text-white') : (darkTheme ? 'border-slate-600 hover:border-indigo-400' : 'border-amber-300 hover:border-amber-600')">
                                    <template x-if="task.status === 'done'">
                                        <x-heroicon-m-check class="w-3.5 h-3.5 stroke-[3]" />
                                    </template>
                                </button>
                                
                                <div class="space-y-0.5">
                                    <span class="text-sm font-semibold tracking-wide"
                                          :class="task.status === 'done' ? 'line-through text-slate-500' : (darkTheme ? 'text-slate-200' : 'text-slate-800')"
                                          x-text="task.title"></span>
                                    <div class="flex items-center space-x-2 text-[10px]">
                                        <!-- Priority Tagging -->
                                        <span class="px-1.5 py-0.5 rounded-full uppercase tracking-wider font-extrabold"
                                              :class="
                                                task.priority === 'high' ? 'bg-red-500/10 text-red-400' :
                                                task.priority === 'medium' ? 'bg-indigo-500/10 text-indigo-400' : 'bg-emerald-500/10 text-emerald-400'
                                              " x-text="task.priority"></span>
                                        <template x-if="task.due_date">
                                            <span class="text-slate-400 font-semibold" x-text="task.due_date"></span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Move Status Trigger (Status Tracker helper) -->
                            <div class="flex items-center space-x-1">
                                <template x-if="task.status === 'todo'">
                                    <button @click="toggleTaskStatus(task, 'in_progress')" class="p-1 rounded-lg text-slate-400 hover:text-indigo-400 transition-colors">
                                        <x-heroicon-o-play class="w-4 h-4" />
                                    </button>
                                </template>
                                <template x-if="task.status === 'in_progress'">
                                    <div class="flex items-center space-x-1">
                                        <!-- Cancel Button -->
                                        <button @click="toggleTaskStatus(task, 'todo')"
                                                class="p-1 rounded-lg text-slate-400 hover:text-amber-500 dark:hover:text-amber-400 hover:bg-amber-500/10 transition-colors"
                                                title="Cancel In-Progress (Move back to To Do)">
                                            <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                                        </button>
                                        <!-- Complete Button -->
                                        <button @click="toggleTaskStatus(task, 'done')"
                                                class="p-1 rounded-lg text-slate-400 hover:text-emerald-400 hover:bg-emerald-500/10 transition-colors"
                                                title="Complete Task">
                                            <x-heroicon-o-check-circle class="w-4 h-4" />
                                        </button>
                                    </div>
                                </template>
                            </div>

                        </div>
                    </template>

                    <!-- Empty State -->
                    <div x-show="tasks.filter(t => t.status === activeStatus && (activeCategory === '' || t.category_id == activeCategory) && (searchQuery === '' || t.title.toLowerCase().includes(searchQuery.toLowerCase()))).length === 0"
                         class="text-center py-8 space-y-2">
                        <x-heroicon-o-clipboard-document-check class="w-8 h-8 mx-auto text-slate-500/60" />
                        <p class="text-xs text-slate-400 font-medium">All clear in this section!</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Center: Interactive Cozy Lo-Fi Space & Pomodoro Timer -->
        <section class="lg:col-span-5 flex flex-col justify-between space-y-4 lg:h-full lg:min-h-0 overflow-hidden">
            
            <!-- Cozy Room Visual (Visual subject in the middle) -->
            <div class="glass-panel rounded-3xl p-6 flex-grow flex flex-col items-center justify-center relative overflow-hidden transition-all duration-300 min-h-[320px]">
                
                <!-- Ambient Backdrop Window -->
                <div class="absolute top-4 left-1/2 -translate-x-1/2 w-52 h-36 rounded-2xl overflow-hidden border transition-all duration-300 flex items-center justify-center"
                     :class="darkTheme ? 'bg-slate-950/60 border-indigo-950/80' : 'bg-sky-100 border-amber-200'">
                    <!-- Sky with stars (Dark) or golden twilight clouds (Light) -->
                    <div class="absolute inset-0 opacity-40"
                         :style="darkTheme ? 'background-image: radial-gradient(white 1px, transparent 0); background-size: 16px 16px;' : 'background: linear-gradient(to top, #feb47b, #ff7e5f)'"></div>
                    
                    <!-- Raindrops animation -->
                    <div class="absolute inset-0 flex justify-around">
                        <div class="rain-drop w-[1px] h-4 bg-indigo-200/40" style="animation-delay: 0.1s;"></div>
                        <div class="rain-drop w-[1px] h-6 bg-indigo-200/30" style="animation-delay: 0.4s;"></div>
                        <div class="rain-drop w-[1px] h-4 bg-indigo-200/40" style="animation-delay: 0.2s;"></div>
                        <div class="rain-drop w-[1px] h-5 bg-indigo-200/30" style="animation-delay: 0.5s;"></div>
                        <div class="rain-drop w-[1px] h-4 bg-indigo-200/40" style="animation-delay: 0.3s;"></div>
                    </div>

                    <!-- Window Frame -->
                    <div class="w-full h-full border-t-2 border-b-2 border-r-2 border-l-2 border-slate-700/40 pointer-events-none flex">
                        <div class="w-1/2 h-full border-r border-slate-700/40"></div>
                        <div class="w-1/2 h-full"></div>
                    </div>
                </div>

                <!-- Interactive Cozy Desk Illustration (Pure CSS + SVG) -->
                <div class="absolute top-[135px] left-1/2 -translate-x-1/2 w-80 h-48 transition-all duration-300">
                    <!-- Retro Desk Lamp (Taller) -->
                    <div class="absolute left-8 -bottom-6 w-14 h-56 cursor-pointer z-10" @click="lampOn = !lampOn">
                        
                        <div x-show="lampOn" 
                            class="absolute left-[42px] top-[64px] w-56 h-36 pointer-events-none opacity-25" 
                            style="background: radial-gradient(ellipse at top left, rgba(253, 224, 71, 0.85) 0%, rgba(253, 224, 71, 0) 80%); transform: rotate(22deg); transform-origin: top left;"></div>
                        
                        <svg viewBox="0 0 100 200" class="w-full h-full transition-all duration-300"
                            :class="lampOn ? (darkTheme ? 'text-yellow-400' : 'text-amber-500') : 'text-slate-600'">
                            
                            <path d="M20,200 L80,200 L60,180 L40,180 Z" fill="currentColor"/> 
                            
                            <path d="M50,180 L50,45" stroke="currentColor" stroke-width="8" stroke-linecap="round"/> 
                            
                            <path d="M50,45 L70,25" stroke="currentColor" stroke-width="6" stroke-linecap="round"/> 
                            <path d="M55,15 A20,20 0 0,0 95,15 Z" fill="currentColor" :style="lampOn ? 'filter: drop-shadow(0 0 8px currentColor)' : ''"/> 
                        </svg>
                    </div>

                    <!-- Steam Coffee Mug -->
                    <div class="absolute right-12 bottom-6 w-8 h-8 cursor-pointer z-10" @click="mugSteam = !mugSteam">
                        <!-- Animated Steam lines -->
                        <div x-show="mugSteam" class="absolute left-1/2 -top-6 -translate-x-1/2 flex space-x-1.5 opacity-60">
                            <span class="steam-line w-[1.5px] h-3 bg-slate-300 rounded-full block"></span>
                            <span class="steam-line w-[1.5px] h-4 bg-slate-300 rounded-full block"></span>
                            <span class="steam-line w-[1.5px] h-3 bg-slate-300 rounded-full block"></span>
                        </div>
                        <!-- Physical Mug -->
                        <svg viewBox="0 0 100 100" class="w-full h-full text-pink-400">
                            <rect x="25" y="30" width="40" height="50" rx="10" fill="currentColor" />
                            <path d="M65,40 C75,40 75,60 65,60" stroke="currentColor" stroke-width="6" fill="none" stroke-linecap="round"/>
                        </svg>
                    </div>

                    <!-- Glowing Laptop -->
                    <div class="absolute left-1/2 -translate-x-1/2 bottom-4 w-32 h-20 z-10">
                        <!-- Screen Glow -->
                        <div class="absolute left-4 top-2 w-24 h-12 rounded flex items-center justify-center transition-all"
                             :class="darkTheme ? 'bg-indigo-500/20 border border-indigo-400/40 neon-glow-indigo' : 'bg-orange-800/10 border border-orange-800/30'">
                            <span class="text-[8px] font-mono animate-pulse tracking-widest font-extrabold"
                                  :class="darkTheme ? 'text-indigo-300' : 'text-orange-900'">> STUDYING...</span>
                        </div>
                        <!-- Laptop Frame -->
                        <svg viewBox="0 0 120 80" class="w-full h-full transition-colors" :class="darkTheme ? 'text-slate-500' : 'text-amber-800'">
                            <!-- screen back -->
                            <rect x="15" y="5" width="90" height="55" rx="4" :fill="darkTheme ? '#334155' : '#d4a373'" />
                            <rect x="18" y="8" width="84" height="49" rx="2" :fill="darkTheme ? '#0f172a' : '#faedcd'" />
                            <!-- Keyboard base -->
                            <path d="M5,60 L115,60 L120,70 L0,70 Z" :fill="darkTheme ? '#475569' : '#dda15e'" />
                            <!-- glowing keyboard strip -->
                            <rect x="20" y="62" width="80" height="3" :fill="darkTheme ? '#818cf8' : '#fefae0'" opacity="0.6"/>
                        </svg>
                    </div>

                    <!-- Desk Base Surface -->
                    <div class="absolute bottom-0 left-0 right-0 h-4 rounded-full shadow-lg"
                         :class="darkTheme ? 'bg-slate-900/90' : 'bg-amber-100'"></div>
                </div>

            </div>

            <!-- Pomodoro Timer Panel -->
            <div class="glass-panel rounded-3xl p-3.5 py-2.5 flex flex-col items-center justify-center space-y-2 transition-all duration-500"
                 :style="timerGlow ? (darkTheme ? 'border: 2px solid rgba(129,140,248,0.9); box-shadow: 0 0 14px rgba(129,140,248,0.55), inset 0 0 6px rgba(129,140,248,0.15);' : 'border: 2px solid rgba(245,158,11,0.9); box-shadow: 0 0 14px rgba(245,158,11,0.55), inset 0 0 6px rgba(245,158,11,0.15);') : ''">
                
                <!-- Header -->
                <div class="flex items-center justify-between w-full px-2">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-o-clock class="w-4 h-4" ::class="darkTheme ? 'text-indigo-400' : 'text-amber-700'" />
                        <span class="text-xs font-bold uppercase tracking-wider" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Pomodoro Flow</span>
                    </div>
                </div>

                <!-- Loop / Status Indicator -->
                <div class="text-[10px] font-bold uppercase tracking-wider text-center" :class="darkTheme ? 'text-indigo-400' : 'text-amber-800'">
                    <template x-if="isTimerRunning">
                        <span x-text="isBreakMode ? 'Break Time • Loop ' + currentLoop + ' of ' + timerLoops : 'Focusing • Loop ' + currentLoop + ' of ' + timerLoops"></span>
                    </template>
                    <template x-if="!isTimerRunning">
                        <span x-text="timerStatusText ? timerStatusText : 'Ready • Loop ' + currentLoop + ' of ' + timerLoops"></span>
                    </template>
                </div>

                <!-- Large Time Display -->
                <div class="flex items-baseline space-x-1 font-mono text-3xl font-extrabold tracking-widest"
                     :class="darkTheme ? 'text-indigo-300 drop-shadow-[0_0_8px_rgba(99,102,241,0.5)]' : 'text-slate-800'">
                    <span x-text="String(timerMinutes).padStart(2, '0')"></span>
                    <span class="animate-pulse">:</span>
                    <span x-text="String(timerSeconds).padStart(2, '0')"></span>
                </div>

                <!-- Controls: Start/Pause, Reset -->
                <div class="flex space-x-2.5">
                    <button @click="
                        isTimerRunning = !isTimerRunning;
                        if (isTimerRunning) {
                            timerStatusText = isBreakMode ? 'Break Time!' : 'Focusing (Loop ' + currentLoop + ')';
                            timerInterval = setInterval(() => {
                                if (timerSeconds === 0) {
                                    if (timerMinutes === 0) {
                                        triggerGlow();
                                        if (!isBreakMode) {
                                            completePomodoroDb();
                                            isBreakMode = true;
                                            timerMinutes = customBreakDuration;
                                            timerSeconds = 0;
                                            timerStatusText = 'Break Time!';
                                        } else {
                                            if (currentLoop < timerLoops) {
                                                currentLoop++;
                                                isBreakMode = false;
                                                timerMinutes = customPomodoroDuration;
                                                timerSeconds = 0;
                                                timerStatusText = 'Focusing (Loop ' + currentLoop + ')';
                                            } else {
                                                clearInterval(timerInterval);
                                                isTimerRunning = false;
                                                currentLoop = 1;
                                                isBreakMode = false;
                                                timerMinutes = customPomodoroDuration;
                                                timerSeconds = 0;
                                                timerStatusText = 'Session Completed!';
                                            }
                                        }
                                    } else {
                                        timerMinutes--;
                                        timerSeconds = 59;
                                    }
                                } else {
                                    timerSeconds--;
                                }
                            }, 1000);
                        } else {
                            clearInterval(timerInterval);
                        }
                    " class="px-3.5 py-1.5 rounded-xl text-[11px] font-bold uppercase tracking-wider transition-all duration-300 hover:scale-105"
                       :class="isTimerRunning ? (darkTheme ? 'bg-red-500/20 border border-red-500/40 text-red-300' : 'bg-red-600 text-white') : (darkTheme ? 'bg-indigo-500/20 border border-indigo-500/40 text-indigo-300' : 'bg-amber-600 text-white')">
                        <span x-text="isTimerRunning ? 'Pause' : (isBreakMode ? 'Start Break' : 'Start Focus')"></span>
                    </button>

                    <button @click="
                        clearInterval(timerInterval);
                        isTimerRunning = false;
                        isBreakMode = false;
                        currentLoop = 1;
                        timerMinutes = customPomodoroDuration;
                        timerSeconds = 0;
                        timerStatusText = '';
                    " class="px-3 py-1.5 rounded-xl text-[11px] font-bold uppercase tracking-wider border transition-all duration-300 hover:scale-105"
                       :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-300 hover:bg-slate-900' : 'bg-amber-50 border-amber-200 text-amber-800'">
                        Reset
                    </button>
                </div>

                <!-- Adjusters grid directly below controls -->
                <div class="grid grid-cols-3 gap-1.5 w-full pt-2.5 border-t" :class="darkTheme ? 'border-slate-800/60' : 'border-slate-200'">
                    <!-- Focus Control -->
                    <div class="flex flex-col items-center p-1.5 rounded-xl bg-slate-900/20 border" :class="darkTheme ? 'border-slate-800/40' : 'border-amber-200/50 bg-amber-50/30'">
                        <span class="text-[8px] uppercase tracking-wider font-bold text-slate-400">Focus</span>
                        <div class="flex items-center space-x-1 mt-0.5">
                            <span class="text-xs font-extrabold" x-text="customPomodoroDuration"></span>
                            <div class="flex flex-col space-y-0.5">
                                <button @click="incrementFocus()" class="p-0.5 rounded hover:bg-slate-700/20 text-slate-400 hover:text-slate-200 transition leading-none">
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 15l7-7 7 7" /></svg>
                                </button>
                                <button @click="decrementFocus()" class="p-0.5 rounded hover:bg-slate-700/20 text-slate-400 hover:text-slate-200 transition leading-none">
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Break Control -->
                    <div class="flex flex-col items-center p-1.5 rounded-xl bg-slate-900/20 border" :class="darkTheme ? 'border-slate-800/40' : 'border-amber-200/50 bg-amber-50/30'">
                        <span class="text-[8px] uppercase tracking-wider font-bold text-slate-400">Break</span>
                        <div class="flex items-center space-x-1 mt-0.5">
                            <span class="text-xs font-extrabold" x-text="customBreakDuration"></span>
                            <div class="flex flex-col space-y-0.5">
                                <button @click="incrementBreak()" class="p-0.5 rounded hover:bg-slate-700/20 text-slate-400 hover:text-slate-200 transition leading-none">
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 15l7-7 7 7" /></svg>
                                </button>
                                <button @click="decrementBreak()" class="p-0.5 rounded hover:bg-slate-700/20 text-slate-400 hover:text-slate-200 transition leading-none">
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Loops Control -->
                    <div class="flex flex-col items-center p-1.5 rounded-xl bg-slate-900/20 border" :class="darkTheme ? 'border-slate-800/40' : 'border-amber-200/50 bg-amber-50/30'">
                        <span class="text-[8px] uppercase tracking-wider font-bold text-slate-400">Loops</span>
                        <div class="flex items-center space-x-1 mt-0.5">
                            <span class="text-xs font-extrabold" x-text="timerLoops"></span>
                            <div class="flex flex-col space-y-0.5">
                                <button @click="incrementLoops()" class="p-0.5 rounded hover:bg-slate-700/20 text-slate-400 hover:text-slate-200 transition leading-none">
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 15l7-7 7 7" /></svg>
                                </button>
                                <button @click="decrementLoops()" class="p-0.5 rounded hover:bg-slate-700/20 text-slate-400 hover:text-slate-200 transition leading-none">
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

        </section>

        <!-- Right Side: Gamification, Notes & Calendar -->
        <section class="lg:col-span-3 flex flex-col space-y-4 lg:h-full lg:min-h-0 overflow-hidden">
            
            <!-- Gamification: Quests -->
            <div class="glass-panel rounded-2xl p-5 space-y-3.5 flex flex-col min-h-0 flex-grow">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold tracking-wide text-sm uppercase" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Active Quests</h3>
                    <x-heroicon-o-fire class="w-5 h-5 text-orange-400 animate-bounce" />
                </div>
                <div class="space-y-2.5 flex-grow overflow-y-auto pr-1 min-h-0">
                    <template x-for="quest in quests" :key="quest.id">
                        <div class="p-2.5 rounded-xl border flex items-center justify-between space-x-2 transition-all duration-300"
                             :class="darkTheme ? 'bg-slate-900/20 border-slate-800' : 'bg-white border-amber-100'">
                            <div class="space-y-0.5 flex-grow pr-2">
                                <span class="text-xs font-bold block" :class="darkTheme ? 'text-slate-200' : 'text-slate-800'" x-text="quest.title"></span>
                                <div class="flex flex-col space-y-0.5">
                                    <span class="text-[9px] uppercase font-extrabold tracking-wider" :class="darkTheme ? 'text-indigo-400' : 'text-amber-600'">
                                        +<span x-text="quest.points_reward"></span> XP (<span x-text="quest.type"></span>)
                                    </span>
                                    <!-- Simple Progress Indicator -->
                                    <div class="flex items-center space-x-1.5 w-full">
                                        <div class="flex-grow bg-slate-700/30 rounded-full h-1 overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500" 
                                                 :class="darkTheme ? 'bg-indigo-400' : 'bg-amber-600'"
                                                 :style="`width: ${Math.min(100, (quest.current_value / quest.goal_value) * 100)}%`"></div>
                                        </div>
                                        <span class="text-[8px] text-slate-400 font-bold font-mono">
                                            <span x-text="quest.current_value"></span>/<span x-text="quest.goal_value"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center flex-shrink-0">
                                <template x-if="quest.completed">
                                    <x-heroicon-m-check-badge class="w-5 h-5 text-emerald-400" />
                                </template>
                                <template x-if="!quest.completed">
                                    <div class="w-5 h-5 rounded-full border border-slate-600/60 flex items-center justify-center text-[9px] text-slate-400 font-bold">
                                        Q
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Mini Calendar Widget -->
            <div class="glass-panel rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-1">
                        <button @click="prevMonth()" class="p-1 rounded-md hover:bg-slate-700/20 text-slate-400 hover:text-slate-200 transition-colors" title="Previous Month">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                        </button>
                        <h3 class="font-bold tracking-wide text-[11px] uppercase font-mono text-center min-w-[100px]" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'" x-text="calendarMonthName()"></h3>
                        <button @click="nextMonth()" class="p-1 rounded-md hover:bg-slate-700/20 text-slate-400 hover:text-slate-200 transition-colors" title="Next Month">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </button>
                    </div>
                    <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" />
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-slate-400">
                    <div>S</div><div>M</div><div>T</div><div>W</div><div>T</div><div>F</div><div>S</div>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-xs">
                    <template x-for="day in getDaysInMonth()">
                        <div class="py-1 rounded-md relative flex items-center justify-center transition-all duration-300"
                             :class="[
                                day.day ? 'cursor-pointer hover:scale-110' : 'pointer-events-none',
                                day.isToday ? (darkTheme ? 'bg-indigo-500/25 text-indigo-300 font-extrabold border border-indigo-500/40' : 'bg-amber-250 text-amber-950 font-extrabold border border-amber-300') : '',
                                day.day ? (darkTheme ? 'text-slate-300 hover:bg-slate-800/60' : 'text-slate-800 hover:bg-amber-100/60') : 'opacity-0'
                             ]"
                             @click="if (day.day) { selectedCalendarDate = day.dateString; selectedCalendarDay = day.day; newCalendarTask = { title: '', priority: 'low', category_id: '' }; showCalendarDayModal = true; }">
                            <span x-text="day.day || ''"></span>
                            <template x-if="day.day && day.hasDue">
                                <span class="absolute bottom-0.5 w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Ambient Cassette Player Widget -->
            <div class="glass-panel rounded-2xl p-4 flex items-center justify-between border-t border-slate-700/20 shadow-lg">
                <div class="flex items-center space-x-2">
                    <x-heroicon-o-musical-note class="w-5 h-5 animate-bounce" ::class="darkTheme ? 'text-indigo-400' : 'text-amber-700'" />
                    <div>
                        <span class="text-[10px] uppercase tracking-widest font-bold block" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Lofi Player</span>
                        <span class="text-xs font-semibold block text-slate-300" x-text="activeTrack === 'lofi' ? 'Cozy Study Radio' : (activeTrack === 'rain' ? 'Rainstorm loop' : 'Campfire loop')"></span>
                    </div>
                </div>

                <!-- Player Actions -->
                <div class="flex items-center space-x-2">
                    <!-- Source selector button -->
                    <button @click="
                        activeTrack = activeTrack === 'lofi' ? 'rain' : (activeTrack === 'rain' ? 'campfire' : 'lofi');
                        audioSource = activeTrack === 'lofi' ? 'https://stream.zeno.fm/0r0xa792kwzuv' : (activeTrack === 'rain' ? 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3' : 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3');
                        if (audioPlaying) { $refs.lofiPlayer.load(); $refs.lofiPlayer.play(); }
                    " class="p-1.5 rounded-lg border transition-colors"
                       :class="darkTheme ? 'bg-slate-800/40 border-slate-700 text-slate-400 hover:text-slate-200' : 'bg-amber-50 border-amber-200 text-amber-700 hover:text-amber-900'">
                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                    </button>

                    <button @click="
                        audioPlaying = !audioPlaying;
                        if (audioPlaying) { $refs.lofiPlayer.play(); } else { $refs.lofiPlayer.pause(); }
                    " class="p-2 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-105"
                       :class="audioPlaying ? (darkTheme ? 'bg-pink-500/20 border border-pink-500/40 text-pink-300' : 'bg-amber-600 text-white') : (darkTheme ? 'bg-indigo-500/20 border border-indigo-500/40 text-indigo-300' : 'bg-amber-200 text-amber-950')">
                        <template x-if="!audioPlaying">
                            <x-heroicon-s-play class="w-4 h-4" />
                        </template>
                        <template x-if="audioPlaying">
                            <x-heroicon-s-pause class="w-4 h-4" />
                        </template>
                    </button>
                </div>

                <!-- Hidden HTML5 Audio Element -->
                <audio x-ref="lofiPlayer" loop :src="audioSource"></audio>
            </div>

        </section>

    </main>

    <!-- Floating Draggable Notes Board -->
    <div x-show="showNotesWindow"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed z-40 w-80 rounded-2xl glass-panel border shadow-2xl flex flex-col overflow-hidden"
         :class="darkTheme ? 'border-slate-800' : 'border-amber-200'"
         :style="`left: ${notesPosition.x}px; top: ${notesPosition.y}px;`"
         @mousedown="
             if ($event.target.closest('.drag-handle')) {
                 isDraggingNotes = true;
                 dragStart = { x: $event.clientX - notesPosition.x, y: $event.clientY - notesPosition.y };
             }
         ">
         <!-- Drag Handle / Header -->
         <div class="drag-handle cursor-move px-4 py-3 border-b flex justify-between items-center bg-slate-900/30"
              :class="darkTheme ? 'border-slate-800/80' : 'border-amber-150'">
              <div class="flex items-center space-x-1.5 text-xs font-bold uppercase tracking-wider"
                   :class="darkTheme ? 'text-indigo-300' : 'text-amber-900'">
                  <x-heroicon-o-document-text class="w-4 h-4" />
                  <span>Notes</span>
              </div>
              <div class="flex items-center space-x-1.5">
                  <button @click="selectedNote = { id: null, title: '', content: '' }; showNoteModal = true"
                          class="p-1 rounded hover:bg-slate-700/20 text-slate-400 hover:text-slate-200 transition-colors">
                      <x-heroicon-o-plus class="w-3.5 h-3.5" />
                  </button>
                  <button @click="showNotesWindow = false"
                          class="p-1 rounded hover:bg-slate-700/20 text-slate-400 hover:text-slate-200 transition-colors">
                      <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                  </button>
              </div>
         </div>
         
         <!-- Content Area -->
         <div class="p-4 space-y-2.5 max-h-[300px] overflow-y-auto pr-2">
             <template x-for="(note, index) in notes" :key="note.id">
                 <div x-data="{ isDraggable: false }"
                      :draggable="isDraggable"
                      @dragstart="draggedNoteIndex = index; event.dataTransfer.effectAllowed = 'move'; event.dataTransfer.setData('text/plain', index);"
                      @dragover.prevent
                      @dragenter.prevent
                      @drop="reorderNotes(draggedNoteIndex, index)"
                      @dragend="draggedNoteIndex = null; isDraggable = false"
                      @click="selectedNote = { id: note.id, title: note.title, content: note.content }; showNoteModal = true"
                      class="group relative p-3 rounded-xl border cursor-pointer transition-all duration-300 hover:scale-[1.01] flex flex-col min-w-0 w-full"
                      :class="draggedNoteIndex === index ? 'opacity-30 border-dashed border-indigo-400/60' : (darkTheme ? 'bg-slate-900/20 border-slate-800 hover:bg-slate-900/40' : 'bg-white border-amber-100 hover:bg-amber-50/40')"
                      style="min-width: 0;">
                      
                      <!-- Header with Title & Action Menu -->
                      <div class="flex items-start justify-between gap-2 min-w-0 w-full">
                          <span class="text-xs font-bold block min-w-0 flex-grow"
                                style="overflow-wrap: break-word; word-break: break-word; min-width: 0;"
                                :class="darkTheme ? 'text-slate-200' : 'text-slate-800'"
                                x-text="note.title"></span>
                          
                          <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex-shrink-0 pointer-events-none group-hover:pointer-events-auto" @click.stop>
                              <!-- Drag Handle -->
                              <div class="p-1 rounded transition-colors cursor-grab active:cursor-grabbing pointer-events-auto"
                                   @mousedown="isDraggable = true"
                                   @mouseup="isDraggable = false"
                                   @touchstart="isDraggable = true"
                                   @touchend="isDraggable = false"
                                   :class="darkTheme ? 'hover:bg-slate-800 text-slate-400 hover:text-slate-200' : 'hover:bg-slate-100 text-slate-400 hover:text-slate-700'"
                                   title="Drag">
                                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                      <circle cx="9" cy="5" r="1.5" fill="currentColor"/>
                                      <circle cx="9" cy="12" r="1.5" fill="currentColor"/>
                                      <circle cx="9" cy="19" r="1.5" fill="currentColor"/>
                                      <circle cx="15" cy="5" r="1.5" fill="currentColor"/>
                                      <circle cx="15" cy="12" r="1.5" fill="currentColor"/>
                                      <circle cx="15" cy="19" r="1.5" fill="currentColor"/>
                                  </svg>
                              </div>
                              
                              <!-- Edit Button -->
                              <button @click.stop="selectedNote = { id: note.id, title: note.title, content: note.content }; showNoteModal = true"
                                      class="p-1 rounded transition-colors pointer-events-auto"
                                      :class="darkTheme ? 'hover:bg-indigo-500/10 text-slate-400 hover:text-indigo-300' : 'hover:bg-amber-100 text-slate-400 hover:text-amber-800'"
                                      title="Edit Note">
                                  <x-heroicon-o-pencil class="w-3.5 h-3.5" />
                              </button>
                              
                              <!-- Delete Button -->
                              <button @click.stop="deleteNoteDb(note.id)"
                                      class="p-1 rounded transition-colors pointer-events-auto"
                                      :class="darkTheme ? 'hover:bg-red-500/10 text-slate-400 hover:text-red-400' : 'hover:bg-red-50 text-slate-400 hover:text-red-650'"
                                      title="Delete Note">
                                  <x-heroicon-o-trash class="w-3.5 h-3.5" />
                              </button>
                          </div>
                      </div>
                      
                      <!-- Content Preview -->
                      <p class="text-[10px] text-slate-400 mt-1 truncate min-w-0"
                         x-text="note.content ? (note.content.substring(0, 40) + (note.content.length > 40 ? '...' : '')) : ''"></p>
                 </div>
             </template>
             <div x-show="notes.length === 0" class="text-center py-6 text-xs text-slate-400">
                 No notes yet. Click the + button above to create one!
             </div>
         </div>
    </div>

    <!-- Notes Detail Modal -->
    <div x-show="showNoteModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-transition>
        <div class="glass-panel w-full max-w-md rounded-2xl p-6 space-y-4"
             @click.away="showNoteModal = false">
            <div class="flex justify-between items-center">
                <input x-model="selectedNote.title" 
                       type="text" 
                       placeholder="Note Title" 
                       class="font-bold text-lg outline-none border-b bg-transparent w-4/5"
                       :class="darkTheme ? 'text-indigo-200 border-slate-700 focus:border-indigo-500' : 'text-amber-950 border-amber-200 focus:border-amber-500'" />
                <button @click="showNoteModal = false" class="text-slate-400 hover:text-slate-200">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>
            <textarea x-model="selectedNote.content" 
                      placeholder="Type your notes here..." 
                      class="w-full h-40 resize-none outline-none bg-transparent text-sm"
                      :class="darkTheme ? 'text-slate-300' : 'text-slate-700'"></textarea>
            <div class="flex justify-between items-center">
                <div>
                    <template x-if="selectedNote.id">
                        <button @click="deleteNoteDb(selectedNote.id)"
                                class="px-3 py-2 text-xs font-semibold rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20 transition-all duration-300 hover:scale-105">
                            Delete
                        </button>
                    </template>
                </div>
                <div class="flex space-x-2">
                    <button @click="showNoteModal = false"
                            class="px-4 py-2 text-xs font-semibold rounded-xl border transition-all duration-300 hover:scale-105"
                            :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-300' : 'bg-amber-50 border-amber-200 text-amber-950'">
                        Cancel
                    </button>
                    <button @click="saveNoteDb()"
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-white transition-all duration-300 hover:scale-105"
                            :class="darkTheme ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-amber-600 hover:bg-amber-700'">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div x-show="showAddTaskModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-transition>
        <div class="glass-panel w-full max-w-md rounded-2xl p-6 space-y-4"
             @click.away="showAddTaskModal = false">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-lg" :class="darkTheme ? 'text-indigo-200' : 'text-amber-950'">New Task Flow</h3>
                <button @click="showAddTaskModal = false" class="text-slate-400 hover:text-slate-200">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>
            
            <div class="space-y-3 text-sm">
                <!-- Task Title -->
                <div class="flex flex-col space-y-1">
                    <label class="font-semibold text-xs uppercase tracking-wider" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Task Name</label>
                    <input x-model="newTask.title" 
                           type="text" 
                           placeholder="What are you studying today?" 
                           class="px-3 py-2 rounded-xl border outline-none transition-all duration-300 font-medium"
                           :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500'" />
                </div>

                <!-- Priority Level -->
                <div class="flex flex-col space-y-1">
                    <label class="font-semibold text-xs uppercase tracking-wider" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Priority</label>
                    <select x-model="newTask.priority"
                            class="px-3 py-2 rounded-xl border outline-none transition-all duration-300 font-medium"
                            :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500'">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                <!-- Category -->
                <div class="flex flex-col space-y-1">
                    <label class="font-semibold text-xs uppercase tracking-wider" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Category</label>
                    <select x-model="newTask.category_id"
                            class="px-3 py-2 rounded-xl border outline-none transition-all duration-300 font-medium"
                            :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500'">
                        <option value="">No Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Due Date -->
                <div class="flex flex-col space-y-1">
                    <label class="font-semibold text-xs uppercase tracking-wider" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Due Date</label>
                    <input x-model="newTask.due_date" 
                           type="date" 
                           class="px-3 py-2 rounded-xl border outline-none transition-all duration-300 font-medium"
                           :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500'" />
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button @click="showAddTaskModal = false"
                        class="px-4 py-2 text-xs font-semibold rounded-xl border transition-all duration-300 hover:scale-105"
                        :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-300' : 'bg-amber-50 border-amber-200 text-amber-950'">
                    Cancel
                </button>
                <button @click="saveTaskDb()"
                        class="px-4 py-2 text-xs font-semibold rounded-xl text-white transition-all duration-300 hover:scale-105"
                        :class="darkTheme ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-amber-600 hover:bg-amber-700'">
                    Add Task
                </button>
            </div>
        </div>
    </div>

    <!-- Calendar Day Modal (Schedules & Assignments) -->
    <div x-show="showCalendarDayModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-transition>
        <div class="glass-panel w-full max-w-lg rounded-2xl p-6 space-y-4"
             @click.away="showCalendarDayModal = false">
            <div class="flex justify-between items-center border-b pb-2"
                 :class="darkTheme ? 'border-slate-800' : 'border-amber-250'">
                <div>
                    <h3 class="font-bold text-lg" :class="darkTheme ? 'text-indigo-200' : 'text-amber-950'">
                        Schedules & Assignments
                    </h3>
                    <p class="text-xs font-mono text-slate-400" x-text="selectedCalendarDate"></p>
                </div>
                <button @click="showCalendarDayModal = false" class="text-slate-400 hover:text-slate-200">
                    <x-heroicon-o-x-mark class="w-6 h-6" />
                </button>
            </div>
            
            <!-- List of events / assignments on this day -->
            <div class="space-y-2.5 max-h-[180px] overflow-y-auto pr-1">
                <h4 class="text-xs uppercase font-extrabold tracking-wider" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Schedules for Today</h4>
                
                <template x-for="task in tasks.filter(t => t.due_date === selectedCalendarDate)" :key="task.id">
                    <div class="p-2.5 rounded-xl border flex items-center justify-between transition-all duration-300"
                         :class="darkTheme ? 'bg-slate-900/35 border-slate-800 hover:bg-slate-900/60' : 'bg-white border-amber-100 hover:bg-amber-50/40'">
                        <div class="flex items-center space-x-3 flex-grow">
                            <!-- Click check to complete/uncomplete directly in modal -->
                            <button @click="toggleTaskStatus(task, task.status === 'done' ? 'todo' : 'done')" 
                                    class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all duration-300"
                                    :class="task.status === 'done' ? (darkTheme ? 'bg-indigo-500 border-indigo-500 text-slate-950' : 'bg-amber-600 border-amber-600 text-white') : (darkTheme ? 'border-slate-600 hover:border-indigo-400' : 'border-amber-300 hover:border-amber-600')">
                                <template x-if="task.status === 'done'">
                                    <x-heroicon-m-check class="w-3 h-3 stroke-[3]" />
                                </template>
                            </button>
                            <div>
                                <span class="text-xs font-bold block" :class="task.status === 'done' ? 'line-through text-slate-500' : (darkTheme ? 'text-slate-200' : 'text-slate-850')" x-text="task.title"></span>
                                <div class="flex items-center space-x-1.5 mt-0.5">
                                    <span class="text-[8px] px-1 py-0.5 rounded-full font-extrabold uppercase"
                                          :class="
                                            task.priority === 'high' ? 'bg-red-500/10 text-red-400' :
                                            task.priority === 'medium' ? 'bg-indigo-500/10 text-indigo-400' : 'bg-emerald-500/10 text-emerald-400'
                                          " x-text="task.priority"></span>
                                    <span class="text-[8px] px-1 py-0.5 rounded-full font-extrabold uppercase bg-slate-800 text-slate-400" x-text="task.status === 'in_progress' ? 'In Progress' : (task.status === 'done' ? 'Completed' : 'To Do')"></span>
                                </div>
                            </div>
                        </div>
                        <button @click="deleteTaskDb(task.id)" class="p-1 rounded-lg text-slate-400 hover:text-red-400 transition-colors" title="Delete Schedule">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                        </button>
                    </div>
                </template>
                
                <div x-show="tasks.filter(t => t.due_date === selectedCalendarDate).length === 0" 
                     class="p-4 rounded-xl border border-dashed border-slate-700/60 text-center text-xs text-slate-400">
                    No events or assignments scheduled for this date.
                </div>
            </div>

            <!-- Form to add new task/schedule for this day -->
            <div class="border-t pt-4 space-y-3" :class="darkTheme ? 'border-slate-800' : 'border-amber-250'">
                <h4 class="text-xs uppercase font-extrabold tracking-wider" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Add New Schedule</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="flex flex-col space-y-1">
                        <label class="font-semibold text-[10px] uppercase tracking-wider" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Schedule Name</label>
                        <input x-model="newCalendarTask.title" 
                               type="text" 
                               placeholder="Study Chapter / Event..." 
                               class="px-2.5 py-1.5 rounded-xl border outline-none text-xs transition-all duration-300 font-medium"
                               :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500'" />
                    </div>
                    
                    <div class="flex flex-col space-y-1">
                        <label class="font-semibold text-[10px] uppercase tracking-wider" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Priority</label>
                        <select x-model="newCalendarTask.priority"
                                class="px-2.5 py-1.5 rounded-xl border outline-none text-xs transition-all duration-300 font-medium"
                                :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500'">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    <div class="flex flex-col space-y-1 md:col-span-2">
                        <label class="font-semibold text-[10px] uppercase tracking-wider" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Category</label>
                        <select x-model="newCalendarTask.category_id"
                                class="px-2.5 py-1.5 rounded-xl border outline-none text-xs transition-all duration-300 font-medium"
                                :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500'">
                            <option value="">No Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end pt-2">
                    <button @click="addCalendarTaskDb()" 
                            class="px-4 py-2 text-xs font-semibold rounded-xl text-white transition-all duration-300 hover:scale-105"
                            :class="darkTheme ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-amber-600 hover:bg-amber-700'">
                        Add Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Immersive Footer Panel -->
    <footer class="w-full px-6 py-3 flex justify-between items-center text-[10px] tracking-wider uppercase font-bold"
            :class="darkTheme ? 'text-slate-500 border-t border-slate-900' : 'text-amber-700/80 border-t border-amber-200'">
        <span>StudyFlow Project Group © 2026</span>
        <span>Cozy Study Music Player and Productive Workspace</span>
    </footer>

</body>
</html>
