# Group Publication Flow

## Overview

A post or poem belongs to a group when:

```text
posts.group_id = group.id
```

Reading and publishing use different permissions.

## Reading group publications

Reading follows the group's visibility.

```text
public group
→ published group content is readable by guests and authenticated users

private group
→ published group content is readable only by approved members
```

The shared implementation is:

```text
Post::isVisibleTo()
Post::postsForTimeline()
```

## Publishing into a group

Publishing is stricter than reading.

Even in a public group, only an approved member may create a group publication.

`StorePostRequest` validates the supplied `group_id` against approved membership.

Conceptually:

```text
authenticated user
→ submit group_id
→ approved membership exists?
   yes → create publication
   no  → validation error
```

## Group profile loading

`GroupController::profile()` loads group posts only when:

```text
group is public
OR
viewer is approved member
```

The query starts from:

```php
Post::postsForTimeline($userId)
```

and adds:

```text
posts.group_id = current group
```

Pagination:

```text
10 per page
```

JSON requests used by infinite scrolling apply the same permission check.

## Home feed

The Home feed can contain:

- standalone public publications;
- public-group publications;
- private-group publications for approved members.

For an authenticated user using:

```text
source=groups
```

the query is further restricted to group publications already visible to that user.

## Interactions

An authenticated user can comment/react only if the parent publication is visible.

Therefore:

- an authenticated outsider can interact with a public-group publication;
- an outsider cannot interact with a private-group publication;
- a former private-group member loses interaction access after membership is removed.

## Attachments

Group publication attachments inherit the parent publication's visibility.

```text
public group attachment
→ guest-accessible through authorized attachment route

private group attachment
→ approved member only
```

The physical file remains stored privately.

## Moderation

The publication author may delete their publication.

An approved group administrator may also delete a group member's publication.

Soft deletion records `deleted_by` for moderation/audit context.

## Pinning

A group publication can be pinned to its group only by an approved administrator.

The group stores:

```text
pinned_post_id
```

## Main files

```text
app/Models/Post.php
app/Http/Controllers/GroupController.php
app/Http/Controllers/PostController.php
app/Http/Controllers/PostInteractionController.php
app/Http/Requests/StorePostRequest.php
app/Http/Requests/UpdatePostRequest.php
resources/js/Pages/Group/View.vue
resources/js/Components/app/PostList.vue
resources/js/Components/app/PostItem.vue
routes/web.php
```
