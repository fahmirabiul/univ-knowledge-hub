<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\PublicationReviewController;
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

Route::get('/dashboard', function () {
    /** @var \App\Models\User|null $user */
    $user = \Illuminate\Support\Facades\Auth::user();
    if (! $user) {
        return redirect()->route('login');
    }

    if ($user->isAdminLPPM()) {
        return redirect()->route('admin.review.index');
    }

    if ($user->isDosen()) {
        return redirect()->route('publications.index');
    }

    return redirect()->route('home');
})->name('dashboard')->middleware('auth');

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

Route::match(['get', 'post'], '/logout', [SsoAuthController::class, 'logout'])->name('auth.logout');

/*
|--------------------------------------------------------------------------
| Dosen & Researcher Workspace Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:dosen,admin_lppm', 'active'])->group(function () {
    Route::get('/publications/doi/fetch', [DoiFetchController::class, 'fetch'])->name('publications.doi.fetch');
    Route::resource('publications', PublicationController::class);
});

/*
|--------------------------------------------------------------------------
| Admin LPPM Verification Queue Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin_lppm', 'active'])
    ->prefix('admin/review')
    ->name('admin.review.')
    ->group(function () {
        Route::get('/', [PublicationReviewController::class, 'index'])->name('index');
        Route::post('/{publication}/approve', [PublicationReviewController::class, 'approve'])->name('approve');
        Route::post('/{publication}/reject', [PublicationReviewController::class, 'reject'])->name('reject');
        Route::get('/{publication}/audit', [PublicationReviewController::class, 'audit'])->name('audit');
    });
