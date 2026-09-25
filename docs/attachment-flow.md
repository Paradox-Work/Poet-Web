# Post attachment upload flow

```mermaid
flowchart TD
    A[User selects file] --> B[PostModal]
    B --> C[attachmentFiles]
    C --> D[Local FileReader preview]

    C --> E[form.attachments]
    E --> F[Inertia FormData]
    F --> G[POST /posts]

    G --> H[StorePostRequest]
    H --> I[PostController]

    I --> J[Create Post]
    I --> K[Store file on public disk]

    K --> L[Create PostAttachment record]

    L --> M[PostAttachmentResource]
    M --> N[PostResource]
    N --> O[PostItem.vue]

    O --> P[Displayed attachment]
```

## Storage

Uploaded files are stored under:

`storage/app/public/attachments/{post_id}`

The `post_attachments` database table stores metadata including the original file name, storage path, public URL, MIME type, size, uploader, and owning post.

## Editing attachments

```mermaid
flowchart TD
    A[Open Edit Post] --> B[Existing attachments]
    A --> C[Select new attachments]

    B --> D{User removes attachment}
    D --> E[Add ID to deleted_file_ids]
    E --> F{Undo?}
    F -->|Yes| B
    F -->|No| G[Save Changes]

    C --> H[attachmentFiles]
    H --> G

    G --> I["POST /posts/{id}"]
    I --> J["_method = PUT"]
    J --> K[UpdatePostRequest]
    K --> L[PostController update]

    L --> M[Update post body]
    L --> N[Store new files]
    L --> O[Delete selected attachment records]

    O --> P[Delete physical files after DB commit]
```