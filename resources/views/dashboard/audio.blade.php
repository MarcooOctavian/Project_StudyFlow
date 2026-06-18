<!-- Ambient Cassette Player Widget -->
<div class="glass-panel rounded-2xl p-4 flex items-center justify-between border-t border-slate-700/20 shadow-lg">
    <div class="flex items-center space-x-2">
        <x-heroicon-o-musical-note class="w-5 h-5 animate-bounce" ::class="darkTheme ? 'text-indigo-400' : 'text-amber-700'" />
        <div>
            <span class="text-[10px] uppercase tracking-widest font-bold block" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">Lofi Player</span>
            <span class="text-xs font-semibold block text-slate-300" x-text="activeTrack === 'lofi' ? 'Cozy Study Radio' : (activeTrack === 'rain' ? 'Rainstorm loop' : 'Campfire loop')"></span>
        </div>
    </div>

    <!-- Player Actions -->
    <div class="flex items-center space-x-2">
        <!-- Source selector button -->
        <button @click="
            activeTrack = activeTrack === 'lofi' ? 'rain' : (activeTrack === 'rain' ? 'campfire' : 'lofi');
            audioSource = activeTrack === 'lofi' ? 'https://stream.zeno.fm/0r0xa792kwzuv' : (activeTrack === 'rain' ? 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3' : 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3');
            if (audioPlaying) { $refs.lofiPlayer.load(); $refs.lofiPlayer.play(); }
        " class="p-1.5 rounded-lg border transition-colors"
           :class="darkTheme ? 'bg-slate-800/40 border-slate-700 text-slate-400 hover:text-slate-200' : 'bg-amber-50 border-amber-200 text-amber-700 hover:text-amber-900'">
            <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
        </button>

        <button @click="
            audioPlaying = !audioPlaying;
            if (audioPlaying) { $refs.lofiPlayer.play(); } else { $refs.lofiPlayer.pause(); }
        " class="p-2 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-105"
           :class="audioPlaying ? (darkTheme ? 'bg-pink-500/20 border border-pink-500/40 text-pink-300' : 'bg-amber-600 text-white') : (darkTheme ? 'bg-indigo-500/20 border border-indigo-500/40 text-indigo-300' : 'bg-amber-200 text-amber-950')">
            <template x-if="!audioPlaying">
                <x-heroicon-s-play class="w-4 h-4" />
            </template>
            <template x-if="audioPlaying">
                <x-heroicon-s-pause class="w-4 h-4" />
            </template>
        </button>
    </div>

    <!-- Hidden HTML5 Audio Element -->
    <audio x-ref="lofiPlayer" loop :src="audioSource"></audio>
</div>
