<x-guest-layout>
    <x-authentication-card>
        <x-slot name="left">
            <h1 class="text-4xl font-bold mb-10">Welcome Back</h1>
            <div class="w-full justify-between flex">
                <a href="{{ route('oauth.redirect', ['provider' => 'google']) }}"
                    class="w-[46%] justify-center select-none cursor-pointer hover:bg-[rgba(74,74,74,0.6)] bg-[rgba(74,74,74,0.4)] p-3 rounded-xl flex gap-3 font-semibold fs-3">
                    <img class="w-6 h-6" src="/images/google.png" alt="google">
                    Sign In with Google
                </a>
                <a href="{{ route('oauth.redirect', ['provider' => 'github']) }}" x
                    class="w-[46%] justify-center select-none cursor-pointer hover:bg-[rgba(74,74,74,0.6)] bg-[rgba(74,74,74,0.4)] p-3 rounded-xl flex gap-3 font-semibold fs-3">
                    <img class="w-6 h-6" src="/images/github.png" alt="google">
                    Sign In with Github
                </a>
            </div>
            <div class="flex items-center gap-4 my-4 w-full">
                <hr class="w-1/2 text-[#4A4A4A]">
                <span class="text-[#4A4A4A] text-sm">or</span>
                <hr class="w-1/2 text-[#4A4A4A]">
            </div>

            @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
            @endsession

            <form method="POST" action="{{ route('login') }}" class="w-full flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-2">
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" type="email" name="email" :value="old('email')" required autofocus
                        autocomplete="username" />
                </div>
                <div class="flex flex-col gap-2">
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                        autocomplete="current-password" />
                </div>
                <div class="text-sm text-gray-600">
                    {{ __('Don\'t have an account?') }}
                    <a href="{{ route('register') }}" class="ml-1 no-underline font-semibold text-sm hover:text-[#F18A16] text-[#EA9330]">
                        {{ __('Sign up here') }}
                    </a>
                </div>
                @if (Route::has('password.request'))
                <a class="no-underline font-semibold text-sm hover:text-[#F18A16] text-[#EA9330] w-max"
                    href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
                @endif
                <div class="w-full flex justify-center mt-4">
                    <x-button class="w-1/2 ">
                        {{ __('Sign In') }}
                    </x-button>
                </div>
            </form>
        </x-slot>
        <x-slot name="right">
            <img class="w-full rounded-2xl" src="images/coffee.png" alt="coffee">
        </x-slot>
        <x-validation-errors class="mb-4" />
    </x-authentication-card>
</x-guest-layout>