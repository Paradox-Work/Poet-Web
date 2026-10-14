# Profile and Group Photo Gallery Flow

## Overview

Poet-Web reuses image post attachments as photo galleries.

There is no separate photo entity.

Only attachments with:

```text
mime LIKE image/%
```

are included.

## User profile photos

Public user profiles deliberately show only images from the profile owner's standalone published publications.

The query begins with:

```php
Post::postsForTimeline($currentUserId)
```

and then adds:

```text
posts.user_id = profile user
posts.group_id IS NULL
```

This means group-publication images do not appear in a user's public profile gallery, even when the group itself is public.

The resulting image collection is available to guests because standalone published content is public.

## Group photos

Group profile photos follow group visibility.

The controller derives group post IDs from:

```php
Post::postsForTimeline($userId)
```

and restricts them to the current group.

Therefore:

```text
public group
→ guests may receive visible group-post images

private group
→ only approved members receive group-post images
```

Private-group outsiders receive an empty photo collection.

## Resource and serving

Gallery items use:

```text
PostAttachmentResource
```

The resource provides application-controlled view/download URLs.

Even though the gallery knows an attachment ID, file bytes are still served through `PostAttachmentController`, which rechecks the parent publication visibility.

## Frontend

Both user and group galleries reuse:

```text
resources/js/Pages/Profile/TabPhotos.vue
```

Clicking an image opens:

```text
AttachmentPreviewModal.vue
```

The same modal supports previous/next navigation and downloading.

## Main files

```text
app/Http/Controllers/ProfileController.php
app/Http/Controllers/GroupController.php
app/Http/Resources/PostAttachmentResource.php
app/Models/PostAttachment.php
resources/js/Pages/Profile/View.vue
resources/js/Pages/Group/View.vue
resources/js/Pages/Profile/TabPhotos.vue
resources/js/Components/app/AttachmentPreviewModal.vue
```
