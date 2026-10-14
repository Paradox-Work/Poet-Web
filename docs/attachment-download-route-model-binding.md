# Attachment Routes, Model Binding, and Authorization

## Overview

Post attachments are stored privately and served through Laravel after authorization.

They are not intended to be accessed through a direct `/storage/...` path.

## Routes

Inline view:

```text
GET /posts/attachments/{attachment}
route: post.attachment.view
```

Download:

```text
GET /posts/attachments/{attachment}/download
route: post.download
```

Both routes are public endpoints in the routing sense because a guest may legitimately access an attachment belonging to public content.

The controller still performs content authorization before returning file bytes.

## Route model binding

```text
/posts/attachments/12
→ resolve PostAttachment 12
→ PostAttachmentController
```

Missing attachment rows return 404.

## Draft authorization

For a draft:

```text
authenticated user exists
AND
post.user_id = authenticated user
→ allowed

otherwise
→ 404
```

## Published authorization

For published content the controller calls:

```php
$post->isVisibleTo($request->user()?->id)
```

Therefore:

```text
standalone-publication attachment
→ guest-accessible

public-group attachment
→ guest-accessible

private-group attachment
→ approved member only
```

The attachment URL therefore cannot bypass the parent publication's access model.

## Storage

New uploads use:

```text
Laravel local disk
storage/app/private/attachments/{post_id}/...
```

The serving controller checks:

1. `local`;
2. legacy `public` fallback.

The public fallback exists for older/migrated records. New application uploads are private.

## Response hardening

Inline responses preserve the stored MIME type and include:

```text
X-Content-Type-Options: nosniff
```

Downloads use the original attachment name.

## Resource URLs

`PostAttachmentResource` generates:

```text
url
→ post.attachment.view

download_url
→ post.download
```

The database `url` field is not the authorization mechanism.

## Migration

```text
2026_10_14_000003_move_post_attachments_to_private_storage.php
```

moves older public post files to private storage when possible.

## Tests

Attachment regression tests cover:

- approved access;
- outsider denial;
- draft owner-only access;
- private storage for new uploads;
- guest denial for private-group files.

Public-group behavior inherits the tested `Post::isVisibleTo()` visibility model.

## Main files

```text
app/Http/Controllers/PostAttachmentController.php
app/Http/Resources/PostAttachmentResource.php
app/Models/PostAttachment.php
app/Models/Post.php
database/migrations/2026_10_14_000003_move_post_attachments_to_private_storage.php
tests/Feature/AttachmentSecurityTest.php
routes/web.php
```
