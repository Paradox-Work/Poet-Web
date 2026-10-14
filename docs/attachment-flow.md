# Post Attachment Upload Flow

## Overview

Poet-Web supports attachments on publication creation and update.

New post attachments are stored privately, represented by `PostAttachment` rows, and exposed to the frontend through authorization-controlled application routes.

## Create flow

```text
select files
→ PostModal.vue
→ multipart request
→ StorePostRequest validation
→ PostController::store()
→ create Post
→ store physical files on local private disk
→ create PostAttachment records
→ commit
```

Physical path:

```text
storage/app/private/attachments/{post_id}/...
```

## Metadata

`post_attachments` stores metadata such as:

```text
post_id
name
path
url
mime
size
created_by
created_at
```

The legacy `url` database field is not used as the public serving URL for new files. `PostAttachmentResource` generates controlled routes instead.

## Transaction cleanup

During creation/update, newly stored paths are tracked.

If the database operation throws, newly written private files are deleted before the exception is rethrown.

For attachment deletion during an edit:

```text
mark IDs in deleted_file_ids
→ delete records inside transaction
→ commit database transaction
→ delete physical files
```

This avoids deleting an existing physical file before the database update is known to have succeeded.

## Update behavior

An edit can contain:

```text
existing attachments kept
existing attachments removed
new attachments uploaded
```

`UpdatePostRequest` validates the resulting stored attachment set, not only the newly uploaded request files.

## Rendering and access

Attachment resources expose:

```text
post.attachment.view
post.download
```

The controller rechecks publication visibility before returning file bytes.

This means the attachment layer inherits the same standalone/group visibility policy as the post itself.

## Existing file migration

Older post attachments that were stored on the public disk are moved to the private local disk by:

```text
2026_10_14_000003_move_post_attachments_to_private_storage.php
```

The serving controller retains a public-disk fallback for migration compatibility.

## Related documentation

- [Attachment validation flow](attachment-validation-flow.md)
- [Attachment size validation](attachment-size-validation.md)
- [Attachment preview flow](attachment-preview-flow.md)
- [Attachment routes and authorization](attachment-download-route-model-binding.md)
