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
        <!-- Theme Dropdown Selector -->
        <div class="relative" @click.away="showThemeDropdown = false">
            <button @click="showThemeDropdown = !showThemeDropdown"
                    class="p-2 rounded-xl border transition-all duration-300 hover:scale-105 flex items-center justify-center"
                    :class="darkTheme ? 'bg-slate-800/60 border-slate-700 text-slate-300 hover:bg-slate-800' : (themeMode === 'sakura' ? 'bg-pink-100/50 border-pink-300 text-pink-700 hover:bg-pink-100' : 'bg-white border-amber-200 text-amber-800 hover:bg-amber-50')"
                    title="Choose Workspace Theme">
                <x-heroicon-o-swatch class="w-5 h-5" />
            </button>
            
            <!-- Dropdown menu -->
            <div x-show="showThemeDropdown"
                 x-transition
                 class="absolute right-0 mt-2 w-48 rounded-xl shadow-xl border z-50 p-1.5"
                 :class="darkTheme ? 'bg-slate-900 border-slate-800' : (themeMode === 'sakura' ? 'bg-pink-50 border-pink-200' : 'bg-white border-amber-100')"
                 style="display: none;">
                
                <!-- Cozy Studio Dark Option -->
                <button @click="setThemeDb('cozy_studio_dark'); showThemeDropdown = false"
                        class="w-full text-left px-3 py-2 text-xs font-semibold rounded-lg flex items-center justify-between transition"
                        :class="themeMode === 'cozy_studio_dark' ? (darkTheme ? 'bg-indigo-500/20 text-indigo-300' : 'bg-indigo-100 text-indigo-800') : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'">
                    <span>Cozy Studio (Dark)</span>
                </button>
                
                <!-- Cozy Studio Light Option -->
                <button @click="setThemeDb('cozy_studio_light'); showThemeDropdown = false"
                        class="w-full text-left px-3 py-2 text-xs font-semibold rounded-lg flex items-center justify-between transition"
                        :class="themeMode === 'cozy_studio_light' ? 'bg-amber-100 text-amber-900 font-bold' : 'text-slate-500 hover:bg-amber-50 hover:text-amber-800'">
                    <span>Cozy Studio (Light)</span>
                </button>

                <!-- Premium Themes Section -->
                <div class="px-3 py-1 text-[9px] uppercase tracking-wider font-extrabold text-slate-400 border-t my-1" :class="darkTheme ? 'border-slate-800' : 'border-slate-100'">Unlocked Themes</div>

                <!-- Sakura Theme -->
                <template x-if="unlockedThemes.includes('sakura')">
                    <button @click="setThemeDb('sakura'); showThemeDropdown = false"
                            class="w-full text-left px-3 py-2 text-xs font-semibold rounded-lg flex items-center justify-between transition"
                            :class="themeMode === 'sakura' ? 'bg-pink-100 text-pink-700 font-bold' : 'text-pink-500 hover:bg-pink-50 hover:text-pink-700'">
                        <span>Sakura Bliss</span>
                    </button>
                </template>
                <template x-if="!unlockedThemes.includes('sakura')">
                    <div class="px-3 py-2 text-xs font-semibold text-slate-400 flex items-center justify-between opacity-50">
                        <span>Sakura Bliss</span>
                        <x-heroicon-o-lock-closed class="w-3.5 h-3.5 text-slate-500" />
                    </div>
                </template>
            </div>
        </div>

        <!-- Shop Toggle -->
        <button @click="showShopWindow = !showShopWindow" 
                class="p-2 rounded-xl border transition-all duration-300 hover:scale-105"
                :class="showShopWindow ? 'bg-pink-500/20 border-pink-400 text-pink-400' : (darkTheme ? 'bg-slate-800/60 border-slate-700 text-slate-300 hover:bg-slate-800' : (themeMode === 'sakura' ? 'bg-pink-100/50 border-pink-300 text-pink-700 hover:bg-pink-100' : 'bg-white border-amber-200 text-amber-800 hover:bg-amber-50'))"
                title="Theme Shop">
            <x-heroicon-o-shopping-bag class="w-5 h-5" />
        </button>

        <!-- Points Panel -->
        <div class="flex items-center space-x-2 px-3 py-1.5 rounded-lg border text-sm font-semibold transition-all duration-300"
             :class="darkTheme ? 'bg-indigo-950/40 border-indigo-500/30 text-indigo-200' : (themeMode === 'sakura' ? 'bg-pink-100/30 border-pink-300 text-pink-700' : 'bg-amber-50 border-amber-200 text-amber-800')">
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

        <!-- Dark Theme Toggle (Cozy Studio Only) -->
        <template x-if="themeMode.startsWith('cozy_studio_')">
            <button @click="setThemeDb(themeMode === 'cozy_studio_dark' ? 'cozy_studio_light' : 'cozy_studio_dark')" 
                    class="p-2 rounded-xl border transition-all duration-300 hover:scale-105"
                    :class="darkTheme ? 'bg-slate-800/60 border-slate-700 text-yellow-400 hover:bg-slate-800' : 'bg-white border-amber-200 text-indigo-900 hover:bg-amber-50'">
                <template x-if="darkTheme">
                    <x-heroicon-o-sun class="w-5 h-5" />
                </template>
                <template x-if="!darkTheme">
                    <x-heroicon-o-moon class="w-5 h-5" />
                </template>
            </button>
        </template>

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
