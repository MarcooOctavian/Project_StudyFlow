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
                                <x-heroicon-m-check class="w-3.5 h-3.5 stroke-[3]" />
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
