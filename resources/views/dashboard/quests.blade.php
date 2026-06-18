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
