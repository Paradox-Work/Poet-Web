# Attachment Routes, Model Binding, and Authorization

## Overview

Post attachments are private files served through Laravel after authorization.

They are not intended to be reachable through a direct `/storage/...` URL.

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

Both routes use Laravel route model binding for `PostAttachment`.

## Model binding

For an attachment with ID 12:

```text
/posts/attachments/12
        ↓
{attachment} = 12
        ↓
PostAttachment model is resolved
        ↓
PostAttachmentController
```

A missing database row returns 404.

## Authorization

`PostAttachmentController::authorizeAttachment()` loads the parent post and applies different rules by post state.

### Draft attachment

```text
request user exists
AND post.user_id = request user ID
→ allowed

otherwise
→ 404
```

### Published attachment

The controller calls:

```php
$post->isVisibleTo($userId)
```

Therefore:

```text
standalone publication attachment
→ visible to guests and authenticated users

group publication attachment
→ approved member only
```

This prevents an attachment URL or ID from bypassing publication access rules.

## Storage lookup

Current uploads use the private `local` disk.

The controller checks:

1. `local`;
2. `public`.

The public fallback provides compatibility during/after migration of older files. New application code stores post attachments privately.

## Response

Inline view uses the stored MIME type and includes:

```text
X-Content-Type-Options: nosniff
```

Download uses the original attachment name.

## Resource URLs

`PostAttachmentResource` generates:

```text
url          → post.attachment.view
download_url → post.download
```

The database `url` column is no longer the access-control mechanism.

## Migration

`2026_10_14_000003_move_post_attachments_to_private_storage.php` moves existing physical post files from the public disk to the private local disk when possible.

## Tests

`AttachmentSecurityTest` verifies:

- group members can access permitted attachments;
- outsiders cannot;
- draft attachments are owner-only;
- new publication attachments are stored on the private disk;
- guests cannot access group-only attachments.

## Main files

```text
routes/web.php
app/Http/Controllers/PostAttachmentController.php
app/Http/Resources/PostAttachmentResource.php
app/Models/PostAttachment.php
database/migrations/2026_10_14_000003_move_post_attachments_to_private_storage.php
tests/Feature/AttachmentSecurityTest.php
```
