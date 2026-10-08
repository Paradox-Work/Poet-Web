<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;


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

    Route::get(
        '/write/poem',
        [\App\Http\Controllers\PostController::class, 'writePoem']
    )->name('poem.write');

    Route::get(
        '/drafts',
        [\App\Http\Controllers\PostController::class, 'drafts']
    )->name('draft.index');

    Route::get(
        '/drafts/latest',
        [\App\Http\Controllers\PostController::class, 'latestDraft']
    )->name('draft.latest');

    Route::post(
        '/drafts',
        [\App\Http\Controllers\PostController::class, 'storeDraft']
    )->name('draft.store');

    Route::put(
        '/drafts/{post}',
        [\App\Http\Controllers\PostController::class, 'updateDraft']
    )->name('draft.update');

    Route::put(
        '/posts/{post}',
        [\App\Http\Controllers\PostController::class, 'update']
    )->name('post.update');

    Route::delete(
        '/posts/{post}',
        [\App\Http\Controllers\PostController::class, 'destroy']
    )->name('post.destroy');

    Route::get(
        '/posts/{post}',
        [\App\Http\Controllers\PostController::class, 'view']
    )->name('post.view');

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
        '/posts/{post}/pin',
        [\App\Http\Controllers\PostController::class, 'pinUnpin']
    )->name('post.pin');

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
        '/comments/{comment}/reaction',
        [\App\Http\Controllers\PostController::class, 'commentReaction']
    )->name('post.comment.reaction');
    
    Route::post(
        '/groups',
        [GroupController::class, 'store']
    )->name('group.create');

    Route::put(
        '/groups/{group:slug}',
        [GroupController::class, 'update']
    )->name('group.update');

    Route::post(
        '/groups/{group:slug}/images',
        [GroupController::class, 'updateImage']
    )->name('group.updateImages');

    Route::post(
        '/groups/{group:slug}/invitations',
        [GroupController::class, 'inviteUsers']
    )->name('group.inviteUsers');

    Route::get(
        '/groups/invitations/{token}/accept',
        [GroupController::class, 'approveInvitation']
    )->name('group.approveInvitation');

    Route::post(
        '/groups/{group:slug}/join',
        [GroupController::class, 'join']
    )->name('group.join');

    Route::post(
        '/groups/{group:slug}/requests/resolve',
        [GroupController::class,'resolveJoinRequest']
    )->name('group.resolveJoinRequest');

    Route::post(
        '/groups/{group:slug}/members/role',
        [GroupController::class, 'changeRole']
    )->name('group.changeRole');

    Route::delete(
        '/groups/{group:slug}/members',
        [GroupController::class, 'removeUser']
    )->name('group.removeUser');

    Route::post(
        '/users/{user}/follow',
        [UserController::class, 'follow']
    )->name('user.follow');

    

//   Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch(
        '/profile',
         [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
         [ProfileController::class, 'destroy']
    )->name('profile.destroy');

    Route::get(
        '/search/{search?}',
        [SearchController::class, 'search']
    )->name('search');
});

require __DIR__.'/auth.php';
