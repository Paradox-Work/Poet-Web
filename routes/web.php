<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;


Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth'])->name('dashboard');

Route::get('/u/{user:username}', [ProfileController::class, 'index'])
    ->name('profile');

Route::get(
    '/g/{group:slug}',
    [GroupController::class, 'profile']
)->name('group.profile');

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

    Route::get(
        '/posts/attachments/{attachment}/download',
        [\App\Http\Controllers\PostController::class, 'downloadAttachment']
    )->name('post.download');
    
    Route::post(
        '/profile/update-images',
         [ProfileController::class, 'updateImage']
    )->name('profile.updateImages');

    Route::post(
        '/posts/{post}/reaction',
        [\App\Http\Controllers\PostController::class, 'postReaction']
    )->name('post.reaction');

    Route::post(
        '/posts/{post}/comments',
        [\App\Http\Controllers\PostController::class, 'createComment']
    )->name('post.comment.create');
    
    Route::put(
        '/comments/{comment}',
        [\App\Http\Controllers\PostController::class, 'updateComment']
    )->name('post.comment.update');

    Route::delete(
        '/comments/{comment}',
        [\App\Http\Controllers\PostController::class, 'deleteComment']
    )->name('post.comment.delete');
    
    Route::post(
        '/groups',
        [GroupController::class, 'store']
    )->name('group.create');

    Route::post(
        '/groups/{group:slug}/images',
        [GroupController::class, 'updateImage']
    )->name('group.updateImages');
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
