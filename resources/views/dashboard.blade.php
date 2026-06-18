<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ 
    themeMode: '{{ $user->theme_mode }}',
    unlockedThemes: {{ json_encode($user->unlocked_themes ?? ['cozy_studio']) }},
    showShopWindow: false,
    shopPosition: { x: (window.innerWidth ? window.innerWidth / 2 - 160 : 400), y: 150 },
    isDraggingShop: false,
    showThemeDropdown: false,
    get darkTheme() { return this.themeMode === 'cozy_studio_dark'; },
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
    categories: {{ json_encode($categories->map(function($c) {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'color' => $c->color,
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
                },
                body: JSON.stringify({
                    duration: this.customPomodoroDuration
                })
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
    },
    async buyThemeDb(theme) {
        try {
            const res = await fetch('/dashboard/shop/buy', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ theme })
            });
            const data = await res.json();
            if (data.success) {
                this.points = data.points;
                this.unlockedThemes = data.unlocked_themes;
                alert(data.message);
            } else {
                alert(data.message);
            }
        } catch (err) {
            console.error(err);
        }
    },
    async setThemeDb(themeMode) {
        try {
            const res = await fetch('/dashboard/theme/change', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ theme_mode: themeMode })
            });
            const data = await res.json();
            if (data.success) {
                this.themeMode = data.theme_mode;
            } else {
                alert(data.message);
            }
        } catch (err) {
            console.error(err);
        }
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
    <link href="https://fonts.googleapis.com/css?family=Outfit:300,400,500,600,700|Quicksand:400,500,600,700|VT323&display=swap" rel="stylesheet">

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

        /* Sakura Theme Overrides */
        body.sakura {
            color: #70424d;
        }
        body.sakura .glass-panel {
            background: rgba(255, 235, 240, 0.45);
            border: 1px solid rgba(255, 174, 185, 0.35);
            box-shadow: 0 8px 32px 0 rgba(219, 112, 147, 0.1);
        }
        body.sakura .text-slate-100, body.sakura .text-slate-200, body.sakura .text-slate-300, body.sakura .text-slate-400 {
            color: #7a4b57;
        }
        body.sakura .text-indigo-300, body.sakura .text-indigo-400, body.sakura .text-indigo-200 {
            color: #db7093;
        }
        body.sakura .bg-slate-900\/30 {
            background: rgba(255, 192, 203, 0.2);
        }
        body.sakura .border-slate-800 {
            border-color: rgba(255, 182, 193, 0.35);
        }
        body.sakura ::-webkit-scrollbar-thumb {
            background: rgba(219, 112, 147, 0.3);
        }
        body.sakura ::-webkit-scrollbar-thumb:hover {
            background: rgba(219, 112, 147, 0.55);
        }
        body.sakura ::-webkit-scrollbar-track {
            background: rgba(255, 240, 245, 0.35);
        }

        /* Sky Theme Overrides */
        body.sky {
            color: #0f172a;
        }
        body.sky .glass-panel {
            background: rgba(240, 249, 255, 0.55);
            border: 1px solid rgba(186, 230, 253, 0.45);
            box-shadow: 0 8px 32px 0 rgba(14, 165, 233, 0.08);
        }
        body.sky .text-slate-100, body.sky .text-slate-200, body.sky .text-slate-300, body.sky .text-slate-400 {
            color: #1e293b;
        }
        body.sky .text-indigo-300, body.sky .text-indigo-400, body.sky .text-indigo-200 {
            color: #0284c7;
        }
        body.sky .bg-slate-900\/30 {
            background: rgba(224, 242, 254, 0.4);
        }
        body.sky .border-slate-800 {
            border-color: rgba(186, 230, 253, 0.4);
        }
        body.sky ::-webkit-scrollbar-thumb {
            background: rgba(14, 165, 233, 0.3);
        }
        body.sky ::-webkit-scrollbar-thumb:hover {
            background: rgba(14, 165, 233, 0.55);
        }
        body.sky ::-webkit-scrollbar-track {
            background: rgba(240, 249, 255, 0.4);
        }

        /* Sakura Petals Animation */
        @keyframes sakuraFall {
            0% { transform: translateY(-20px) translateX(0) rotate(0deg); opacity: 0; }
            10% { opacity: 0.8; }
            90% { opacity: 0.8; }
            100% { transform: translateY(105vh) translateX(40px) rotate(360deg); opacity: 0; }
        }
        .sakura-petal {
            position: absolute;
            top: -20px;
            width: 10px;
            height: 7px;
            background: #ffb7c5;
            border-radius: 50% 0 50% 50%;
            animation: sakuraFall 10s infinite linear;
            transform-origin: center;
        }

        /* Sakura Theme Overrides */
        body.sakura {
            color: #70424d;
        }
        body.sakura .glass-panel {
            background: rgba(255, 235, 240, 0.45);
            border: 1px solid rgba(255, 174, 185, 0.35);
            box-shadow: 0 8px 32px 0 rgba(219, 112, 147, 0.1);
        }
        body.sakura .text-slate-100, body.sakura .text-slate-200, body.sakura .text-slate-300, body.sakura .text-slate-400 {
            color: #7a4b57;
        }
        body.sakura .text-indigo-300, body.sakura .text-indigo-400, body.sakura .text-indigo-200 {
            color: #db7093;
        }
        body.sakura .bg-slate-900\/30 {
            background: rgba(255, 192, 203, 0.2);
        }
        body.sakura .border-slate-800 {
            border-color: rgba(255, 182, 193, 0.35);
        }
        body.sakura ::-webkit-scrollbar-thumb {
            background: rgba(219, 112, 147, 0.3);
        }
        body.sakura ::-webkit-scrollbar-thumb:hover {
            background: rgba(219, 112, 147, 0.55);
        }
        body.sakura ::-webkit-scrollbar-track {
            background: rgba(255, 240, 245, 0.35);
        }

        /* Sky Theme Overrides */
        body.sky {
            color: #0f172a;
        }
        body.sky .glass-panel {
            background: rgba(240, 249, 255, 0.55);
            border: 1px solid rgba(186, 230, 253, 0.45);
            box-shadow: 0 8px 32px 0 rgba(14, 165, 233, 0.08);
        }
        body.sky .text-slate-100, body.sky .text-slate-200, body.sky .text-slate-300, body.sky .text-slate-400 {
            color: #1e293b;
        }
        body.sky .text-indigo-300, body.sky .text-indigo-400, body.sky .text-indigo-200 {
            color: #0284c7;
        }
        body.sky .bg-slate-900\/30 {
            background: rgba(224, 242, 254, 0.4);
        }
        body.sky .border-slate-800 {
            border-color: rgba(186, 230, 253, 0.4);
        }
        body.sky ::-webkit-scrollbar-thumb {
            background: rgba(14, 165, 233, 0.3);
        }
        body.sky ::-webkit-scrollbar-thumb:hover {
            background: rgba(14, 165, 233, 0.55);
        }
        body.sky ::-webkit-scrollbar-track {
            background: rgba(240, 249, 255, 0.4);
        }

        /* Monochrome Theme Overrides - Strict Black and White */
        body.monochrome, body.monochrome * {
            color: #000000 !important;
            border-color: #000000 !important;
            text-shadow: none !important;
            box-shadow: none !important;
        }
        body.monochrome svg {
            color: #000000 !important;
            stroke: #000000 !important;
            fill: none !important;
        }
        body.monochrome .glass-panel {
            background: #ffffff !important;
            border: 2px solid #000000 !important;
            box-shadow: 6px 6px 0px 0px #000000 !important;
            border-radius: 0px !important;
        }
        body.monochrome .bg-slate-900\/30, body.monochrome .bg-indigo-950\/20, body.monochrome .bg-indigo-950\/40, body.monochrome .bg-slate-900\/10, body.monochrome .bg-slate-900\/20, body.monochrome .bg-pink-100\/30, body.monochrome .bg-pink-50\/50, body.monochrome .bg-pink-500\/10 {
            background: #ffffff !important;
        }
        body.monochrome button, body.monochrome select, body.monochrome input {
            background: #ffffff !important;
            color: #000000 !important;
            border: 2px solid #000000 !important;
            border-radius: 0px !important;
        }
        body.monochrome button:hover {
            background: #000000 !important;
            color: #ffffff !important;
        }
        body.monochrome .text-indigo-300, body.monochrome .text-indigo-400, body.monochrome .text-indigo-200, body.monochrome .text-yellow-400, body.monochrome .text-emerald-500, body.monochrome .text-pink-500, body.monochrome .text-pink-700, body.monochrome .text-slate-400, body.monochrome .text-slate-500 {
            color: #000000 !important;
        }
        body.monochrome ::-webkit-scrollbar-thumb {
            background: #000000 !important;
            border: 1px solid #000000 !important;
            border-radius: 0px !important;
        }
        body.monochrome ::-webkit-scrollbar-track {
            background: #ffffff !important;
            border: 1px solid #000000 !important;
            border-radius: 0px !important;
        }
        body.monochrome .progress-ring__circle {
            stroke: #000000 !important;
        }

        /* Pixel Theme Overrides - High legibility and contrast */
        body.pixel {
            font-family: 'VT323', monospace;
            color: #ffffff;
            font-size: 1.3rem;
            image-rendering: pixelated;
            text-shadow: 1px 1px 0px #000;
        }
        body.pixel .glass-panel {
            background: #1d0f3a !important;
            border: 4px double #00ffff !important;
            box-shadow: 4px 4px 0px #ff007f !important;
            border-radius: 0px !important;
        }
        body.pixel .text-slate-100, body.pixel .text-slate-200, body.pixel .text-slate-300, body.pixel .text-slate-400, body.pixel .text-slate-500, body.pixel .text-slate-600, body.pixel .text-slate-700, body.pixel .text-slate-800, body.pixel .text-slate-900, body.pixel .text-amber-900, body.pixel .text-amber-850, body.pixel .text-amber-800, body.pixel .text-indigo-950, body.pixel .text-indigo-900 {
            color: #00ffff !important;
            text-shadow: 1px 1px 0px #000000;
        }
        body.pixel .text-white, body.pixel h1, body.pixel h2, body.pixel h3, body.pixel h4, body.pixel h5, body.pixel p, body.pixel span, body.pixel label {
            color: #ffffff !important;
            text-shadow: 1px 1px 0px #000000;
        }
        body.pixel .text-indigo-300, body.pixel .text-indigo-400, body.pixel .text-indigo-200, body.pixel .text-pink-700, body.pixel .text-pink-600, body.pixel .text-amber-700\/80 {
            color: #ff007f !important;
            text-shadow: 1px 1px 0px #000000;
        }
        body.pixel .bg-slate-900\/30 {
            background: #2b1754 !important;
        }
        body.pixel .border-slate-800 {
            border-color: #00ffff !important;
        }
        body.pixel button, body.pixel a, body.pixel select, body.pixel input {
            border: 3px double #00ffff !important;
            border-radius: 0px !important;
            background: #2b1754 !important;
            color: #00ffff !important;
            font-family: 'VT323', monospace !important;
            font-size: 1.15rem !important;
        }
        body.pixel button:hover {
            background: #ff007f !important;
            color: #ffffff !important;
            border-color: #ffffff !important;
        }
        body.pixel ::-webkit-scrollbar-thumb {
            background: #ff007f !important;
        }
        body.pixel ::-webkit-scrollbar-thumb:hover {
            background: #ff55bb !important;
        }
        body.pixel ::-webkit-scrollbar-track {
            background: #1d0f3a !important;
            border: 2px solid #ff007f !important;
        }
    </style>
</head>
<body class="min-h-screen text-slate-100 flex flex-col justify-between overflow-x-hidden lg:h-screen lg:overflow-hidden transition-colors duration-500"
    :class="[themeMode, darkTheme ? 'dark' : 'light']"
    :style="themeMode === 'cozy_studio_dark' ? 'background: linear-gradient(135deg, #090514 0%, #150d2a 50%, #05020a 100%)' : (themeMode === 'cozy_studio_light' ? 'background: linear-gradient(135deg, #fef4e8 0%, #f7d2bc 50%, #fbd5c6 100%)' : (themeMode === 'sakura' ? 'background: linear-gradient(135deg, #fff0f5 0%, #ffe4e1 50%, #ffd1dc 100%)' : (themeMode === 'sky' ? 'background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 50%, #93c5fd 100%)' : (themeMode === 'monochrome' ? 'background: linear-gradient(135deg, #ffffff 0%, #f3f4f6 50%, #e5e7eb 100%)' : 'background: linear-gradient(135deg, #1c0e3a 0%, #3e1b75 50%, #15062c 100%)'))))"
    @mousemove="
        if (isDraggingNotes) { notesPosition.x = $event.clientX - dragStart.x; notesPosition.y = $event.clientY - dragStart.y; }
        if (isDraggingShop) { shopPosition.x = $event.clientX - dragStart.x; shopPosition.y = $event.clientY - dragStart.y; }
    "
    @mouseup="isDraggingNotes = false; isDraggingShop = false">

    <!-- Subtle Falling Sakura Petals -->
    <div x-show="themeMode === 'sakura'" class="fixed inset-0 pointer-events-none z-50 overflow-hidden" style="display: none;">
        <div class="sakura-petal" style="left: 10%; animation-delay: 0s; animation-duration: 9s;"></div>
        <div class="sakura-petal" style="left: 25%; animation-delay: 3s; animation-duration: 12s;"></div>
        <div class="sakura-petal" style="left: 40%; animation-delay: 1s; animation-duration: 10s;"></div>
        <div class="sakura-petal" style="left: 60%; animation-delay: 5s; animation-duration: 11s;"></div>
        <div class="sakura-petal" style="left: 75%; animation-delay: 2s; animation-duration: 8s;"></div>
        <div class="sakura-petal" style="left: 90%; animation-delay: 4s; animation-duration: 13s;"></div>
    </div>

    @include('dashboard.header')

    <!-- Main Workspace -->
    <main class="w-full flex-grow px-6 py-2 grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch lg:h-[calc(100vh-140px)] lg:min-h-0">
        
        <!-- Left Side: Tasks & Progress -->
        <section class="lg:col-span-4 flex flex-col space-y-4 lg:h-full lg:min-h-0 overflow-hidden">
            @include('dashboard.progress')
            @include('dashboard.tasks')
        </section>

        <!-- Center: Interactive Cozy Lo-Fi Space & Pomodoro Timer -->
        <section class="lg:col-span-5 flex flex-col justify-between space-y-4 lg:h-full lg:min-h-0 overflow-hidden">
            @include('dashboard.room')
            @include('dashboard.timer')
        </section>

        <!-- Right Side: Gamification, Notes & Calendar -->
        <section class="lg:col-span-3 flex flex-col space-y-4 lg:h-full lg:min-h-0 overflow-hidden">
            @include('dashboard.quests')
            @include('dashboard.calendar')
            @include('dashboard.audio')
        </section>

    </main>

    @include('dashboard.notes')
    @include('dashboard.shop')

    <!-- Immersive Footer Panel -->
    <footer class="w-full px-6 py-3 flex justify-between items-center text-[10px] tracking-wider uppercase font-bold"
            :class="darkTheme ? 'text-slate-500 border-t border-slate-900' : 'text-amber-700/80 border-t border-amber-200'">
        <span>StudyFlow Project Group © 2026</span>
        <span>Cozy Study Music Player and Productive Workspace</span>
    </footer>

</body>
</html>
