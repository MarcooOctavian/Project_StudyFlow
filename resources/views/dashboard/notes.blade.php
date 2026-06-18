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
