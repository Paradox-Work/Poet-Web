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
    USERS ||--o{ BOOKMARKS : saves
    POSTS ||--o{ BOOKMARKS : bookmarked
    USERS ||--o{ READING_LISTS : owns
    READING_LISTS ||--o{ READING_LIST_POST : contains
    POSTS ||--o{ READING_LIST_POST : listed
    USERS ||--o{ CONVERSATION_USER : participates
    CONVERSATIONS ||--o{ CONVERSATION_USER : has
    CONVERSATIONS ||--o{ MESSAGES : contains
    USERS ||--o{ MESSAGES : sends
    POSTS ||--o{ POST_COLLABORATORS : shared_with
    USERS ||--o{ POST_COLLABORATORS : collaborates
    USERS ||--o{ REPORTS : reports

    USERS {
        bigint id PK
        string name
        string username UK
        string email UK
        string avatar_path
        string cover_path
        bigint pinned_post_id
        boolean is_site_admin
        timestamp suspended_at
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

    READING_LISTS {
        bigint id PK
        bigint user_id FK
        string name
        string description
    }

    READING_LIST_POST {
        bigint id PK
        bigint reading_list_id FK
        bigint post_id FK
    }

    CONVERSATIONS {
        bigint id PK
        bigint created_by FK
        string direct_key UK
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
        timestamp edited_at
    }

    POST_COLLABORATORS {
        bigint id PK
        bigint post_id FK
        bigint user_id FK
        bigint invited_by FK
        string status
        string role
    }

    REPORTS {
        bigint id PK
        bigint reporter_id FK
        string target_type
        bigint target_id
        string reason
        string status
        bigint reviewed_by FK
        string action
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

### Library

Bookmarks create a unique user-to-post saved relationship.

Reading lists are user-owned collections connected to posts through `reading_list_post`.

### Messaging

Direct conversations use a `conversation_user` participant pivot and a separate `messages` table.

The `direct_key` uniquely represents a pair of users.

### Collaborators

`post_collaborators` grants invited users shared poem-edit permission after acceptance.

### Reports and administration

Reports use a typed target:

```text
post
comment
user
```

and store moderation status/action information.

Site administration itself is represented by `users.is_site_admin`.

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

users
→ conversations → messages

users
→ bookmarks / reading_lists → posts

posts
→ post_collaborators → users

users
→ reports → moderation
```

That is enough to show database depth without spending several presentation slides zooming into the same schema.
