# Reaction System Flow

## Overview

Poet-Web uses one polymorphic reaction system for:

- publications;
- comments.

The currently supported reaction is:

```text
like
```

Authentication is required to react.

## Database model

`reactions` stores:

```text
id
object_id
object_type
user_id
type
created_at
```

The pair of:

```text
object_id
object_type
```

identifies the target model.

Examples:

```text
App\Models\Post
App\Models\Comment
```

A uniqueness constraint prevents the same user from storing duplicate reactions on the same target.

## Post reactions

Route:

```text
POST /posts/{post}/reaction
route: post.reaction
```

Controller:

```text
PostInteractionController::postReaction()
```

Before toggling the reaction, the controller calls the shared publication visibility check.

If the post is not visible to the authenticated user:

```text
404
```

This matters for private-group publications.

If a reaction already exists it is deleted; otherwise a new reaction is created.

Response:

```text
num_of_reactions
current_user_has_reaction
```

## Comment reactions

Route:

```text
POST /comments/{comment}/reaction
route: post.comment.reaction
```

Controller:

```text
CommentController::commentReaction()
```

The controller first checks visibility of the comment's parent publication.

Knowing a comment ID is therefore not enough to react to a protected private-group discussion.

## Public groups

An authenticated non-member may react to a public-group publication because that publication is visible.

A guest can read the publication but cannot react because reaction routes require authentication.

## Private groups

Only approved members can see/interact with private-group publications.

If membership is removed later, reaction endpoints immediately stop accepting access to the protected post/comment.

## Notifications

When a new reaction is added to another user's content:

```text
post    → ReactionAddedOnPost
comment → ReactionAddedOnComment
```

Removing an existing reaction does not create a new reaction notification.

## Frontend state

Post and comment resources expose:

```text
num_of_reactions
current_user_has_reaction
```

For guests, current-user reaction relationships are intentionally loaded as empty.

## Main files

```text
app/Models/Reaction.php
app/Models/Post.php
app/Models/Comment.php
app/Enums/ReactionEnum.php
app/Http/Controllers/PostInteractionController.php
app/Http/Controllers/CommentController.php
app/Http/Resources/PostResource.php
app/Http/Resources/CommentResource.php
resources/js/Components/app/PostItem.vue
resources/js/Components/app/CommentList.vue
routes/web.php
```
