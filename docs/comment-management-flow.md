# Comment Management and Authorization

## Overview

Guests may read comments belonging to publications they can view.

Authentication is required to create, edit, delete, reply to, or react to comments.

Backend checks enforce both ownership/moderation rules and publication visibility.

## Creating comments

Route:

```text
POST /posts/{post}/comments
```

Before validation/creation, `CommentController` verifies:

```php
$post->isVisibleTo($request->user()->id)
```

A user cannot comment on a private group publication unless they are currently allowed to view it.

Comment text is required, must be a string, and is limited to 2000 characters.

An optional `parent_id` must reference another comment from the same post.

## Editing comments

Route:

```text
PUT /comments/{comment}
```

`UpdateCommentRequest::authorize()` requires all of the following:

```text
authenticated user exists
comment.user_id = authenticated user ID
comment.post is still visible to authenticated user
```

This means a former group member cannot keep editing an old comment after their group access is removed.

## Deleting comments

Route:

```text
DELETE /comments/{comment}
```

The controller first verifies the post is still visible to the authenticated user.

Deletion is then allowed when the user is:

- the comment owner; or
- the publication owner.

Unauthorized users receive a forbidden response. Users who no longer have access to the underlying group publication receive not-found behavior from the visibility boundary.

## Comment reactions

Route:

```text
POST /comments/{comment}/reaction
```

The controller checks visibility of `$comment->post` before toggling the reaction.

Knowing a comment ID is therefore insufficient to react to protected group content.

## Frontend behavior

`CommentList.vue` is guest-safe:

- comments can render without an authenticated user;
- the new-comment editor is hidden for guests;
- reacting as a guest sends the user to login;
- edit/delete controls safely handle `auth.user = null`.

Frontend controls improve usability, but the backend checks remain the security boundary.

## Regression coverage

`VisibilitySecurityTest` covers:

- outsiders cannot react to private group publications;
- outsiders cannot comment on private group publications;
- outsiders cannot react to comments on private group publications;
- former members cannot edit/delete their old group comments after losing access.

## Main files

```text
app/Http/Controllers/CommentController.php
app/Http/Requests/UpdateCommentRequest.php
app/Models/Post.php
resources/js/Components/app/CommentList.vue
resources/js/Components/app/EditDeleteDropdown.vue
tests/Feature/VisibilitySecurityTest.php
```
