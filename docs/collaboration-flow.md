# Poem Collaboration Flow

## Overview

Poet-Web supports shared editing of published poems between registered users.

The owner remains the publication owner. Accepted collaborators receive edit permission.

This feature is shared editing, not simultaneous conflict-free co-editing. Two users should not intentionally edit/save the same poem at exactly the same instant.

## Data model

`post_collaborators` stores:

```text
post_id
user_id
invited_by
status
role
timestamps
```

Current role:

```text
editor
```

Current statuses:

```text
pending
accepted
```

The pair:

```text
post_id + user_id
```

is unique.

## Owner invitation

Only:

```text
post.user_id = authenticated user
AND
post.type = poem
AND
post.status = published
```

may send collaborator invitations.

The owner enters an existing username.

Self-invitation is rejected.

## Group-poem safety

For a poem belonging to a group, the invited collaborator must already be an approved group member.

Accepted collaborators must still satisfy group membership when opening/saving the group poem.

This prevents collaboration from bypassing private-group or group-membership rules.

## Accept / decline

The invitee may:

```text
accept
→ status = accepted

decline
→ collaborator row deleted
```

Only the invitee can accept/decline their invitation.

The invitee receives a database notification when invited. After acceptance, the poem owner receives a collaboration-accepted notification. Both notifications integrate with the existing Poet-Web notification dropdown.

## Editing

`UpdatePostRequest::authorize()` allows:

- poem owner;
- accepted poem collaborator;
- while still respecting required group membership.

The poem writing page also accepts an authorized collaborator when opened with:

```text
/write/poem?edit={post_id}
```

A collaborator can edit poem content/metadata but cannot delete the owner's poem merely because they can edit it.

## Managing collaborators

The poem owner's content menu exposes:

```text
Collaborators
```

The management page allows:

- invite by username;
- see pending/accepted collaborators;
- remove a collaborator;
- open the poem editor.

Accepted collaborators also receive a personal Collaborations page for shared poems.

## Account deletion

If a collaborator deletes their account, their personal collaboration rows are removed.

Historical inviter/user relationships otherwise use anonymized deleted-user handling where appropriate.

## Main files

```text
app/Models/PostCollaborator.php
app/Models/Post.php
app/Http/Controllers/CollaborationController.php
app/Http/Controllers/PoemController.php
app/Http/Controllers/DraftController.php
app/Http/Requests/UpdatePostRequest.php
database/migrations/2026_10_14_000008_create_post_collaborators_table.php
resources/js/Pages/Collaborations/Index.vue
resources/js/Pages/Collaborations/Manage.vue
resources/js/Components/app/EditDeleteDropdown.vue
tests/Feature/CollaborationTest.php
routes/web.php
```
