# Group Profile Flow

## Overview

Every group has a public profile URL:

```text
GET /g/{group:slug}
route: group.profile
```

Laravel resolves the group by slug.

The group page separates public group metadata from protected group content.

## Group metadata

The page can show:

- name;
- about text;
- cover;
- thumbnail;
- visibility;
- automatic/manual membership mode;
- current user's membership state/role when available.

## Content permission

The controller calculates:

```text
isApprovedMember
canViewContent
```

with:

```text
visibility = public
→ canViewContent = true

visibility = private AND approved member
→ canViewContent = true

visibility = private AND not approved
→ canViewContent = false
```

This rule controls group:

- posts;
- approved member list;
- photo gallery.

For private-group outsiders, those collections are not exposed.

## Group posts

When `canViewContent` is true, group posts are loaded from:

```php
Post::postsForTimeline($userId)
```

and restricted to:

```text
posts.group_id = current group
```

Pinned group content is ordered first when `pinned_post_id` is set.

Posts are paginated by 10.

JSON/infinite-scroll requests apply the same `canViewContent` check.

## Members

The approved member list is returned only when the viewer may read group content.

Pending join requests are different: they are returned only to approved group administrators.

## Photos

The photo gallery is derived from visible group-post IDs and image attachments.

Therefore:

- guests can see photos for public group publications;
- approved members can see private-group photos;
- private-group outsiders receive no photo collection.

Attachment bytes remain protected again by `PostAttachmentController`.

## Administrator controls

Approved administrators can:

- invite users;
- resolve membership requests;
- change member roles;
- remove members;
- update group settings;
- update cover/thumbnail;
- publish/moderate group content;
- pin a group post.

## Group images

Images are stored on the public disk under:

```text
groups/{group_id}/...
```

The safer replacement order is:

```text
store new image
→ update database inside transaction
→ delete old image after successful update
```

If the update fails, newly stored replacement files are removed and the previous image is left intact.

Image validation allows:

```text
jpg
jpeg
png
webp
max 5 MB
```

## Main files

```text
app/Http/Controllers/GroupController.php
app/Http/Controllers/GroupImageController.php
app/Http/Resources/GroupResource.php
app/Http/Resources/GroupMemberResource.php
app/Models/Group.php
resources/js/Pages/Group/View.vue
resources/js/Pages/Profile/TabPhotos.vue
routes/web.php
```
