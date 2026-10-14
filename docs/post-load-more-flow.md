# Infinite Post Loading Flow

## Overview

Poet-Web incrementally loads publications for both:

- Home/Explore;
- group publication feeds.

The first page is delivered through Inertia. Later pages are requested as JSON when the user approaches the end of the visible feed.

## Shared backend query

Both feeds start from:

```php
Post::postsForTimeline($userId)
```

This means pagination never bypasses publication visibility.

### Home

`HomeController` applies:

- feed type;
- source;
- genre;

before paginating by 10.

Guests are allowed only the non-personalized `for_you` source.

### Group profile

`GroupController::profile()` limits the shared query to:

```text
posts.group_id = current group
```

and only serves the collection when:

```text
group visibility = public
OR
viewer is approved member
```

This same permission is checked for JSON load-more requests.

## Frontend state

`PostList.vue` keeps local reactive state for:

```text
posts
nextPageUrl
loadedBeyondFirstPage
```

The complete feed is not persisted with Inertia `useRemember()`, reducing stale-data restoration after redirects.

## Loading another page

The flow is:

```text
IntersectionObserver reaches marker
→ nextPageUrl exists?
→ request with Accept: application/json
→ backend applies same visibility/filter query
→ receive next PostResource page
→ remove duplicate IDs
→ append unique publications
→ update nextPageUrl
```

## Duplicate protection

Two safeguards are used:

- `loadingMore` prevents parallel load-more requests;
- returned IDs are filtered against IDs already present locally.

## First-page synchronization

After create/edit actions cause Inertia to return a refreshed first page, `PostList.vue`:

1. updates matching publications already in local state;
2. finds new IDs;
3. prepends newly created publications.

This allows the feed to stay reactive without replacing all already-loaded later pages.

## Deletion synchronization

After a successful delete:

```text
PostItem emits deleted(post.id)
→ PostList removes that ID
```

This prevents a soft-deleted record from remaining visible in stale frontend state.

## Main files

```text
app/Http/Controllers/HomeController.php
app/Http/Controllers/GroupController.php
app/Models/Post.php
app/Http/Resources/PostResource.php
resources/js/Components/app/PostList.vue
resources/js/Components/app/PostItem.vue
```
