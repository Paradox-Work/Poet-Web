# Group Join Flow

## Overview

Joining a group requires authentication.

Route:

```text
POST /groups/{group:slug}/join
route: group.join
```

Controller:

```text
GroupMembershipController::join()
```

## Visibility is separate from membership

A group's `visibility` setting controls reading.

A group's `auto_approval` setting controls joining.

These are independent.

Examples:

```text
public + auto approval
→ anyone can read; authenticated user joins immediately

public + manual approval
→ anyone can read; membership request waits for admin

private + auto approval
→ protected content until user joins; join is immediate

private + manual approval
→ protected content until admin approves request
```

## Existing membership

Before changing membership state, the controller searches for an existing `group_users` record for:

```text
user_id + group_id
```

If already approved:

```text
no duplicate row
→ return already-member message
```

If already pending:

```text
no duplicate request
→ return pending message
```

Rejected/inactive rows can be reused through `updateOrCreate()`.

## Automatic approval

When:

```text
auto_approval = true
```

the membership becomes:

```text
status = approved
role = member
```

The user can immediately publish into the group.

## Manual approval

When:

```text
auto_approval = false
```

the membership becomes:

```text
status = pending
role = member
token = null
```

Approved group administrators receive a `RequestToJoinGroup` notification.

The `token = null` distinction matters because invitation-based pending memberships use an invitation token.

## Duplicate protection

The flow is protected by:

- application-level existing-membership checks;
- database uniqueness for `user_id + group_id`.

## Guest behavior

Guests may open the group profile.

Trying to join sends them to authentication rather than creating membership state.

## Main files

```text
app/Http/Controllers/GroupMembershipController.php
app/Models/Group.php
app/Models/GroupUser.php
app/Notifications/RequestToJoinGroup.php
resources/js/Pages/Group/Index.vue
resources/js/Pages/Group/View.vue
routes/web.php
```
