# Poet-Web ER Diagram

The following diagram focuses on the project-domain tables that are most useful in a qualification presentation. Framework/support tables are intentionally omitted.

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

    USERS {
        bigint id PK
        string name
        string username UK
        string email UK
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
```

## Important relationships to explain

### Users and publications

A user can create many publications.

A publication may be:

- a standalone public publication;
- a publication inside a group.

### Groups and memberships

`group_users` represents membership and stores:

- membership status;
- member/admin role;
- invitation token information.

The pair `group_id + user_id` is unique.

### Group visibility

`groups.visibility` is:

```text
public
private
```

Public group publications can be read by guests.

Private group publications require approved membership.

### Comments

Comments belong to both a user and a post.

`parent_id` is self-referencing, allowing replies/threading.

### Reactions

Reactions are polymorphic:

```text
object_type
object_id
```

This allows the same reaction table to support both posts and comments.

### Followers

The followers table represents user-to-user relationships:

```text
user_id
→ followed user

follower_id
→ user who follows
```

## Presentation advice

Use the diagram to explain relationships, not every field.

The most useful sequence is:

```text
users
→ posts
→ comments / reactions / attachments

users
→ group_users
→ groups
→ group posts
```

That is enough to show database depth without spending several presentation slides zooming into the same schema.
