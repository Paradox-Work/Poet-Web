# Global Search Flow

## Overview

Global search is public.

```text
GET /search/{search?}
route: search
```

Empty search redirects to the dashboard.

The query is trimmed and capped at 100 characters.

## Users

Searches:

```text
name
username
```

Pagination:

```text
12 per page
users_page
```

## Groups

Searches:

```text
name
about
```

Pagination:

```text
12 per page
groups_page
```

Group results include metadata such as visibility, but publication visibility is enforced separately.

## Publications

Publication search begins with:

```php
Post::postsForTimeline($request->user()?->id)
```

It then matches:

```text
body
title
caption
```

If the search starts with `#`, it also checks the JSON `hashtags` field.

Pagination:

```text
20 per page
posts_page
```

## Guest visibility

Guests may find:

- users;
- groups;
- standalone public publications;
- public-group publications.

Guests cannot receive private-group publications because the base timeline query excludes them without approved membership.

## Authenticated visibility

Authenticated users receive:

- all public standalone/group content;
- private-group publications for groups where they are approved members.

Search therefore does not create an alternate path around private-group authorization.

## Independent pagination

```text
users_page
groups_page
posts_page
```

are independent so paging one result type does not move the others.

## Hashtags

A hashtag must be URL encoded when placed in the route because `#` otherwise starts a browser fragment.

The server strips the leading `#` before matching the stored hashtags array.

## Main files

```text
app/Http/Controllers/SearchController.php
app/Models/Post.php
app/Http/Resources/PostResource.php
app/Http/Resources/UserResource.php
app/Http/Resources/GroupResource.php
resources/js/Pages/Search.vue
resources/js/Layouts/AuthenticatedLayout.vue
routes/web.php
```
