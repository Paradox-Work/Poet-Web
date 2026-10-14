<?php

use App\Http\Controllers\CommentController;
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
use App\Http\Controllers\UserController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CollaborationController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])
    ->name('dashboard');

Route::get('/about', fn () => Inertia::render('About'))
    ->name('about');

Route::get('/legal/{page}', function (string $page) {
    abort_unless(
        in_array($page, ['privacy', 'terms', 'guidelines', 'copyright'], true),
        404
    );

    return Inertia::render('Legal/Show', ['page' => $page]);
})->name('legal.show');

Route::get('/sitemap.xml', SitemapController::class)
    ->name('sitemap');

Route::get('/robots.txt', function () {
    $body = implode("\n", [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin',
        'Disallow: /messages',
        'Disallow: /library',
        'Disallow: /drafts',
        'Disallow: /collaborations',
        'Disallow: /report',
        'Sitemap: ' . route('sitemap'),
        '',
    ]);

    return response($body, 200, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
})->name('robots');

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

Route::middleware(['auth', 'not_suspended'])->group(function () {
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
        ->name('post.update');

    Route::delete('/posts/{post}', [PostController::class, 'destroy'])
        ->name('post.destroy');

    Route::post(
        '/profile/update-images',
        [ProfileController::class, 'updateImage']
    )->name('profile.updateImages');

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
        ->name('group.create');

    Route::put('/groups/{group:slug}', [GroupController::class, 'update'])
        ->name('group.update');

    Route::post('/groups/{group:slug}/images', [GroupImageController::class, 'updateImage'])
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

    Route::get('/library', [LibraryController::class, 'index'])
        ->name('library.index');
    Route::post('/posts/{post}/bookmark', [LibraryController::class, 'toggleBookmark'])
        ->name('bookmarks.toggle');
    Route::post('/reading-lists', [LibraryController::class, 'storeList'])
        ->name('reading-lists.store');
    Route::delete('/reading-lists/{readingList}', [LibraryController::class, 'destroyList'])
        ->name('reading-lists.destroy');
    Route::post('/reading-lists/{readingList}/posts/{post}', [LibraryController::class, 'addToList'])
        ->name('reading-lists.posts.add');
    Route::delete('/reading-lists/{readingList}/posts/{post}', [LibraryController::class, 'removeFromList'])
        ->name('reading-lists.posts.remove');

    Route::get('/messages', [MessageController::class, 'index'])
        ->name('messages.index');
    Route::get('/messages/{conversation}', [MessageController::class, 'index'])
        ->name('messages.show');
    Route::post('/messages/start/{user}', [MessageController::class, 'start'])
        ->name('messages.start');
    Route::post('/messages/{conversation}', [MessageController::class, 'send'])
        ->middleware('throttle:60,1')
        ->name('messages.send');
    Route::get('/messages/{conversation}/poll', [MessageController::class, 'poll'])
        ->middleware('throttle:120,1')
        ->name('messages.poll');

    Route::get('/report/{type}/{id}', [ReportController::class, 'create'])
        ->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('reports.store');

    Route::get('/collaborations', [CollaborationController::class, 'index'])
        ->name('collaborations.index');
    Route::get('/posts/{post}/collaborators', [CollaborationController::class, 'manage'])
        ->name('collaborations.manage');
    Route::post('/posts/{post}/collaborators', [CollaborationController::class, 'invite'])
        ->name('collaborations.invite');
    Route::post('/collaborations/{collaborator}/accept', [CollaborationController::class, 'accept'])
        ->name('collaborations.accept');
    Route::post('/collaborations/{collaborator}/decline', [CollaborationController::class, 'decline'])
        ->name('collaborations.decline');
    Route::delete('/collaborations/{collaborator}', [CollaborationController::class, 'remove'])
        ->name('collaborations.remove');

    Route::get('/admin', [AdminController::class, 'index'])
        ->name('admin.index');
    Route::post('/admin/reports/{report}/resolve', [AdminController::class, 'resolve'])
        ->name('admin.reports.resolve');
    Route::post('/admin/users/{user}/unsuspend', [AdminController::class, 'unsuspend'])
        ->name('admin.users.unsuspend');
});

require __DIR__.'/auth.php';
