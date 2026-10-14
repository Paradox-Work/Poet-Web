# Bookmarks / Reading List

## Overview

Authenticated users can save visible publications into a personal reading list.

Routes:

```text
GET  /saved
POST /posts/{post}/bookmark
```

## Bookmarking

Before changing bookmark state, the backend checks:

```php
$post->isVisibleTo($request->user()->id)
```

Therefore a private-group publication cannot be bookmarked by an outsider through a guessed post ID.

The pair:

```text
user_id + post_id
```

is unique in the database.

The endpoint behaves as a toggle:

```text
bookmark exists
→ delete it

bookmark absent
→ create it
```

JSON responses return:

```json
{
  "bookmarked": true
}
```

or false after removal.

## Saved page

`BookmarkController::index()` starts from the same visible-publication query used elsewhere and narrows it to posts bookmarked by the current user.

The page supports normal HTML/Inertia rendering and JSON pagination for the shared `PostList` load-more flow.

If previously saved private content is no longer visible, it is not returned.

## Frontend

`PostItem.vue` exposes a Save action for authenticated users.

Its visual state comes from:

```text
current_user_has_bookmark
```

provided by `PostResource`.

Guests do not see a bookmark action.

## Main files

```text
database/migrations/2026_10_14_000006_create_bookmarks_table.php
app/Models/Bookmark.php
app/Models/Post.php
app/Models/User.php
app/Http/Controllers/BookmarkController.php
app/Http/Resources/PostResource.php
resources/js/Pages/Saved/Index.vue
resources/js/Components/app/PostItem.vue
routes/web.php
```
