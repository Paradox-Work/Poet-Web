# Profile Content Flow

## Overview

Profiles are public:

```text
GET /u/{user:username}
route: profile
```

Guests and authenticated users can inspect profile identity, follower/following information, standalone publications, and standalone-publication photos.

Only the account owner receives private account fields and the `My Profile` editing tab.

## Public profile data

The controller provides:

- public user identity;
- follower count;
- following count;
- paginated standalone publications;
- followers;
- followings;
- public standalone-publication photos.

`UserResource` does not expose another user's email or email verification state.

## Publications

Profile publication queries require:

```text
status = published
user_id = viewed user
group_id IS NULL
```

This intentionally keeps group publications out of the public profile stream.

If the user has a pinned standalone publication, ordering places it first.

Posts are paginated by 12 with the independent parameter:

```text
posts_page
```

## Guest behavior

Guests receive the same public standalone publication list instead of a login-only placeholder.

They cannot follow from the profile without authenticating; the frontend shows a sign-in action.

## Followers and following

Followers and followings are independently paginated and may be filtered with `people_search`.

The search term is capped at 80 characters and matches name/username.

Page parameters are independent:

```text
followers_page
following_page
```

## Photos

The photo gallery starts from visible timeline post IDs, limits them to the viewed author and standalone publications, then selects image attachments.

Therefore group attachments do not leak through a user's public photo gallery.

The resource supplies authorization-controlled attachment URLs rather than direct public-storage URLs.

## My Profile tab

`my_profile` is only accepted when:

```text
authenticated user ID = viewed profile user ID
```

For anyone else, the requested tab falls back to `posts`.

## Profile update

Profile update requires:

- name;
- valid unique username;
- valid unique email.

Changing email clears `email_verified_at`.

Username uniqueness is enforced by both Laravel validation and a database unique index.

## Account deletion

Deletion requires the current password.

The implementation anonymizes account identity, hides authored publications/owned groups, removes relationship data that should not survive, then soft-deletes the user.

This preserves database integrity while making the deleted account inactive and non-identifying.

## Main files

```text
app/Http/Controllers/ProfileController.php
app/Http/Requests/ProfileUpdateRequest.php
app/Http/Resources/UserResource.php
app/Http/Resources/ProfilePublicationResource.php
app/Http/Resources/PostAttachmentResource.php
app/Models/User.php
app/Models/Post.php
resources/js/Pages/Profile/View.vue
```
