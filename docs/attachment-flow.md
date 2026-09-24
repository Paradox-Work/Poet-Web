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