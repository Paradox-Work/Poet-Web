# Direct Messaging Flow

## Overview

Poet-Web supports private one-to-one conversations between authenticated users.

The implementation deliberately uses lightweight HTTP polling instead of adding a websocket/Reverb deployment dependency before the qualification presentation.

## Starting a conversation

From another user's profile:

```text
POST /messages/start/{user}
route: messages.start
```

The backend rejects messaging yourself.

Before creating a new conversation, Poet-Web looks for an existing two-person conversation containing both users. If one exists, it is reused.

Otherwise:

```text
create conversation
→ attach both users
→ sender last_read_at = now
→ recipient last_read_at = null
→ redirect to conversation
```

## Reading conversations

Route:

```text
GET /messages/{conversation?}
route: messages.show
```

Only conversation participants may open a conversation.

A non-participant receives 404 rather than learning whether the private conversation exists.

Opening a conversation updates the current user's `last_read_at`.

## Sending messages

Route:

```text
POST /messages/{conversation}
route: messages.store
```

Validation:

```text
body
→ required
→ string
→ max 5000 characters
→ must contain a non-whitespace character
```

After insertion:

- the conversation timestamp is touched;
- sender read state is updated;
- JSON for the new message is returned.

## Live updating

The conversation UI requests:

```text
GET /messages/{conversation}/items
```

every five seconds while a conversation is open.

This provides a live-updating experience without requiring:

- websockets;
- Laravel Reverb;
- a separate realtime process;
- extra production infrastructure.

A failed polling request is silent and retried on the next interval.

## Unread counts

`conversation_user.last_read_at` tracks when each participant last viewed the conversation.

Conversation summaries count messages:

```text
authored by the other participant
AND
created after last_read_at
```

## Privacy

Direct-message routes are inside the authenticated route group.

Authorization is repeated in `MessageController::ensureParticipant()`, so knowing a conversation ID does not grant access.

## Main files

```text
database/migrations/2026_10_14_000007_create_direct_messages_tables.php
app/Models/Conversation.php
app/Models/Message.php
app/Http/Controllers/MessageController.php
resources/js/Pages/Messages/Index.vue
resources/js/Pages/Profile/View.vue
routes/web.php
```
