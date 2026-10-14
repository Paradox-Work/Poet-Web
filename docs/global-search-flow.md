# Global Search Flow

## Overview

Global search is available to guests and authenticated users.

Route:

```text
GET /search/{search?}
route: search
```

An empty search redirects to the dashboard.

The search string is trimmed and limited to 100 characters.

## Search targets

### Users

Matches:

```text
name LIKE %search%
OR username LIKE %search%
```

User results are paginated with the independent page parameter:

```text
users_page
```

### Groups

Matches:

```text
name LIKE %search%
OR about LIKE %search%
```

Group results use:

```text
groups_page
```

### Publications

Publication search starts from:

```php
Post::postsForTimeline($request->user()?->id)
```

Then matches:

```text
body
title
caption
```

When the search starts with `#`, it also checks the JSON `hashtags` field.

Publication results use:

```text
posts_page
```

and are paginated by 20.

## Guest visibility

Guests may search public standalone publications, users, and groups.

Because `postsForTimeline(null)` excludes group publications, guest search cannot reveal member-only group content.

## Authenticated visibility

Authenticated users receive:

- standalone public publications;
- group publications for groups where they are approved members.

Search does not provide a second path around group authorization.

## Independent pagination

Users, groups, and publications use separate pagination parameter names so moving to page 2 of one result type does not unintentionally move the other result types.

This behavior is covered by automated tests.

## Hashtags

A hashtag such as:

```text
#poetry
```

must be URL encoded when placed into the route because `#` otherwise starts a browser fragment.

The server removes the leading `#` before matching the `hashtags` JSON array.

## Main files

```text
routes/web.php
app/Http/Controllers/SearchController.php
app/Models/Post.php
app/Http/Resources/PostResource.php
app/Http/Resources/UserResource.php
app/Http/Resources/GroupResource.php
resources/js/Layouts/AuthenticatedLayout.vue
resources/js/Pages/Search.vue
```
