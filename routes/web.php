<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;


Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth'])->name('dashboard');

Route::get('/u/{user:username}', [ProfileController::class, 'index'])
    ->name('profile');

Route::middleware('auth')->group(function () {
    
    Route::post(
        '/posts', 
        [\App\Http\Controllers\PostController::class, 'store']
    )->name('post.create');

    Route::put(
        '/posts/{post}',
        [\App\Http\Controllers\PostController::class, 'update']
    )->name('post.update');

    Route::delete(
        '/posts/{post}',
        [\App\Http\Controllers\PostController::class, 'destroy']
    )->name('post.destroy');
    
    Route::post(
        '/profile/update-images',
         [ProfileController::class, 'updateImage']
    )->name('profile.updateImages');

//   Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch(
        '/profile',
         [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
         [ProfileController::class, 'destroy']
    )->name('profile.destroy');
});

require __DIR__.'/auth.php';
