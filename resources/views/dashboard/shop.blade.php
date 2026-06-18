<!-- Floating Draggable Theme Shop Board -->
<div x-show="showShopWindow"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95"
     class="fixed z-40 w-80 rounded-2xl glass-panel border shadow-2xl flex flex-col overflow-hidden"
     :class="darkTheme ? 'border-slate-800' : (themeMode === 'sakura' ? 'border-pink-300' : 'border-amber-200')"
     :style="`left: ${shopPosition.x}px; top: ${shopPosition.y}px;`"
     @mousedown="
         if ($event.target.closest('.drag-handle')) {
             isDraggingShop = true;
             dragStart = { x: $event.clientX - shopPosition.x, y: $event.clientY - shopPosition.y };
         }
     ">
     <!-- Drag Handle / Header -->
     <div class="drag-handle cursor-move px-4 py-3 border-b flex justify-between items-center bg-slate-900/30"
          :class="darkTheme ? 'border-slate-800/80' : (themeMode === 'sakura' ? 'border-pink-200' : 'border-amber-150')">
          <div class="flex items-center space-x-1.5 text-xs font-bold uppercase tracking-wider"
               :class="darkTheme ? 'text-indigo-300' : (themeMode === 'sakura' ? 'text-pink-700' : 'text-amber-900')">
              <x-heroicon-o-shopping-bag class="w-4 h-4 animate-pulse" />
              <span>Theme Shop</span>
          </div>
          <div class="flex items-center space-x-1.5">
              <button @click="showShopWindow = false"
                      class="p-1 rounded hover:bg-slate-700/20 text-slate-400 hover:text-slate-200 transition-colors">
                  <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
              </button>
          </div>
     </div>
     
     <!-- Content Area -->
     <div class="p-4 space-y-3.5 max-h-[300px] overflow-y-auto pr-2">
         <!-- Wallet Info -->
         <div class="p-3 rounded-xl border flex items-center justify-between"
              :class="darkTheme ? 'bg-indigo-950/20 border-indigo-500/20 text-indigo-300' : (themeMode === 'sakura' ? 'bg-pink-100/40 border-pink-300 text-pink-700' : 'bg-amber-50 border-amber-200 text-amber-900')">
             <div class="flex items-center space-x-2">
                 <x-heroicon-o-trophy class="w-4 h-4 text-yellow-400" />
                 <span class="text-xs font-bold uppercase tracking-wider">Your Balance</span>
             </div>
             <span class="text-sm font-extrabold" x-text="points + ' XP'"></span>
         </div>

         <!-- Sakura Theme Card -->
         <div class="p-3 rounded-xl border flex flex-col space-y-2.5 transition-all duration-300"
              :class="darkTheme ? 'bg-slate-900/20 border-slate-800 hover:bg-slate-900/40' : (themeMode === 'sakura' ? 'bg-pink-50/50 border-pink-200 hover:bg-pink-100/30' : 'bg-white border-amber-100 hover:bg-amber-50/40')">
              <div class="flex justify-between items-start">
                  <div>
                      <span class="text-xs font-bold block" :class="darkTheme ? 'text-slate-200' : 'text-slate-800'">Sakura Bliss</span>
                      <p class="text-[10px] text-slate-400 mt-0.5">Cherry blossom pink spring palette</p>
                  </div>
                  <!-- Premium Badge -->
                  <span class="px-1.5 py-0.5 rounded text-[8px] font-extrabold uppercase bg-pink-500/10 text-pink-500 border border-pink-500/20">Premium</span>
              </div>
              
              <div class="flex items-center justify-between pt-1 border-t" :class="darkTheme ? 'border-slate-800/40' : 'border-pink-100'">
                  <div class="flex items-center space-x-1 text-xs font-bold" :class="themeMode === 'sakura' ? 'text-pink-700' : 'text-slate-500'">
                      <span>3,000</span>
                      <span class="text-[9px] uppercase tracking-wider text-slate-400">XP</span>
                  </div>

                  <template x-if="unlockedThemes.includes('sakura')">
                      <span class="text-[10px] font-extrabold uppercase text-emerald-500 flex items-center space-x-1">
                          <x-heroicon-m-check-badge class="w-4 h-4 text-emerald-400 inline" />
                          <span>Unlocked</span>
                      </span>
                  </template>
                  
                  <template x-if="!unlockedThemes.includes('sakura')">
                      <button @click="buyThemeDb('sakura')"
                              class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider text-white transition-all duration-300 hover:scale-105"
                              :class="points >= 3000 ? 'bg-pink-600 hover:bg-pink-700' : 'bg-slate-600 cursor-not-allowed'"
                              :disabled="points < 3000">
                          Buy Theme
                      </button>
                  </template>
              </div>
         </div>

         <!-- Default Cozy Studio (Locked state is not applicable since it's default) -->
         <div class="p-3 rounded-xl border flex flex-col space-y-1"
              :class="darkTheme ? 'bg-slate-900/10 border-slate-800' : 'bg-white border-amber-100'">
              <div class="flex justify-between items-start">
                  <div>
                      <span class="text-xs font-bold block" :class="darkTheme ? 'text-slate-200' : 'text-slate-800'">Cozy Studio</span>
                      <p class="text-[10px] text-slate-400 mt-0.5">Default study room with light/dark modes</p>
                  </div>
                  <span class="text-[10px] font-extrabold uppercase text-emerald-500 flex items-center space-x-1">
                      <x-heroicon-m-check-badge class="w-4 h-4 text-emerald-400 inline" />
                      <span>Unlocked</span>
                  </span>
              </div>
         </div>
     </div>
</div>
