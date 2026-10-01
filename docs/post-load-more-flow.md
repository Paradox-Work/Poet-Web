# Post Load More Flow

## Overview

Poet-Web uses automatic incremental loading for the main post feed.

Instead of retrieving every post during the initial page request, Laravel returns a paginated collection.

The first page is delivered through Inertia.

When the user approaches the bottom of the loaded post list, Vue automatically requests the next pagination URL and appends the returned posts to the existing feed.

The general flow is:

```text
Initial request
↓
Laravel pagination
↓
First post page
↓
Inertia Home.vue
↓
PostList.vue
↓
User scrolls down
↓
IntersectionObserver activates
↓
JSON request for next page
↓
Append returned posts
```

---

## Backend Pagination

`HomeController` orders posts from newest to oldest and paginates the result.

```text
Post query
↓
latest()
↓
paginate(10)
```

Only a limited number of posts therefore need to be processed during each request.

Each paginated post still passes through `PostResource`, meaning its user information, attachments, reactions, comments, and constructed comment tree are serialized in the same format as posts from the initial request.

---

## Initial Request

During a normal page request, Laravel renders:

```text
Home.vue
```

through Inertia.

The complete paginator is supplied through the `posts` prop.

It contains approximately:

```text
posts
├── data
├── links
│   ├── first
│   ├── last
│   ├── prev
│   └── next
└── meta
```

`data` contains the posts from the current page.

`links.next` contains the URL required to retrieve the next page.

---

## JSON Pagination Requests

When the frontend explicitly requests JSON, `HomeController` returns the post resource collection directly rather than rendering the complete Inertia page.

Conceptually:

```text
Normal request
→ Home.vue

JSON request
→ posts JSON only
```

This prevents unrelated page information from being transferred every time additional posts are loaded.

---

## Local Feed State

`PostList.vue` stores the currently visible posts inside:

```text
allPosts
```

Initially:

```text
allPosts = posts.data
```

The next page URL is stored separately:

```text
nextPageUrl = posts.links.next
```

When another page is loaded, the new posts are appended rather than replacing existing posts.

Example:

```text
Initial:
1 2 3 4 5 6 7 8 9 10

Load page 2:

1 2 3 4 5 6 7 8 9 10
11 12 13 14 15 16 17 18 19 20
```

---

## Intersection Observer

A small invisible element is placed after the rendered posts.

```text
Post
Post
Post
Post

[load-more observer]
```

`IntersectionObserver` watches this element relative to the scrollable post-list container.

When the observer approaches the visible scrolling area, `loadMore()` is called.

A bottom `rootMargin` allows the next page to begin loading shortly before the user reaches the actual end of the feed.

This reduces the chance that the user sees an empty pause while scrolling.

---

## Loading Additional Posts

`loadMore()` first verifies:

```text
a next page exists
AND
another request is not already running
```

If either condition fails, no request is sent.

The frontend then performs a JSON request using the pagination URL returned by Laravel.

The response contains:

```text
data
links
meta
```

New post objects are appended to `allPosts`.

`nextPageUrl` is replaced with the new `links.next` value.

The process can then repeat for the following page.

---

## Duplicate Request Protection

`loadingMore` prevents multiple simultaneous requests for the same pagination URL.

Conceptually:

```text
Observer activates
↓
loadingMore = true
↓
request page 2

Observer activates again
↓
loadingMore already true
↓
request ignored
```

The returned posts are also checked against IDs that have already been loaded before being appended.

This reduces the possibility of duplicate posts appearing in the feed.

---

## End of Feed

Eventually Laravel returns:

```text
links.next = null
```

At that point:

```text
nextPageUrl = null
```

and `loadMore()` stops making requests.

The intersection observer may continue detecting the bottom marker, but no additional network request is performed.

---

## Component Responsibilities

### `HomeController.php`

Responsible for:

```text
querying posts
ordering posts
pagination
returning Inertia on normal requests
returning JSON on load-more requests
```

### `Home.vue`

Responsible for:

```text
receiving the paginator
passing the complete paginator to PostList
```

### `PostList.vue`

Responsible for:

```text
storing currently loaded posts
tracking the next page URL
detecting scrolling near the end
loading additional pages
preventing duplicate requests
appending new posts
rendering PostItem components
```

---

## Result

The main feed no longer needs to load every available post during the initial page request.

Only the first page is rendered initially, while subsequent pages are requested as needed.

This reduces the amount of post, attachment, reaction, and comment data processed during the first page load and provides continuous scrolling through the post feed.