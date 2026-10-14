# Group Membership Approval Flow

## Overview

Groups with:

```text
auto_approval = false
```

use a moderated join-request workflow.

A normal join request is stored as:

```text
status = pending
role = member
token = null
```

The null token distinguishes a user-created join request from an invitation.

## Route

Administrators resolve requests through:

```text
POST /groups/{group:slug}/requests/resolve
route: group.resolveJoinRequest
```

Controller:

```text
GroupMembershipController::resolveJoinRequest()
```

## Authorization

The controller requires:

```text
authenticated user
+
approved group administrator
```

Non-admins receive:

```text
403 Forbidden
```

## Validation

The request validates:

```text
user_id → required existing user
action  → approve | reject
```

The membership lookup additionally requires:

```text
group_id = current group
status = pending
token IS NULL
```

This prevents the join-request endpoint from resolving invitation memberships.

## Approval

```text
action = approve
→ status = approved
```

The user becomes an active member and may publish into the group.

For a private group, approval also grants read access to its protected posts/members/photos.

## Rejection

```text
action = reject
→ status = rejected
```

The row is retained and can later be reused if the user requests membership again.

## Notifications

After the administrator decision, the applicant receives:

```text
GroupJoinRequestResolved
```

## Pending request privacy

Only group administrators receive the pending-request list on the group profile.

Ordinary members, guests, and outsiders do not receive this management data.

## Main files

```text
app/Http/Controllers/GroupMembershipController.php
app/Models/Group.php
app/Models/GroupUser.php
app/Notifications/GroupJoinRequestResolved.php
resources/js/Pages/Group/View.vue
routes/web.php
```
