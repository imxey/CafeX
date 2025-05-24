<x-guest-layout>
    <x-authentication-card>
        <x-slot name="left">
            <div class="h-full bg-[#4A4A4A] w-full flex items-center justify-center rounded-xl">
                <img class="w-[250px] h-[200px]" src="images/coffee.png" alt="coffee">
            </div>
        </x-slot>
        <x-slot name="right">
            <div class="w-full justify-between flex">
                <a href="{{ route('oauth.redirect', ['provider' => 'google']) }}"
                    class="w-[46%] justify-center select-none cursor-pointer hover:bg-[rgba(74,74,74,0.6)] bg-[rgba(74,74,74,0.4)] p-3 rounded-xl flex gap-3 font-semibold fs-3">
                    <img class="w-6 h-6" src="images/google.png" alt="google">
                    Sign Up with Google
                </a>
                <a href="{{ route('oauth.redirect', ['provider' => 'github']) }}"
                    class="w-[46%] justify-center select-none cursor-pointer hover:bg-[rgba(74,74,74,0.6)] bg-[rgba(74,74,74,0.4)] p-3 rounded-xl flex gap-3 font-semibold fs-3">
                    <img class="w-6 h-6" src="images/github.png" alt="google">
                    Sign Up with Github
                </a>
            </div>
            <div class="flex items-center gap-4 my-4 w-full">
                <hr class="w-1/2 text-[#4A4A4A]">
                <span class="text-[#4A4A4A] text-sm">or</span>
                <hr class="w-1/2 text-[#4A4A4A]">
            </div>
            <x-validation-errors class="mb-4" />

            <form method="POST" action="{{ route('register') }}" class="w-full flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-2">
                    <x-label for="name" value="{{ __('Name') }}" />
                    <x-input id="name" type="name" name="name" :value="old('name')" required autofocus
                        autocomplete="username" />
                </div>
                <div class="flex flex-col gap-2">
                    <x-label for="email" value="{{ __('Email') }}" />
                    <x-input id="email" type="email" name="email" :value="old('email')" required autocomplete="email" />
                </div>

                <div class="mt-4">
                    <x-label for="password" value="{{ __('password') }}" />
                    <x-input id="password" class="block mt-1 w-full" type="password" name="password"
                        :value="old('password')" required autocomplete="password" />
                </div>

                <div class="mt-4">
                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                    <x-input id="password_confirmation" class="block mt-1 w-full" type="password_confirmation"
                        name="password_confirmation" required autocomplete="password_confirmation" />
                </div>
                <div class="text-sm text-gray-600">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}" class="ml-1 no-underline font-semibold text-sm hover:text-[#F18A16] text-[#EA9330]">
                        {{ __('Sign In here') }}
                    </a>
                </div>
                <div class="w-full justify-end flex">

                    <x-button class="w-1/3 ">
                        {{ __('Sign Up') }}
                    </x-button>
                </div>
            </form>
        </x-slot>


    </x-authentication-card>
</x-guest-layout>
