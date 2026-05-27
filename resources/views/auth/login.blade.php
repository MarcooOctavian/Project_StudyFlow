<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold tracking-wider" :class="darkTheme ? 'text-slate-100' : 'text-slate-800'">Welcome Back</h2>
        <p class="text-sm mt-1" :class="darkTheme ? 'text-slate-400' : 'text-slate-600'">Continue your StudyFlow journey</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold mb-1" :class="darkTheme ? 'text-indigo-200' : 'text-amber-900'">Email Address</label>
            <input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                   class="w-full px-4 py-2.5 rounded-xl border outline-none transition-all duration-300 shadow-sm"
                   :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500'" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-semibold mb-1" :class="darkTheme ? 'text-indigo-200' : 'text-amber-900'">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full px-4 py-2.5 rounded-xl border outline-none transition-all duration-300 shadow-sm"
                   :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500'" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between pt-2">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded w-4 h-4 transition-colors"
                       :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900' : 'bg-amber-50 border-amber-300 text-amber-600 focus:ring-amber-500'">
                <span class="ms-2 text-sm font-medium" :class="darkTheme ? 'text-slate-300' : 'text-slate-600'">Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold transition-colors hover:underline"
                   :class="darkTheme ? 'text-indigo-400 hover:text-indigo-300' : 'text-amber-600 hover:text-amber-800'"
                   href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit"
                    class="w-full py-3 px-4 rounded-xl font-bold tracking-wider uppercase transition-all duration-300 shadow-lg hover:scale-[1.02]"
                    :class="darkTheme ? 'bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 hover:bg-indigo-500/30' : 'bg-amber-600 border border-amber-600 text-white hover:bg-amber-700'">
                Log in
            </button>
        </div>
        
        <div class="text-center pt-4">
            <p class="text-sm" :class="darkTheme ? 'text-slate-400' : 'text-slate-600'">
                Don't have an account? 
                <a href="{{ route('register') }}" class="font-bold transition-colors hover:underline" :class="darkTheme ? 'text-indigo-400 hover:text-indigo-300' : 'text-amber-600 hover:text-amber-800'">
                    Register
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
