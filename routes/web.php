<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;



Route::get('oauth/{provider}', [\App\Http\Controllers\OauthController::class, 'redirectToProvider'])->where('provider', 'google|github')->name('oauth.redirect');

Route::get('oauth/{provider}/callback', [\App\Http\Controllers\OauthController::class, 'handleProviderCallback'])->where('provider', 'google|github')->name('oauth.callback');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');
});
