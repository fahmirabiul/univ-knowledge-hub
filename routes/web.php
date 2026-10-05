<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\SsoAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Landing Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/catalog', function () {
    return redirect()->route('home');
})->name('catalog');

/*
|--------------------------------------------------------------------------
| SSO OAuth2 Authentication Routes (univ-iam Consumer)
|--------------------------------------------------------------------------
*/
Route::prefix('auth/sso')->name('auth.sso.')->group(function () {
    Route::get('/redirect', [SsoAuthController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [SsoAuthController::class, 'callback'])->name('callback');
});

Route::post('/logout', [SsoAuthController::class, 'logout'])->name('auth.logout');
