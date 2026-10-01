# User Follow and Unfollow Flow

## Overview

Poet-Web allows authenticated users to follow and unfollow other users from profile pages.

Users can reach another user's profile by clicking the author's name or avatar on a post.

The feature provides:

- navigation from posts to user profiles;
- follower count display;
- Follow and Unfollow controls;
- persistent follower relationships;
- backend protection against following yourself;
- application-level duplicate avoidance through `firstOrCreate()`.

---

## Profile Navigation

Post authors are displayed through `PostUserHeader.vue`.

The author's name and avatar link to:

```text
/u/{user:username}
```

Example:

```text
/u/adrians
```

Navigation flow:

```text
Timeline
    ↓
Post
    ↓
Author name/avatar
    ↓
User profile
    ↓
Follow / Unfollow
```

Inertia's `Link` component is used so navigation does not require a traditional full-page reload.

---

## Followers Table

Follower relationships are stored in the `followers` table.

Important columns:

```text
user_id
follower_id
created_at
```

Their meanings are:

```text
user_id
└── user being followed

follower_id
└── user who is following
```

Example:

```text
user_id = 2
follower_id = 1
```

means:

```text
User 1 follows User 2
```

---

## Follower Model

`Follower.php` allows mass assignment of:

```text
user_id
follower_id
```

The table stores `created_at` but not `updated_at`, so the model disables Laravel's normal updated timestamp using:

```php
const UPDATED_AT = null;
```

---

## Loading a Profile

When a profile is opened, `ProfileController::index()` calculates two follow-related values.

### Follower Count

Rows are counted where:

```text
user_id = viewed profile user ID
```

This gives the number of users following that profile.

### Current User Follow State

If a user is authenticated, Poet-Web checks whether this pair exists:

```text
user_id = viewed profile user ID
follower_id = authenticated user ID
```

The result is sent to Vue as:

```text
isCurrentUserFollower
```

The follower total is sent as:

```text
followerCount
```

---

## Profile Data Flow

```mermaid
flowchart TD
    A[Open user profile]
    B[ProfileController index]
    C[Load requested User]
    D[Count followers]
    E[Check current-user follower row]
    F[Send Inertia props]
    G[Profile View.vue]
    H[Display follower count]
    I{Own profile?}
    J[Hide follow controls]
    K[Show Follow or Unfollow]

    A --> B
    B --> C
    C --> D
    C --> E
    D --> F
    E --> F
    F --> G
    G --> H
    G --> I
    I -- Yes --> J
    I -- No --> K
```

---

## Follow and Unfollow Controls

The profile hides follow controls when the viewed profile belongs to the authenticated user.

For another user's profile:

```text
isCurrentUserFollower = false
└── Follow

isCurrentUserFollower = true
└── Unfollow
```

The follower count is displayed beside the profile information.

---

## Follow Request

Both operations use:

```text
POST /users/{user}/follow
```

The route is named:

```text
user.follow
```

and is handled by:

```text
UserController::follow()
```

The request validates:

```text
follow = boolean
```

Conceptually:

```text
follow = true
└── follow target user

follow = false
└── unfollow target user
```

---

## Creating a Follow

When `follow = true`, the controller uses:

```php
Follower::firstOrCreate([
    'user_id' => $user->id,
    'follower_id' => $currentUser->id,
]);
```

This means repeated normal Follow requests for the same pair reuse the existing row instead of intentionally inserting another one.

### Important database note

The current `followers` migration does **not** define a database-level unique constraint on:

```text
(user_id, follower_id)
```

Therefore, duplicate avoidance is currently provided by the application logic using `firstOrCreate()`, not by a hard database uniqueness guarantee.

A unique index could be added later for stronger database-level enforcement.

---

## Removing a Follow

When `follow = false`, the controller deletes rows matching both:

```text
user_id = target user ID
follower_id = authenticated user ID
```

This ensures the unfollow operation targets the relationship between those two users.

After the request returns, the profile data is refreshed and the updated follower count/state are displayed.

---

## Self-Follow Protection

The controller rejects attempts to follow yourself:

```php
if ($currentUser->id === $user->id) {
    abort(
        422,
        'You cannot follow yourself.'
    );
}
```

The frontend also hides the follow controls on the authenticated user's own profile.

This gives:

```text
frontend
└── avoids presenting an invalid action

backend
└── enforces the restriction
```

---

## Main Files

### `ProfileController.php`

Loads follower count and current-user follow state for the viewed profile.

### `UserController.php`

Validates and processes follow/unfollow requests.

### `Follower.php`

Represents the follower relationship.

### `Profile/View.vue`

Displays follower count and Follow/Unfollow controls.

### `PostUserHeader.vue`

Links post authors to their profile pages.

### `routes/web.php`

Defines the profile and authenticated follow routes.

---

## Result

Authenticated users can:

1. navigate from a post author to their profile;
2. see that user's follower count;
3. follow another user;
4. unfollow a user they currently follow;
5. keep the relationship persisted in the database;
6. avoid self-following through backend validation.

Repeated ordinary Follow requests are handled safely by `firstOrCreate()`, while database-level uniqueness remains a possible future improvement.
