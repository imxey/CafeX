@extends('layouts.without-navbar')

@section('content')
<div class="bg-white p-[2rem] gap-10 w-[80%] min-h-[80vh] rounded-xl flex">
    <!-- Left Section -->
    <div class="w-1/2 flex flex-col justify-center items-center">
        <div class="w-full justify-between flex">
            <a href="{{ route('oauth.google') }}" class="w-[46%] justify-center select-none cursor-pointer hover:bg-[rgba(74,74,74,0.6)] bg-[rgba(74,74,74,0.4)] p-3 rounded-xl flex gap-3 font-semibold fs-3">
    <img class="w-6 h-6" src="images/google.png" alt="google">
    Sign In with Google
</a>

            <button class="w-[46%] justify-center select-none cursor-pointer hover:bg-[rgba(74,74,74,0.6)] bg-[rgba(74,74,74,0.4)] p-3 rounded-xl flex gap-3 font-semibold fs-3">
                <img class="w-6 h-6" src="images/github.png" alt="google">
                Sign In with Github
            </button>
        </div>
        <div class="flex items-center gap-4 my-4 w-full">
            <hr class="w-1/2 text-[#4A4A4A]">
            <span class="text-[#4A4A4A] text-sm">or</span>
            <hr class="w-1/2 text-[#4A4A4A]">
        </div>

        <!-- Form -->
        <form action="" class="w-full flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                <label class="font-semibold fs-3" for="email">Email</label>
                <input class="focus:outline-none border-none bg-[rgba(74,74,74,0.4)] p-4 rounded-xl h-12" type="email" name="email" id="email">
            </div>
            <div class="flex flex-col gap-2">
                <label class="font-semibold fs-3" for="password">Password</label>
                <input class="focus:outline-none border-none bg-[rgba(74,74,74,0.4)] p-4 rounded-xl h-12" type="password" name="password" id="password">
            </div>
            <div class="w-full flex justify-center mt-4">
                <button class=" w-1/2 justify-center select-none cursor-pointer hover:bg-[#F18A16] bg-[#EA9330] p-3 rounded-xl font-semibold fs-3" type="submit">Sign In</button>
            </div>
        </form>
        <p class="fs-3 mt-12">Don't have an account? <a class="text-[#F18A16] font-semibold" href="/signup">Sign Up</a></p>
    </div>

    <!-- Right Section -->
    <div class="bg-[#4A4A4A] w-1/2 min-h-[inherit] rounded-xl justify-center items-center flex"><img class="w-[250px] h-[200px]" src="images/coffee.png" alt="coffee"></div>
</div>
@endsection
