
<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-[#614c50]">
            Welcome back!
        </h1>
        <p class="text-sm text-[#998387] mt-2">
            Log in to manage your money, one paw at a time.
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Email address"
                class="text-[#614c50] font-semibold" />

            <x-text-input id="email"
                class="block mt-2 w-full rounded-xl border-[#e9cdd0] bg-[#fffafa] focus:border-[#d99ca7] focus:ring-[#d99ca7]"
                type="email"
                name="email"
                :value="old('email')"
                required autofocus autocomplete="username"
                placeholder="Enter your email" />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <x-input-label for="password" value="Password"
                class="text-[#614c50] font-semibold" />

            <x-text-input id="password"
                class="block mt-2 w-full rounded-xl border-[#e9cdd0] bg-[#fffafa] focus:border-[#d99ca7] focus:ring-[#d99ca7]"
                type="password"
                name="password"
                required autocomplete="current-password"
                placeholder="Enter your password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mt-5">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-[#e9cdd0] text-[#d99ca7] focus:ring-[#d99ca7]"
                    name="remember">

                <span class="ms-2 text-sm text-[#806b70]">
                    Remember me
                </span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-sm text-[#c47d8c] hover:underline">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <button type="submit"
            class="w-full mt-7 py-3 rounded-xl bg-[#d99ca7] text-white font-semibold hover:bg-[#c78391] transition duration-200 shadow-sm">
            Log in
        </button>

        <!-- Register -->
        <p class="text-center text-sm text-[#806b70] mt-6">
            Don't have an account?
            <a href="{{ route('register') }}"
               class="font-semibold text-[#c47d8c] hover:underline">
                Create one
            </a>
        </p>
    </form>
</x-guest-layout>
