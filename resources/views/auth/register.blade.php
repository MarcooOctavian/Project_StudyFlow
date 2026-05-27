<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold tracking-wider" :class="darkTheme ? 'text-slate-100' : 'text-slate-800'">Create Account</h2>
        <p class="text-sm mt-1" :class="darkTheme ? 'text-slate-400' : 'text-slate-600'">Join the StudyFlow space</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-semibold mb-1" :class="darkTheme ? 'text-indigo-200' : 'text-amber-900'">Full Name</label>
            <input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name"
                   class="w-full px-4 py-2.5 rounded-xl border outline-none transition-all duration-300 shadow-sm"
                   :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500'" />
            <x-input-error :messages="$errors->get('name')" class="mt-1 text-red-400 text-xs" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-semibold mb-1" :class="darkTheme ? 'text-indigo-200' : 'text-amber-900'">Email Address</label>
            <input id="email" type="email" name="email" :value="old('email')" required autocomplete="username"
                   class="w-full px-4 py-2.5 rounded-xl border outline-none transition-all duration-300 shadow-sm"
                   :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500'" />
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-400 text-xs" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-semibold mb-1" :class="darkTheme ? 'text-indigo-200' : 'text-amber-900'">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 rounded-xl border outline-none transition-all duration-300 shadow-sm"
                   :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500'" />
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-400 text-xs" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold mb-1" :class="darkTheme ? 'text-indigo-200' : 'text-amber-900'">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="w-full px-4 py-2.5 rounded-xl border outline-none transition-all duration-300 shadow-sm"
                   :class="darkTheme ? 'bg-slate-900/60 border-slate-700 text-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500' : 'bg-amber-50 border-amber-200 text-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500'" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-red-400 text-xs" />
        </div>

        <!-- Submit Button -->
        <div class="pt-3">
            <button type="submit"
                    class="w-full py-3 px-4 rounded-xl font-bold tracking-wider uppercase transition-all duration-300 shadow-lg hover:scale-[1.02]"
                    :class="darkTheme ? 'bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 hover:bg-indigo-500/30' : 'bg-amber-600 border border-amber-600 text-white hover:bg-amber-700'">
                Register
            </button>
        </div>

        <div class="text-center pt-3">
            <p class="text-sm" :class="darkTheme ? 'text-slate-400' : 'text-slate-600'">
                Already registered? 
                <a href="{{ route('login') }}" class="font-bold transition-colors hover:underline" :class="darkTheme ? 'text-indigo-400 hover:text-indigo-300' : 'text-amber-600 hover:text-amber-800'">
                    Log in
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
