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
        <template x-for="cat in categories" :key="cat.id">
            <button @click="activeCategory = cat.id"
                    class="px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-300 flex items-center space-x-1"
                    :class="activeCategory == cat.id ? (darkTheme ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40' : (themeMode === 'sakura' ? 'bg-pink-100 text-pink-700' : (themeMode === 'sky' ? 'bg-sky-100 text-sky-700' : (themeMode === 'monochrome' ? 'bg-neutral-800 text-white border-neutral-900' : 'bg-amber-200 text-amber-900')))) : (darkTheme ? 'bg-slate-800/40 border border-transparent text-slate-400 hover:text-slate-200' : (themeMode === 'sakura' ? 'bg-pink-50 border border-transparent text-pink-600 hover:bg-pink-100' : (themeMode === 'sky' ? 'bg-sky-50 border border-transparent text-sky-600 hover:bg-sky-100' : (themeMode === 'monochrome' ? 'bg-white border border-neutral-200 text-neutral-600 hover:bg-neutral-100' : 'bg-amber-50 border border-transparent text-amber-700/80'))))">
                <span class="w-2 h-2 rounded-full" :style="themeMode === 'monochrome' ? 'background-color: #000000;' : 'background-color: ' + (cat.color ?? '#6366f1')"></span>
                <span x-text="cat.name"></span>
            </button>
        </template>
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
                            <!-- Category Tagging -->
                            <template x-if="task.category_id">
                                <span class="px-1.5 py-0.5 rounded-full uppercase tracking-wider font-extrabold"
                                      :style="themeMode === 'monochrome' ? 'background: #ffffff; color: #000000; border: 1px solid #000000;' : 'background-color: ' + (categories.find(c => c.id == task.category_id)?.color ?? '#6366f1') + '20; color: ' + (categories.find(c => c.id == task.category_id)?.color ?? '#6366f1') + ';'"
                                      x-text="categories.find(c => c.id == task.category_id)?.name"></span>
                            </template>
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
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name"></option>
                    </template>
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
