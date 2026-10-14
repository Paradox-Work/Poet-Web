# Group Invitation Flow

## Overview

Approved group administrators can invite an existing registered user to a group by username or email.

Invitations are stored in the shared `group_users` table as pending memberships with a temporary token.

## Main controller

```text
app/Http/Controllers/GroupInvitationController.php
```

The invitation system is intentionally separate from normal join-request handling.

## Sending an invitation

Route:

```text
POST /groups/{group:slug}/invitations
route: group.inviteUsers
```

The request uses `InviteUsersRequest`, which validates the target and administrator permission.

A pending invitation stores:

```text
status = pending
role = member
token = random 64-character token
token_expire_date = now + 24 hours
token_used = null
created_by = inviting administrator
```

`updateOrCreate()` reuses the existing membership row if one already exists.

The database also enforces one membership row per:

```text
user_id + group_id
```

## Invitation notification

The invited user receives:

```text
InvitationInGroup
```

The notification contains the temporary invitation token.

## Viewing an invitation

Authenticated route:

```text
GET /groups/invitations/{token}
route: group.invitation
```

The backend verifies:

- token exists;
- logged-in user is the invitee;
- invitation is still pending;
- token has not been used;
- expiration time has not passed.

Another user cannot use a copied invitation URL.

## Accepting

Route:

```text
POST /groups/invitations/{token}/accept
route: group.approveInvitation
```

Acceptance changes:

```text
status → approved
token_used → current timestamp
```

The original inviter is notified through:

```text
InvitationApproved
```

Using POST rather than GET ensures that opening/viewing an invitation does not itself mutate membership state.

## Declining

Route:

```text
POST /groups/invitations/{token}/decline
route: group.declineInvitation
```

Declining changes:

```text
status → rejected
token_used → current timestamp
```

## Visibility relationship

Invitation acceptance grants approved membership.

For a private group, that newly approved membership grants access to protected group posts, members, photos, comments, and attachments.

For a public group, membership is not required for reading, but is still required for publishing into the group and member-only administrative behavior.

## Security

The flow combines:

```text
authenticated routes
→ admin authorization
→ existing-user validation
→ unique membership constraint
→ random token
→ expiration
→ invitee ownership check
→ POST acceptance/decline
→ single-use token
```

## Main files

```text
app/Http/Controllers/GroupInvitationController.php
app/Http/Requests/InviteUsersRequest.php
app/Models/GroupUser.php
app/Notifications/InvitationInGroup.php
app/Notifications/InvitationApproved.php
resources/js/Pages/Group/Invitation.vue
resources/js/Pages/Group/InviteUserModal.vue
resources/js/Pages/Group/View.vue
routes/web.php
```
