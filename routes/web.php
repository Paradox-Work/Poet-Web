<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/u/{user:username}', [ProfileController::class, 'index'])
    ->name('profile');

Route::post('/posts', [\App\Http\Controllers\PostController::class, 'store'])
    ->middleware('auth')
    ->name('post.create');

Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
    ->middleware('auth')
    ->name('posts.comments.store');

Route::post('/u/{user:username}/follow', [ProfileController::class, 'toggleFollow'])
    ->middleware('auth')
    ->name('profile.follow');

require __DIR__.'/auth.php';
