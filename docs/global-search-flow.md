# Global Search Flow

## Overview

Poet-Web provides an authenticated global search system for:

```text
users
groups
posts
```

The search field is located in the authenticated navigation bar.

When the user enters a search term and presses Enter, Poet-Web navigates to:

```text
/search/{search}
```

The search page then displays matching users, groups, and visible posts.

---

## Main Files

```text
app/Http/Controllers/SearchController.php
resources/js/Layouts/AuthenticatedLayout.vue
resources/js/Pages/Search.vue
resources/js/Components/app/UserListItem.vue
resources/js/Components/app/GroupItem.vue
resources/js/Components/app/PostList.vue
app/Models/Post.php
routes/web.php
```

---

## Search Route

The global search route is defined inside the authenticated route group:

```text
GET /search/{search?}
```

Route name:

```text
search
```

Because the route is inside:

```php
Route::middleware('auth')->group(...)
```

only authenticated users can use global search.

If no search value is provided, `SearchController` redirects the user back to:

```text
dashboard
```

---

## Navigation Search Input

The search input is located in:

```text
resources/js/Layouts/AuthenticatedLayout.vue
```

The component stores the current search value inside:

```text
keywords
```

The initial value is obtained from:

```text
usePage().props.search
```

This means the search term remains visible in the navigation field when the user is viewing search results.

---

## Starting a Search

The user enters text into the navigation search field.

Pressing:

```text
Enter
```

calls:

```text
search()
```

The function:

1. trims whitespace;
2. ignores an empty search value;
3. uses Inertia's router;
4. opens the named `search` route.

Conceptually:

```text
type search term
        ↓
press Enter
        ↓
trim value
        ↓
empty?
├── yes → do nothing
└── no
        ↓
router.get(...)
        ↓
GET /search/{search}
```

Because Inertia navigation is used, the page changes without requiring a traditional full browser reload.

---

## SearchController

Search requests are handled by:

```text
app/Http/Controllers/SearchController.php
```

The controller searches three separate model types:

```text
User
Group
Post
```

Each result type is then transformed for the frontend using Laravel API resources.

---

## User Search

Users are searched by:

```text
name
username
```

The SQL-style matching pattern is:

```text
%search%
```

This allows partial matches.

For example:

```text
search = "will"

matches:
William
Will Endeavour
@will_poetry
```

The query is conceptually:

```text
name LIKE %search%
OR
username LIKE %search%
```

Results are ordered using:

```text
latest()
```

and transformed through:

```text
UserResource
```

---

## Group Search

Groups are searched using:

```text
name
about
```

This allows users to find groups either by their title or by text contained in the group's description/about content.

Conceptually:

```text
group name LIKE %search%
OR
group about LIKE %search%
```

Results are ordered using:

```text
latest()
```

and transformed through:

```text
GroupResource
```

---

## Post Search

Post search differs from users and groups because Poet-Web must preserve post visibility rules.

Instead of starting from:

```php
Post::query()
```

the search uses:

```php
Post::postsForTimeline(
    $request->user()->id
)
```

This ensures the search only includes posts the authenticated user is already allowed to view.

The visible posts are then filtered by:

```text
body LIKE %search%
```

---

## Post Visibility

Using `postsForTimeline()` is important because Poet-Web supports private group content.

Normal posts are visible to authenticated users.

Group posts are only visible when the current user is an approved member of that group.

Therefore:

```text
normal post containing search term
→ visible in search

private group post containing search term
+ viewer is approved member
→ visible in search

private group post containing search term
+ viewer is not approved member
→ not returned
```

Global search therefore follows the same post-access rules used by the home timeline and profile pages.

---

## Post Relationships

`postsForTimeline()` also loads the relationships required by the existing post components.

These include:

```text
user
group
attachments
comments
reactions
reaction counts
```

Because search reuses this query, search results can be rendered using the normal:

```text
PostList.vue
```

and:

```text
PostItem.vue
```

components without implementing separate search-specific post rendering.

---

## Pagination

Post results are paginated using:

```text
20 posts per page
```

The result is converted through:

```text
PostResource::collection(...)
```

The paginator therefore contains information such as:

```text
data
links
meta
```

The full paginator object is passed to:

```text
PostList.vue
```

instead of only passing:

```text
posts.data
```

This allows the existing infinite-scrolling system to continue working.

---

## JSON Search Requests

`PostList.vue` loads additional pages through Axios.

These requests include:

```text
Accept: application/json
```

When `SearchController` detects:

```php
$request->wantsJson()
```

it returns only the paginated post resource collection.

Conceptually:

```text
Search.vue
        ↓
PostList.vue
        ↓
user scrolls
        ↓
nextPageUrl
        ↓
Axios GET
Accept: application/json
        ↓
SearchController
        ↓
return PostResource paginator
        ↓
append new posts
```

This reuses the same infinite-scroll mechanism already used elsewhere in Poet-Web.

---

## Initial Search Page Response

For a normal Inertia page request, `SearchController` returns:

```text
Search.vue
```

with the following props:

```text
posts
search
users
groups
```

The data is structured as:

```text
Search.vue
│
├── search
├── users[]
├── groups[]
└── posts
    ├── data[]
    ├── links
    └── meta
```

---

## Search Results Page

The search page is implemented in:

```text
resources/js/Pages/Search.vue
```

It uses:

```text
AuthenticatedLayout
```

and displays three result sections.

```text
Search Results
│
├── Users
├── Groups
└── Posts
```

The page also shows the current query:

```text
Search results for "..."
```

---

## User Results

User matches are rendered through:

```text
UserListItem.vue
```

This reuses the existing profile navigation and user presentation.

Each result can therefore display:

```text
avatar
name
username
profile link
```

If no users match, the page displays:

```text
No users were found.
```

---

## Group Results

Group matches are rendered through:

```text
GroupItem.vue
```

This reuses the existing group-profile navigation.

Each group can display information such as:

```text
thumbnail
name
description
membership information
```

If no groups match, the page displays:

```text
No groups were found.
```

---

## Post Results

Matching posts are rendered through:

```text
PostList.vue
```

Because the standard post-list component is reused, search results retain existing functionality including:

```text
post display
comments
reactions
attachments
editing
deletion
attachment preview
infinite scrolling
```

If no visible posts match, the page displays:

```text
No posts were found.
```

---

## Complete Search Flow

```text
AuthenticatedLayout
        ↓
enter keywords
        ↓
press Enter
        ↓
router.get(search route)
        ↓
SearchController
        ↓
┌─────────────────────────────┐
│ Search users                │
│ name / username             │
└─────────────────────────────┘
        ↓
┌─────────────────────────────┐
│ Search groups               │
│ name / about                │
└─────────────────────────────┘
        ↓
┌─────────────────────────────┐
│ Load visible posts          │
│ through postsForTimeline()  │
└─────────────────────────────┘
        ↓
filter post body
        ↓
paginate posts
        ↓
Search.vue
        ↓
Users | Groups | Posts
```

---

## Privacy and Authorization

The global search route requires authentication.

User and group searches currently search the stored user/group records directly.

Post search additionally applies the existing timeline visibility rules.

This prevents global search from becoming a way to discover private group posts that would otherwise be hidden.

---

## Result

Poet-Web now provides one search interface for several important content types.

Users can:

```text
search once
    ↓
find matching users
    ↓
find matching groups
    ↓
find matching visible posts
```

The implementation reuses existing resources and components instead of creating separate search-only presentation logic.

Post searching also preserves the same group-privacy rules already used elsewhere in the application.