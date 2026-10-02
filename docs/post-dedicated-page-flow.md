# Dedicated Post Page Flow

## Overview

Poet-Web provides a dedicated page for each visible post.

Instead of requiring users to find a post inside the timeline or a group feed, a post can now be opened directly through its own route:

```text
GET /posts/{post}
```

Route name:

```text
post.view
```

The dedicated page reuses the existing post components, so the same post content, attachments, reactions, comments, replies, editing controls, and moderation rules remain available.

---

## Main Files

The dedicated post-page flow is implemented through:

```text
app/Http/Controllers/PostController.php
app/Http/Resources/PostResource.php
app/Models/Post.php
resources/js/Components/app/EditDeleteDropdown.vue
resources/js/Components/app/PostItem.vue
resources/js/Components/app/PostModal.vue
resources/js/Components/app/AttachmentPreviewModal.vue
resources/js/Pages/Post/View.vue
routes/web.php
```

Several notification classes also use the dedicated post route:

```text
app/Notifications/PostCreated.php
app/Notifications/CommentCreated.php
app/Notifications/CommentDeleted.php
app/Notifications/ReactionAddedOnPost.php
app/Notifications/ReactionAddedOnComment.php
```

---

## Post View Route

The route is defined inside the authenticated route group:

```php
Route::get(
    '/posts/{post}',
    [PostController::class, 'view']
)->name('post.view');
```

This means the viewer must be authenticated before opening a dedicated post page.

Laravel route-model binding first resolves the requested `Post`.

The controller then applies Poet-Web's own visibility rules before rendering it.

---

## Visibility Protection

A direct post URL must not bypass the normal group privacy rules.

The `view()` method therefore does not simply render the route-bound model.

It re-queries the post through:

```text
Post::postsForTimeline($userId)
```

and then limits the query to the requested post ID.

Conceptually:

```text
requested post
      ↓
postsForTimeline(current user)
      ↓
same visibility rules as feed
      ↓
requested ID still visible?
      ↓
yes → render
no  → 404
```

### Normal Posts

Posts without a group can be opened by authenticated users.

### Group Posts

Posts that belong to a group are only returned when the current user has an approved membership in that group.

Knowing the post ID is therefore not enough to bypass group access.

---

## Controller Flow

```mermaid
flowchart TD
    A[GET /posts/{post}] --> B[Authenticate user]
    B --> C[Resolve route-bound Post]
    C --> D[Read current user ID]
    D --> E[Query Post::postsForTimeline]
    E --> F[Filter by requested post ID]
    F --> G{Post visible to user?}
    G -- No --> H[404 Not Found]
    G -- Yes --> I[Transform with PostResource]
    I --> J[Render Post/View]
```

---

## PostResource

The dedicated page uses the existing:

```text
PostResource
```

rather than creating a second representation of a post.

Because `Post::postsForTimeline()` already loads the required relationships and counts, the resource can return the same information used in feeds, including:

```text
post body
author
group
attachments
reaction count
current-user reaction state
comment count
comment tree
delete permission
```

This keeps the dedicated page consistent with the normal timeline and group feeds.

---

## Dedicated Vue Page

The page component is:

```text
resources/js/Pages/Post/View.vue
```

It is rendered inside:

```text
AuthenticatedLayout
```

and reuses:

```text
PostItem.vue
PostModal.vue
AttachmentPreviewModal.vue
```

The page itself therefore contains very little duplicated post logic.

---

## Reusing PostItem

The main post is displayed with:

```text
PostItem
```

This means the dedicated page inherits the existing behavior for:

```text
rich post body
attachments
likes
comments
nested replies
edit controls
delete controls
group-admin moderation
```

Changes made to the shared `PostItem` interface can therefore affect both feed posts and standalone post pages.

---

## Editing From the Dedicated Page

When `PostItem` emits:

```text
editClick
```

the page stores the selected post and opens the existing:

```text
PostModal
```

The modal continues to use the normal post update route and existing validation.

No separate editing system is required for standalone pages.

---

## Attachment Preview

When an attachment is selected, `PostItem` emits:

```text
attachmentClick
```

The page stores:

```text
post
attachment index
```

and opens the existing:

```text
AttachmentPreviewModal
```

Users can therefore preview post attachments from the dedicated page in the same way as they can from a feed.

---

## Post Menu

`EditDeleteDropdown.vue` now includes two post-specific actions:

```text
Open Post
Copy Post URL
```

These actions are shown for post menus, but not for comment menus.

### Open Post

`Open Post` is an Inertia link to:

```text
post.view
```

with the current post ID.

### Copy Post URL

`Copy Post URL` generates the same route and writes it to the browser clipboard using:

```text
navigator.clipboard.writeText()
```

This gives posts shareable application URLs.

---

## Menu Visibility

Previously, the three-dot menu was only useful when the current user could edit or delete an item.

The menu now remains available for visible posts even when the viewer does not have management permissions, because ordinary viewers may still need:

```text
Open Post
Copy Post URL
```

Comment menus continue to depend on edit/delete permissions.

---

## Notification Integration

The dedicated post route is also used by content-related email notifications.

The following notifications now link directly to the affected post:

```text
PostCreated
CommentCreated
CommentDeleted
ReactionAddedOnPost
ReactionAddedOnComment
```

This replaces the previous behavior where these emails linked only to a group page or the main dashboard.

Conceptually:

```text
email notification
      ↓
View post
      ↓
/posts/{post}
      ↓
exact affected post
```

A `PostDeleted` notification does not link to the removed post because the post is no longer available through normal route-model binding after deletion.

---

## Sharing Flow

```mermaid
flowchart TD
    A[User sees post in feed] --> B[Open three-dot menu]
    B --> C{Action}
    C -->|Open Post| D[Go to /posts/{id}]
    C -->|Copy Post URL| E[Copy dedicated URL]
    E --> F[Share URL]
    F --> G[Recipient opens URL]
    G --> H[Backend applies post visibility rules]
    H --> I{Allowed?}
    I -- No --> J[404 Not Found]
    I -- Yes --> D
```

---

## Relationship With Group Privacy

Dedicated URLs do not make private group content public.

For a group post:

```text
authenticated user
      ↓
approved group member?
      ↓
yes → post is available
no  → post is not returned
```

This is enforced by the backend and does not depend on hiding links in the frontend.

---

## Current Limitation

The dedicated page currently reuses the existing post deletion behavior.

`PostController::destroy()` deletes the post and returns:

```text
back()
```

When deletion is triggered while the user is already on the dedicated page, the redirect behavior should be tested separately because the previous page is the post that has just been soft-deleted.

This does not affect the dedicated view or sharing feature itself, but the post-deletion redirect may require a later UX adjustment.

---

## Result

The completed flow gives each accessible Poet-Web post a dedicated, shareable location while preserving the existing visibility and interaction rules.

```text
feed post
   ↓
open or copy link
   ↓
dedicated post URL
   ↓
backend visibility check
   ↓
PostResource
   ↓
Post/View
   ↓
same post interactions and attachments
```

This also allows notification emails to take users directly to the exact post that caused the notification.
