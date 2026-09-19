<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PieceController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\SketchController;
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
    Route::prefix('/books')->group(function() {
        Route::get('/', [BookController::class, 'index'])->name('books');
        Route::get('/add', [BookController::class, 'add'])->name('add_book');
        Route::post('/add', [BookController::class, 'create'])->name('create_book');
        Route::get('/{id}', [BookController::class, 'show'])->name('view_book');
        Route::get('/{id}/edit', [BookController::class, 'edit'])->name('edit_book');
        Route::post('{id}/edit', [BookController::class, 'update']);
        Route::get('/{id}/delete', [BookController::class, 'delete']);
    });
    Route::prefix('/sketches')->group(function() {
        Route::get('/', [SketchController::class, 'index'])->name('sketches');
        Route::get('/add', [SketchController::class, 'add'])->name('add_sketch');
        Route::post('/add', [SketchController::class, 'create'])->name('create_sketch');
        Route::get('/{id}', [SketchController::class, 'show'])->name('view_sketch');
        Route::get('/{id}/edit', [SketchController::class, 'edit'])->name('edit_sketch');
        Route::post('{id}/edit', [SketchController::class, 'update']);
        Route::get('/{id}/delete', [SketchController::class, 'delete']);
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
        Route::post('/rebuild-db', [APIDashboardController::class, 'rebuildDB']);

        Route::prefix('/pieces')->group(function() {
            Route::get('/{id}/generate-thumbnails', [APIPieceController::class, 'generateThumbnails']);
            Route::post('/{id}/toggle-active', [APIPieceController::class, 'toggleActive']);
        });

        Route::get('/push-changes', [APIDashboardController::class, 'pushChanges']);
        Route::get('/revert-changes', [APIDashboardController::class, 'revertChanges']);
        Route::get('/generate-json', [APIDashboardController::class, 'generateJSON']);
        Route::get('/generate-thumbnails', [APIDashboardController::class, 'generateThumbnails']);
    });

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
