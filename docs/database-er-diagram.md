# Poet-Web ER Diagram

The following diagram focuses on project-domain tables that are useful in the qualification presentation. Laravel framework/support tables are intentionally omitted.

```mermaid
erDiagram
    USERS ||--o{ POSTS : creates
    USERS ||--o{ COMMENTS : writes
    USERS ||--o{ GROUPS : owns
    USERS ||--o{ GROUP_USERS : joins
    GROUPS ||--o{ GROUP_USERS : contains
    GROUPS ||--o{ POSTS : contains

    POSTS ||--o{ POST_ATTACHMENTS : has
    POSTS ||--o{ COMMENTS : receives
    COMMENTS ||--o{ COMMENTS : replies

    USERS ||--o{ FOLLOWERS : follows
    USERS ||--o{ REACTIONS : creates
    POSTS ||--o{ REACTIONS : reacts_to
    COMMENTS ||--o{ REACTIONS : reacts_to

    USERS ||--o{ BOOKMARKS : saves
    POSTS ||--o{ BOOKMARKS : saved_as

    USERS ||--o{ POST_COLLABORATORS : collaborates
    POSTS ||--o{ POST_COLLABORATORS : shared_with

    CONVERSATIONS ||--o{ CONVERSATION_USER : participants
    USERS ||--o{ CONVERSATION_USER : participates
    CONVERSATIONS ||--o{ MESSAGES : contains
    USERS ||--o{ MESSAGES : sends

    USERS ||--o{ REPORTS : submits

    USERS {
        bigint id PK
        string name
        string username UK
        string email UK
        boolean is_admin
        string avatar_path
        string cover_path
        bigint pinned_post_id
        timestamp deleted_at
    }

    GROUPS {
        bigint id PK
        bigint user_id FK
        string name
        string slug
        string visibility
        boolean auto_approval
        text about
        bigint pinned_post_id
        timestamp deleted_at
    }

    GROUP_USERS {
        bigint id PK
        bigint group_id FK
        bigint user_id FK
        string role
        string status
        string token
        timestamp token_expire_date
    }

    POSTS {
        bigint id PK
        bigint user_id FK
        bigint group_id FK
        string type
        string content_rating
        string poem_form
        json poem_genres
        string title
        text caption
        json hashtags
        longtext body
        string status
        timestamp published_at
        timestamp draft_saved_at
        timestamp deleted_at
    }

    POST_ATTACHMENTS {
        bigint id PK
        bigint post_id FK
        string name
        string path
        string mime
        bigint size
        bigint created_by FK
    }

    COMMENTS {
        bigint id PK
        bigint post_id FK
        bigint user_id FK
        bigint parent_id FK
        text comment
    }

    REACTIONS {
        bigint id PK
        bigint object_id
        string object_type
        bigint user_id FK
        string type
    }

    FOLLOWERS {
        bigint id PK
        bigint user_id FK
        bigint follower_id FK
    }

    BOOKMARKS {
        bigint id PK
        bigint user_id FK
        bigint post_id FK
    }

    POST_COLLABORATORS {
        bigint id PK
        bigint post_id FK
        bigint user_id FK
        bigint added_by FK
    }

    CONVERSATIONS {
        bigint id PK
        timestamp created_at
        timestamp updated_at
    }

    CONVERSATION_USER {
        bigint id PK
        bigint conversation_id FK
        bigint user_id FK
        timestamp last_read_at
    }

    MESSAGES {
        bigint id PK
        bigint conversation_id FK
        bigint user_id FK
        text body
    }

    REPORTS {
        bigint id PK
        bigint reporter_id FK
        string reportable_type
        bigint reportable_id
        string reason
        string status
        bigint reviewed_by FK
        timestamp reviewed_at
    }
```

## Core relationships to explain

For the presentation, do not try to explain every line of the diagram.

### Publishing

```text
users
→ posts
→ comments / reactions / attachments
```

A publication may be standalone or belong to a group.

### Groups

```text
users
→ group_users
→ groups
→ group posts
```

`group_users` stores membership status/role.

Group visibility and membership approval are separate controls.

### Social reading

```text
users
→ followers

users
→ bookmarks
→ posts
```

Bookmarks are private reading-list entries.

### Collaborative drafts

```text
post
→ post_collaborators
→ users
```

The post owner stays the publishing authority while collaborators may edit/autosave an explicitly shared draft.

### Direct messages

```text
users
→ conversation_user
→ conversations
→ messages
```

`last_read_at` on the participant pivot supports unread counts.

### Moderation

Reports use:

```text
reportable_type
reportable_id
```

to identify a reported post/comment/user.

The report also stores the reporter and optional reviewing site administrator.

## Presentation advice

The main ER slide can emphasize only these areas:

```text
Users / Posts
Groups / Membership
Comments / Reactions
Bookmarks
Messages
Reports
```

Keep the complete diagram available for questions, but do not spend several slides zooming into every field.
