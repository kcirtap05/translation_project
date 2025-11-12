<?php

use App\Http\Controllers\Api\LocaleController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TranslationController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('translations')->group(function () {
        Route::get('/', [TranslationController::class, 'grid']);
        Route::post('/', [TranslationController::class, 'create']);
        Route::put('{uuid}', [TranslationController::class, 'update']);
        Route::delete('{uuid}', [TranslationController::class, 'destroy']);
        Route::get('search/key', [TranslationController::class, 'searchByKey']);
        Route::get('search/tags', [TranslationController::class, 'searchByTags']);
        Route::get('search/x', [TranslationController::class, 'getX']);
    }); 
    
    Route::prefix('locales')->group(function () {
        Route::get('/', [LocaleController::class, 'grid']);
        Route::post('/', [LocaleController::class, 'create']);
        Route::put('{uuid}', [LocaleController::class, 'update']);
        Route::delete('{uuid}', [LocaleController::class, 'destroy']);
    });
    
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'grid']);
        Route::post('/', [TagController::class, 'create']);
        Route::put('{uuid}', [TagController::class, 'update']);
        Route::delete('{uuid}', [TagController::class, 'destroy']);
    });
});


