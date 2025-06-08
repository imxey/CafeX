<?php

use App\Http\Controllers\History;
use App\Http\Controllers\Preferences;
use App\Http\Controllers\Recommendation;
use App\Http\Controllers\SaveLocation;
use Illuminate\Support\Facades\Route;


Route::get('oauth/{provider}', [\App\Http\Controllers\OauthController::class, 'redirectToProvider'])->where('provider', 'google|github')->name('oauth.redirect');

Route::get('oauth/{provider}/callback', [\App\Http\Controllers\OauthController::class, 'handleProviderCallback'])->where('provider', 'google|github')->name('oauth.callback');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'isEmptyPreferences',
])->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/preferences', action: [Preferences::class, 'index'])->name('preferences')->withoutMiddleware('isEmptyPreferences');
    Route::post('/preferences', action: [Preferences::class, 'store'])->name('preferences.store')->withoutMiddleware('isEmptyPreferences');

    Route::get('/recommendation', [Recommendation::class, 'getRecommendations'])->name('recommendation');

    Route::post('/save-location', [SaveLocation::class, 'saveLocation'])->name('save-location');

    Route::get('/history', [History::class, 'index'])->name('history');
    Route::post('/history/recommendation-details', [History::class, 'getHistoricRecommendationDetails'])->name('history.recommendation.details');

    Route::get('/about', function () {
        return view('about');
    })->name('about');

});
