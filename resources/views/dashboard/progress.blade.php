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
