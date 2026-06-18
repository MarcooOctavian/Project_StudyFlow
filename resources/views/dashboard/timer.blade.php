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
</div>
