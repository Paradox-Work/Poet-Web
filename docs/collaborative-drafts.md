# Collaborative Poem Drafts

## Overview

A poem-draft owner can explicitly share a draft with other registered writers.

This is collaborative draft editing, not realtime simultaneous cursor collaboration.

Collaborators can:

- open the shared draft;
- edit its writing fields;
- trigger the normal autosave flow.

Only the draft owner can:

- add/remove collaborators;
- publish the draft.

## Database

`post_collaborators` stores:

```text
post_id
user_id
added_by
```

The pair:

```text
post_id + user_id
```

is unique.

## Adding a collaborator

Route:

```text
POST /drafts/{post}/collaborators
```

The draft owner supplies a username.

Rules:

- post must still be a draft;
- current user must own the draft;
- owner cannot add themselves;
- target user must exist;
- for a group draft, the collaborator must already be an approved member of that group.

The last rule prevents collaboration from bypassing private/group publishing boundaries.

## Editing

`Post::canEditDraft()` returns true for:

- the draft owner;
- an explicitly attached collaborator.

`DraftController::updateDraft()` uses that rule.

When a collaborator saves, Poet-Web preserves the draft's existing:

- type;
- group_id.

A collaborator therefore cannot move someone else's shared draft into a different group or change its ownership scope.

## Publishing

The poem-writing page disables publishing for a collaborator.

The owner retains publication authority.

This keeps collaboration useful while making the ownership decision explicit.

## Draft listing

The Drafts page includes:

- owned drafts;
- drafts shared with the current user.

Shared drafts are labeled "Shared with you", and only the owner receives the delete action.

## Concurrency model

Autosave remains HTTP-based.

Two collaborators editing at exactly the same time are currently last-write-wins.

Realtime presence, cursors, document-operation merging, and conflict resolution are not implemented.

## Main files

```text
database/migrations/2026_10_14_000009_create_post_collaborators_table.php
app/Models/Post.php
app/Models/User.php
app/Http/Controllers/DraftController.php
app/Http/Controllers/DraftCollaboratorController.php
app/Http/Controllers/PoemController.php
resources/js/Pages/Poem/Write.vue
resources/js/Pages/Drafts/Index.vue
routes/web.php
```
