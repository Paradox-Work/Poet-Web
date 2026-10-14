# Group Member Removal Flow

## Overview

Approved group administrators may remove approved members from a group.

This deletes only the `group_users` membership row. It does not delete the user account or the user's other data.

## Route

```text
DELETE /groups/{group:slug}/members
route: group.removeUser
```

Controller:

```text
GroupMembershipController::removeUser()
```

## Authorization

The acting user must be an approved administrator.

```text
$group->isAdmin(actor)
```

Otherwise:

```text
403 Forbidden
```

## Validation

The request validates:

```text
user_id
→ required
→ integer
→ existing users.id
```

## Owner protection

The group owner cannot be removed.

```text
groups.user_id = target user
→ 403 Forbidden
```

## Membership lookup

The target must have an approved membership in the current group.

```text
group_id = current group
user_id = target
status = approved
```

If that row does not exist, the endpoint returns not found.

## Removal effect

After deleting the membership:

- the user no longer appears as a member;
- the user can no longer publish into the group;
- if the group is private, protected posts/photos/attachments become inaccessible;
- stale direct URLs do not preserve access because publication visibility is checked on every protected request.

## Notification

The removed user receives:

```text
UserRemovedFromGroup
```

## Main files

```text
app/Http/Controllers/GroupMembershipController.php
app/Models/Group.php
app/Models/GroupUser.php
app/Notifications/UserRemovedFromGroup.php
resources/js/Pages/Group/View.vue
routes/web.php
```
