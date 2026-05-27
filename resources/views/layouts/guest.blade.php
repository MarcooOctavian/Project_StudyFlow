<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkTheme: true }" :class="darkTheme ? 'dark' : ''">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'StudyFlow') }} - Authentication</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css?family=Outfit:300,400,500,600,700|Quicksand:400,500,600,700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Outfit', 'Quicksand', sans-serif;
                transition: background-color 0.8s ease, color 0.8s ease;
            }

            /* Glassmorphism Styles */
            .glass-panel {
                background: rgba(25, 18, 42, 0.45);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.08);
                box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            }

            .light .glass-panel {
                background: rgba(255, 255, 255, 0.65);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.4);
                box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
            }
        </style>
    </head>
    <body class="min-h-screen text-slate-100 flex flex-col items-center justify-center pt-6 sm:pt-0 overflow-x-hidden transition-colors duration-500"
          :class="darkTheme ? 'dark' : 'light'"
          :style="darkTheme ? 'background: linear-gradient(135deg, #090514 0%, #150d2a 50%, #05020a 100%)' : 'background: linear-gradient(135deg, #fef4e8 0%, #f7d2bc 50%, #fbd5c6 100%)'">
        
        <!-- Header with Toggle -->
        <div class="absolute top-0 w-full px-6 py-4 flex justify-between items-center z-10">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300"
                     :class="darkTheme ? 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30' : 'bg-amber-500/20 text-amber-700 border border-amber-500/30'">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 animate-pulse">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xl font-bold tracking-wider uppercase block" :class="darkTheme ? 'text-indigo-300' : 'text-amber-800'">StudyFlow</span>
                    <span class="text-xs tracking-widest block uppercase font-semibold" :class="darkTheme ? 'text-slate-400' : 'text-amber-600/80'">Lo-Fi Study Space</span>
                </div>
            </div>

            <!-- Dark Theme Toggle -->
            <button @click="darkTheme = !darkTheme" 
                    class="p-2 rounded-xl border transition-all duration-300 hover:scale-105 z-50"
                    :class="darkTheme ? 'bg-slate-800/60 border-slate-700 text-yellow-400 hover:bg-slate-800' : 'bg-white border-amber-200 text-indigo-900 hover:bg-amber-50'">
                <template x-if="darkTheme">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                </template>
                <template x-if="!darkTheme">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                </template>
            </button>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-8 py-8 glass-panel rounded-3xl overflow-hidden transition-all duration-300 relative z-10"
             :class="darkTheme ? 'shadow-[0_0_40px_rgba(99,102,241,0.1)]' : 'shadow-xl'">
            {{ $slot }}
        </div>
    </body>
</html>
