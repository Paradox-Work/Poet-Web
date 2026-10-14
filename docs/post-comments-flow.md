# Comments and Replies Flow

## Overview

Visible publications include a threaded comment system.

Guests may read comments attached to content they are allowed to view.

Authentication is required to:

- create comments;
- reply;
- edit;
- delete;
- react.

## Database

All comments use the same `comments` table.

```text
post_id
user_id
parent_id
comment
created_at
updated_at
```

`parent_id` is a nullable self-reference.

```text
parent_id = null
→ top-level comment

parent_id = another comment ID
→ reply
```

The model provides:

```text
user()
post()
reactions()
comments()
parent()
```

## Loading

`Post::postsForTimeline()` loads comments with:

- author;
- reaction count;
- current authenticated user's reaction.

`PostResource` receives a flat comment collection and builds the nested tree in PHP.

See [Comment replies flow](comment-replies-flow.md).

## Creating a comment or reply

Route:

```text
POST /posts/{post}/comments
route: post.comment.create
```

Controller:

```text
CommentController::createComment()
```

The controller first verifies:

```text
$post->isVisibleTo(current user)
```

Then validates:

```text
comment
→ required string max 2000

parent_id
→ nullable integer
→ must be a comment belonging to the same post
```

This same endpoint therefore creates both root comments and replies.

## Reply integrity

The `parent_id` validation is scoped to:

```text
post_id = current post
```

so a reply cannot point to a comment from another publication.

## Editing

Route:

```text
PUT /comments/{comment}
```

`UpdateCommentRequest` requires:

- authenticated user owns the comment;
- underlying publication is still visible.

A former member of a private group cannot continue editing an old comment after access is revoked.

## Deletion

Route:

```text
DELETE /comments/{comment}
```

Deletion first rechecks publication visibility.

It is then allowed for:

- comment owner;
- publication owner.

The database self-reference uses cascade deletion, so deleting a parent comment removes dependent replies.

## Reactions

Comment reactions use:

```text
POST /comments/{comment}/reaction
```

and also recheck parent-publication visibility.

## Frontend

`CommentList.vue` recursively renders nested replies.

It is guest-safe:

- comments render without `auth.user`;
- comment form is hidden for guests;
- trying to react as a guest redirects to login;
- edit/delete controls handle null authentication state.

## Main files

```text
app/Models/Comment.php
app/Models/Post.php
app/Http/Controllers/CommentController.php
app/Http/Requests/UpdateCommentRequest.php
app/Http/Resources/CommentResource.php
app/Http/Resources/PostResource.php
resources/js/Components/app/CommentList.vue
resources/js/Components/app/PostItem.vue
routes/web.php
```
