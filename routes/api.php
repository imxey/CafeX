<?php

use App\Http\Controllers\RecommendationAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/recommendation', [RecommendationAPI::class, 'calculate']);
