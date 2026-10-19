<?php

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DraftCollaboratorController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupImageController;
use App\Http\Controllers\GroupInvitationController;
use App\Http\Controllers\GroupMembershipController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PoemController;
use App\Http\Controllers\PostAttachmentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostInteractionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])
    ->name('dashboard');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])
    ->name('sitemap');

Route::get('/about', fn () => Inertia::render('About'))
    ->name('about');

Route::get('/privacy', fn () => Inertia::render('Legal', ['page' => 'privacy']))
    ->name('privacy');

Route::get('/terms', fn () => Inertia::render('Legal', ['page' => 'terms']))
    ->name('terms');

Route::get('/community-guidelines', fn () => Inertia::render('Legal', ['page' => 'community']))
    ->name('community');

Route::get('/copyright', fn () => Inertia::render('Legal', ['page' => 'copyright']))
    ->name('copyright');

Route::get('/u/{user:username}', [ProfileController::class, 'index'])
    ->name('profile');

Route::get('/g/{group:slug}', [GroupController::class, 'profile'])
    ->name('group.profile');

Route::get('/groups', [GroupController::class, 'index'])
    ->name('group.index');

Route::get('/search/{search?}', [SearchController::class, 'search'])
    ->name('search');

Route::get(
    '/posts/attachments/{attachment}',
    [PostAttachmentController::class, 'viewAttachment']
)->name('post.attachment.view');

Route::get(
    '/posts/attachments/{attachment}/download',
    [PostAttachmentController::class, 'downloadAttachment']
)->name('post.download');

Route::get('/posts/{post}', [PostController::class, 'view'])
    ->name('post.view');

Route::middleware('auth')->group(function () {
    Route::get('/saved', [BookmarkController::class, 'index'])
        ->name('saved.index');

    Route::post('/posts/{post}/bookmark', [BookmarkController::class, 'toggle'])
        ->name('post.bookmark');

    Route::get('/messages/{conversation?}', [MessageController::class, 'index'])
        ->name('messages.show');

    Route::post('/messages/start/{user}', [MessageController::class, 'start'])
        ->middleware('throttle:10,1')
        ->name('messages.start');

    Route::post('/messages/{conversation}', [MessageController::class, 'store'])
        ->middleware('throttle:60,1')
        ->name('messages.store');

    Route::get('/messages/{conversation}/items', [MessageController::class, 'messages'])
        ->name('messages.items');

    Route::post('/reports', [ReportController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('reports.store');

    Route::get('/admin/moderation', [ModerationController::class, 'index'])
        ->name('admin.moderation');

    Route::post('/admin/reports/{report}/resolve', [ModerationController::class, 'resolve'])
        ->name('admin.reports.resolve');

    Route::post('/drafts/{post}/collaborators', [DraftCollaboratorController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('draft.collaborators.store');

    Route::delete('/drafts/{post}/collaborators/{user}', [DraftCollaboratorController::class, 'destroy'])
        ->name('draft.collaborators.destroy');

    Route::post('/posts', [PostController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('post.create');

    Route::get('/write/poem', [PoemController::class, 'writePoem'])
        ->name('poem.write');

    Route::get('/drafts', [DraftController::class, 'drafts'])
        ->name('draft.index');

    Route::get('/drafts/latest', [DraftController::class, 'latestDraft'])
        ->name('draft.latest');

    Route::post('/drafts', [DraftController::class, 'storeDraft'])
        ->middleware('throttle:90,1')
        ->name('draft.store');

    Route::put('/drafts/{post}', [DraftController::class, 'updateDraft'])
        ->middleware('throttle:90,1')
        ->name('draft.update');

    Route::put('/posts/{post}', [PostController::class, 'update'])
        ->middleware('throttle:30,1')
        ->name('post.update');

    Route::delete('/posts/{post}', [PostController::class, 'destroy'])
        ->name('post.destroy');

    Route::post(
        '/profile/update-images',
        [ProfileController::class, 'updateImage']
    )->middleware('throttle:10,1')
        ->name('profile.updateImages');

    Route::post(
        '/posts/{post}/reaction',
        [PostInteractionController::class, 'postReaction']
    )
        ->middleware('throttle:90,1')
        ->name('post.reaction');

    Route::post('/posts/{post}/pin', [PostInteractionController::class, 'pinUnpin'])
        ->name('post.pin');

    Route::post('/posts/{post}/comments', [CommentController::class, 'createComment'])
        ->middleware('throttle:30,1')
        ->name('post.comment.create');

    Route::put('/comments/{comment}', [CommentController::class, 'updateComment'])
        ->name('post.comment.update');

    Route::delete('/comments/{comment}', [CommentController::class, 'deleteComment'])
        ->name('post.comment.delete');

    Route::post('/comments/{comment}/reaction', [CommentController::class, 'commentReaction'])
        ->middleware('throttle:90,1')
        ->name('post.comment.reaction');

    Route::post('/groups', [GroupController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('group.create');

    Route::put('/groups/{group:slug}', [GroupController::class, 'update'])
        ->name('group.update');

    Route::post('/groups/{group:slug}/images', [GroupImageController::class, 'updateImage'])
        ->middleware('throttle:10,1')
        ->name('group.updateImages');

    Route::post('/groups/{group:slug}/invitations', [GroupInvitationController::class, 'inviteUsers'])
        ->middleware('throttle:20,1')
        ->name('group.inviteUsers');

    Route::get('/groups/invitations/{token}', [GroupInvitationController::class, 'showInvitation'])
        ->name('group.invitation');

    Route::post('/groups/invitations/{token}/accept', [GroupInvitationController::class, 'approveInvitation'])
        ->name('group.approveInvitation');

    Route::post('/groups/invitations/{token}/decline', [GroupInvitationController::class, 'declineInvitation'])
        ->name('group.declineInvitation');

    Route::post('/groups/{group:slug}/join', [GroupMembershipController::class, 'join'])
        ->middleware('throttle:10,1')
        ->name('group.join');

    Route::post('/groups/{group:slug}/requests/resolve', [GroupMembershipController::class, 'resolveJoinRequest'])
        ->name('group.resolveJoinRequest');

    Route::post('/groups/{group:slug}/members/role', [GroupMembershipController::class, 'changeRole'])
        ->name('group.changeRole');

    Route::delete('/groups/{group:slug}/leave', [GroupMembershipController::class, 'leave'])
        ->middleware('throttle:10,1')
        ->name('group.leave');

    Route::delete('/groups/{group:slug}/members', [GroupMembershipController::class, 'removeUser'])
        ->name('group.removeUser');

    Route::post('/users/{user}/follow', [UserController::class, 'follow'])
        ->middleware('throttle:30,1')
        ->name('user.follow');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.readAll');
});

require __DIR__.'/auth.php';
