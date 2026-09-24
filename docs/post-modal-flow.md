# Post creation and editing flow

`PostModal` is reused for both creating and editing posts.

The presence of a post ID determines which operation is performed.

```mermaid
flowchart TD
    A[PostModal] --> B{Does the post have an ID?}

    B -->|No - id = null| C[Create Post]
    B -->|Yes - id exists| D[Edit Post]

    C --> E[TiptapEditor]
    D --> E

    E --> F{Submission type}

    F -->|Create| G["form.post()"]
    F -->|Update| H["form.put()"]

    G --> I["POST /posts"]
    H --> J["PUT /posts/{post}"]

    I --> K[(Database)]
    J --> K
```

## Purpose

Instead of maintaining separate editors for post creation and post editing,
Poet-Web uses one reusable `PostModal` component and one `TiptapEditor`.

When `post.id` is `null`, the modal creates a new post using a POST request.

When `post.id` exists, the modal updates that post using a PUT request.