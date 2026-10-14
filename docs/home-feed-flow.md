# Home / Explore Feed Flow

## Overview

The Home route is public:

```text
GET /
route: dashboard
```

Guests and authenticated users receive only publications allowed by the shared visibility query.

## Base visibility

`HomeController` starts from:

```php
Post::postsForTimeline($request->user()?->id)
```

That means:

```text
standalone published publication
→ public

public-group publication
→ public

private-group publication
→ approved member only

draft
→ excluded
```

## Content filter

```text
feed=poems
feed=posts
feed=all
```

Invalid values fall back to:

```text
poems
```

## Source filter

Authenticated users can use:

```text
source=for_you
source=following
source=groups
source=mine
```

Guests are forced to:

```text
source=for_you
```

because personalized source filters require an identity.

`source=groups` does not bypass visibility. It simply narrows the already-visible query to posts with a non-null `group_id`.

## Genre filter

A poem genre may be supplied.

The value is:

- trimmed;
- capped at 50 characters;
- applied with `whereJsonContains` to `poem_genres`.

Genre counts are calculated from the currently selected source before final feed/genre filtering.

## Pagination

```text
10 publications per page
```

Query parameters are preserved.

JSON requests return the resource collection directly for infinite scrolling.

## Guest experience

The frontend presents a read-only Explore mode:

- public standalone poems/posts;
- public-group poems/posts;
- search/navigation;
- login/register for write actions.

State-changing actions still require authentication.

## Localization

The presentation-facing Home UI uses the shared Latvian/English translation layer.

Latvian is the default when no saved browser preference exists.

## Main files

```text
app/Http/Controllers/HomeController.php
app/Models/Post.php
app/Http/Resources/PostResource.php
resources/js/Pages/Home.vue
resources/js/Components/app/PostList.vue
resources/js/i18n.js
routes/web.php
```
