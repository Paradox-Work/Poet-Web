# Direct Messaging Flow

## Overview

Poet-Web supports one-to-one direct conversations between registered users.

Messaging is authenticated and protected by conversation membership.

The current implementation uses a short polling interval for live updates rather than a separate WebSocket/Reverb service. This keeps the feature reliable in the Laravel Sail qualification-demo environment while still updating an open conversation automatically.

## Data model

### conversations

Stores the direct conversation itself:

```text
id
created_by
direct_key
timestamps
```

`direct_key` is unique and is generated from the sorted pair of participant user IDs:

```text
min(userA, userB):max(userA, userB)
```

This prevents duplicate direct conversations between the same two users.

### conversation_user

Pivot table:

```text
conversation_id
user_id
last_read_at
timestamps
```

The pair `conversation_id + user_id` is unique.

`last_read_at` is used for unread counts.

### messages

```text
conversation_id
user_id
body
edited_at
timestamps
```

Message body is limited to 5000 characters by the controller validation.

## Starting a conversation

Route:

```text
POST /messages/start/{user}
route: messages.start
```

The controller rejects:

- messaging yourself;
- starting a conversation with a suspended account.

The deterministic `direct_key` is used with `firstOrCreate()`, so repeating the action opens the same conversation instead of creating another one.

## Viewing conversations

Routes:

```text
GET /messages
GET /messages/{conversation}
```

Only conversation participants are allowed to open a conversation.

Unauthorized users receive 404 so conversation existence is not disclosed.

The page contains:

- conversation list;
- unread counts;
- most recent message preview;
- user search;
- active thread;
- message composer.

## Sending

Route:

```text
POST /messages/{conversation}
```

Before creating a message:

```text
authenticated user
→ participant in conversation?
   yes → validate/send
   no  → 404
```

The route is rate-limited.

## Live refresh

Route:

```text
GET /messages/{conversation}/poll?after={message_id}
```

The Vue page polls approximately every two seconds while mounted.

Only messages with:

```text
id > last visible message ID
```

are returned.

This is intentionally a lightweight near-real-time implementation.

It should be described accurately as:

> Direct messaging with automatic live polling.

It is not currently a WebSocket/CRDT chat system.

## Read state

Opening/polling a conversation updates:

```text
conversation_user.last_read_at
```

Unread counts compare incoming message timestamps against that value.

The shared Inertia middleware also calculates the authenticated user's total unread direct-message count so navigation can display a badge outside the messaging page.

## Deleted accounts

Conversation users are loaded with trashed users so historical message threads can retain an anonymized participant after account deletion.

Account deletion anonymizes the user rather than exposing the previous identity.

## Main files

```text
app/Models/Conversation.php
app/Models/Message.php
app/Http/Controllers/MessageController.php
database/migrations/2026_10_14_000006_create_messaging_tables.php
resources/js/Pages/Messages/Index.vue
tests/Feature/MessagingTest.php
routes/web.php
```
