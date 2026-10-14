# Group Role Management

## Overview

Approved group administrators can change another approved member between:

```text
member
admin
```

The original group owner cannot be demoted through this workflow.

## Route

```text
POST /groups/{group:slug}/members/role
route: group.changeRole
```

Controller:

```text
GroupMembershipController::changeRole()
```

## Authorization

The acting user must pass:

```text
$group->isAdmin($actor->id)
```

which requires:

```text
status = approved
role = admin
```

Ordinary members and outsiders receive 403.

## Validation

The request accepts:

```text
user_id → required existing user
role    → valid GroupUserRole enum
```

The target membership must belong to the current group and have:

```text
status = approved
```

Pending/rejected users cannot be promoted directly.

## Owner protection

Before changing role, the controller checks:

```text
$group->isOwner(target user)
```

The owner's role cannot be changed.

## No-op changes

If the submitted role already matches the current role, the controller returns without writing another database change.

## Notification

A successful role change sends:

```text
GroupRoleChanged
```

to the affected user.

## Permission effect

After promotion:

```text
member → admin
```

the user begins passing group administrator authorization checks.

After demotion:

```text
admin → member
```

those administrative permissions are removed.

## Main files

```text
app/Http/Controllers/GroupMembershipController.php
app/Http/Resources/GroupMemberResource.php
app/Models/Group.php
app/Models/GroupUser.php
app/Notifications/GroupRoleChanged.php
resources/js/Pages/Group/View.vue
routes/web.php
```
