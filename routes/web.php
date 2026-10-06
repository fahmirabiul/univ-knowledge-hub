<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\SsoAuthController;
use App\Http\Controllers\Publication\DoiFetchController;
use App\Http\Controllers\Publication\PublicationController;
use App\Models\Publication;
use App\Services\Publication\PublicationStorageService;
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

Route::get('/publications/{publication}/download', function (Publication $publication, PublicationStorageService $storageService) {
    return $storageService->downloadFile($publication);
})->name('publications.download')->middleware('signed');

/*
|--------------------------------------------------------------------------
| SSO OAuth2 Authentication Routes (univ-iam Consumer)
|--------------------------------------------------------------------------
*/
Route::get('/login', [SsoAuthController::class, 'redirect'])->name('login');

Route::prefix('auth/sso')->name('auth.sso.')->group(function () {
    Route::get('/redirect', [SsoAuthController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [SsoAuthController::class, 'callback'])->name('callback');
});

Route::post('/logout', [SsoAuthController::class, 'logout'])->name('auth.logout');

/*
|--------------------------------------------------------------------------
| Dosen & Researcher Workspace Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:dosen,admin_lppm', 'active'])->group(function () {
    Route::get('/publications/doi/fetch', [DoiFetchController::class, 'fetch'])->name('publications.doi.fetch');
    Route::resource('publications', PublicationController::class);
});
