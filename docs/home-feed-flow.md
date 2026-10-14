# Home Feed Flow

## Overview

The Home route is public:

```text
GET /
route: dashboard
```

Both guests and authenticated users can read visible publications.

The controller uses `Post::postsForTimeline(?int $userId)` as the visibility base, then applies feed/source/genre filters.

## Visibility base

```text
published standalone post
→ visible to everyone

published group post
→ visible only to approved group members

draft
→ excluded
```

For a guest, `$userId` is null, so group publications are excluded.

## Feed filters

The Home controller supports:

### Content type

```text
feed=poems
feed=posts
feed=all
```

Invalid values fall back to `poems`.

### Source

```text
source=for_you
source=following
source=groups
source=mine
```

Invalid values fall back to `for_you`.

Guests are always forced to `for_you`, because the other source modes depend on an authenticated identity.

### Genre

A genre may be supplied for poem browsing. The value is trimmed and limited to 50 characters.

Genre filtering uses `whereJsonContains('posts.poem_genres', $genre)`.

## Authenticated source behavior

```text
for_you
→ all publications already visible through postsForTimeline()

following
→ visible publications authored by followed users

groups
→ visible group publications

mine
→ visible publications authored by the current user
```

The visibility query still runs first, so source filters do not bypass group access rules.

## Guest behavior

A guest receives a read-only Explore experience:

```text
source = for_you
standalone public publications only
no private group publications
no personal source tabs
```

Vue reduces the source selector to a single `Explore` option for guests.

State-changing controls continue to require authentication.

## Pagination

Results use:

```php
paginate(10)->withQueryString()
```

When the request asks for JSON, the controller returns the paginated resource collection directly. This supports the existing infinite-scroll behavior.

## Genre counts

Genre counts are calculated from the currently selected source before the final `feed`/genre result filtering.

Only poem genres are counted.

## Security property

Home is public, but that does not make group posts public.

`Post::postsForTimeline()` remains the security boundary for collection visibility, and group membership is checked on the backend.

## Main files

```text
routes/web.php
app/Http/Controllers/HomeController.php
app/Models/Post.php
app/Http/Resources/PostResource.php
resources/js/Pages/Home.vue
resources/js/Components/app/PostList.vue
```
