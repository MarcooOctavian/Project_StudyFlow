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
