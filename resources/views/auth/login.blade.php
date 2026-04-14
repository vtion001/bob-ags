<x-guest-layout>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-[#0A1628] mb-2">Welcome Back</h2>
            <p class="text-gray-500 text-sm">Sign in to access your QA dashboard</p>
        </div>

        <!-- Email Address -->
        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-medium mb-1" />
            <x-text-input id="email" class="block mt-1 w-full border-gray-300 focus:border-[#0A1628] focus:ring-[#0A1628] rounded-md shadow-sm" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-medium mb-1" />
            <x-text-input id="password" class="block mt-1 w-full border-gray-300 focus:border-[#0A1628] focus:ring-[#0A1628] rounded-md shadow-sm" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mb-6">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#0A1628] shadow-sm focus:ring-[#0A1628]" name="remember">
                <span class="ms-2 text-sm text-gray-600">Remember me</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm text-[#0A1628] hover:underline font-medium" href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#0A1628] hover:bg-[#1A2640] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0A1628] transition-colors">
            Sign In
        </button>
    </form>
</x-guest-layout>
