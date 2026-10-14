# Bookmarks and Reading Lists

## Overview

Authenticated users can save visible publications and organize them into personal reading lists.

The library is private to the account owner.

## Bookmarks

Route:

```text
POST /posts/{post}/bookmark
route: bookmarks.toggle
```

Before saving a bookmark:

```text
$post->isVisibleTo(current user)
```

must pass.

Therefore a user cannot bookmark a private-group publication they are not allowed to read.

The database has a unique constraint:

```text
user_id + post_id
```

so one user cannot create duplicate bookmark rows for the same publication.

The post resource exposes:

```text
current_user_has_bookmark
```

for the current authenticated user.

## Library page

Route:

```text
GET /library
route: library.index
```

The page shows:

- paginated bookmarks;
- personal reading lists;
- publications inside each list;
- list creation;
- list deletion;
- add/remove publication actions.

All returned publications are re-filtered through the shared post visibility query.

If a publication later becomes inaccessible, it is not exposed through the library page even if an old bookmark/list relationship exists.

## Reading lists

Tables:

```text
reading_lists
reading_list_post
```

A reading list belongs to one user.

The pivot pair:

```text
reading_list_id + post_id
```

is unique.

List routes verify:

```text
reading_list.user_id = authenticated user ID
```

before mutation.

## Account deletion

Personal bookmarks and reading lists are removed during account-deletion cleanup.

This avoids leaving personal library data behind after the account has been anonymized.

## Main files

```text
app/Models/Bookmark.php
app/Models/ReadingList.php
app/Http/Controllers/LibraryController.php
database/migrations/2026_10_14_000005_create_library_tables.php
resources/js/Pages/Library/Index.vue
resources/js/Components/app/PostItem.vue
tests/Feature/LibraryTest.php
routes/web.php
```
