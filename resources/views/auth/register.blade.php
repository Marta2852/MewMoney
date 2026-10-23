
<x-guest-layout>

    <!-- Welcome Section -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-[#614c50]">
            Join the MewMoney family!
        </h1>
        <p class="text-sm text-[#998387] mt-2">
            Start your journey to purrfect finances.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" value="Your name"
                class="text-[#614c50] font-semibold" />

            <x-text-input id="name"
                class="block mt-2 w-full rounded-xl border-[#e9cdd0] bg-[#fffafa] focus:border-[#d99ca7] focus:ring-[#d99ca7]"
                type="text"
                name="name"
                :value="old('name')"
                required autofocus autocomplete="name"
                placeholder="Enter your name" />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-5">
            <x-input-label for="email" value="Email address"
                class="text-[#614c50] font-semibold" />

            <x-text-input id="email"
                class="block mt-2 w-full rounded-xl border-[#e9cdd0] bg-[#fffafa] focus:border-[#d99ca7] focus:ring-[#d99ca7]"
                type="email"
                name="email"
                :value="old('email')"
                required autocomplete="username"
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
                required autocomplete="new-password"
                placeholder="Create a password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-5">
            <x-input-label for="password_confirmation" value="Confirm password"
                class="text-[#614c50] font-semibold" />

            <x-text-input id="password_confirmation"
                class="block mt-2 w-full rounded-xl border-[#e9cdd0] bg-[#fffafa] focus:border-[#d99ca7] focus:ring-[#d99ca7]"
                type="password"
                name="password_confirmation"
                required autocomplete="new-password"
                placeholder="Confirm your password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Register Button -->
        <button type="submit"
            class="w-full mt-7 py-3 rounded-xl bg-[#d99ca7] text-white font-semibold hover:bg-[#c78391] transition duration-200 shadow-sm">
            Create account
        </button>

        <!-- Login Link -->
        <p class="text-center text-sm text-[#806b70] mt-6">
            Already have an account?
            <a href="{{ route('login') }}"
               class="font-semibold text-[#c47d8c] hover:underline">
                Log in
            </a>
        </p>

    </form>
</x-guest-layout>
