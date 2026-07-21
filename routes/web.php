<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PieceController;
use App\Http\Controllers\MediumController;
use App\Http\Controllers\CollectionController;

use App\Http\Controllers\API\PieceController as APIPieceController;
use App\Http\Controllers\API\DashboardController as APIDashboardController;

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

// Route::get('/phpinfo', function () {
//     phpinfo();
// });

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('/pieces')->group(function () {
        Route::get('/', [PieceController::class, 'index'])->name('pieces');
        Route::get('/add', [PieceController::class, 'add'])->name('add_piece');
        Route::post('/add', [PieceController::class, 'create'])->name('create_piece');
        Route::get('/{id}', [PieceController::class, 'show'])->name('view_piece');
        Route::get('/{id}/edit', [PieceController::class, 'edit'])->name('edit_piece');
        Route::post('{id}/edit', [PieceController::class, 'update']);
        Route::get('/{id}/delete', [PieceController::class, 'delete']);
    });
    Route::prefix('/media')->group(function() {
        Route::get('/add', [MediumController::class, 'add'])->name('add_medium');
        Route::post('/add', [MediumController::class, 'create'])->name('create_medium');
        Route::get('/', [MediumController::class, 'index'])->name('media');
        Route::get('/{id}', [MediumController::class, 'show'])->name('view_medium');
        Route::get('/{id}/edit', [MediumController::class, 'edit'])->name('edit_medium');
        Route::post('/{id}/edit', [MediumController::class, 'update']);
        Route::get('/{id}/delete', [MediumController::class, 'delete']);
    });
    Route::prefix('/collections')->group(function() {
        Route::get('/add', [CollectionController::class, 'add'])->name('add_collection');
        Route::post('/add', [CollectionController::class, 'create'])->name('create_collection');
        Route::get('/', [CollectionController::class, 'index'])->name('collections');
        Route::get('/{id}', [CollectionController::class, 'show'])->name('view_collection');
        Route::get('/{id}/edit', [CollectionController::class, 'edit'])->name('edit_collection');
        Route::post('/{id}/edit', [CollectionController::class, 'update']);
        Route::get('/{id}/delete', [CollectionController::class, 'delete']);
    });

    Route::prefix('/api')->group(function() {
        Route::prefix('/pieces')->group(function() {
            Route::post('/create', [APIPieceController::class, 'create']);
            Route::post('/{id}/add-child', [APIPieceController::class, 'addChild']);
            Route::post('/{id}/add-image', [APIPieceController::class, 'addImage']);
            Route::post('/{id}/toggle-active', [APIPieceController::class, 'toggleActive']);
            Route::post('/{id}/update', [APIPieceController::class, 'update']);
            Route::get('/{id}/delete', [APIPieceController::class, 'delete']);
        });

        Route::get('/get-backup', [APIDashboardController::class, 'getBackup']);
        Route::get('/regenerate-backup', [APIDashboardController::class, 'generateBackup']);
    });

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
