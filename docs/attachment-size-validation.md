# Attachment Size and Count Validation

## Overview

Poet-Web validates both individual uploads and the final attachment set stored for a publication.

## Creation limits

`StorePostRequest` enforces:

```text
maximum attachments in create request: 10
maximum individual file size:          25 MB
maximum combined new upload size:      90 MB
```

Allowed file types are defined in `StorePostRequest::$extensions`.

## Update limits

`UpdatePostRequest` first validates the newly uploaded request files with the same per-request/per-file rules.

It then performs an after-validation check over the final stored set:

```text
existing attachments
- deleted_file_ids
+ newly uploaded attachments
```

The result must satisfy:

```text
maximum final attachment count: 10
maximum final attachment size:  90 MB
```

This prevents repeated edit operations from slowly exceeding the publication limits.

## Empty-publication check

Both creation and update reject a publication that would contain neither meaningful body text nor an attachment.

For updates, the check considers kept existing attachments, deleted attachments, and new attachments.

## Individual files

Each uploaded attachment must:

- be a valid uploaded file;
- use an allowed type;
- be at most 25 MB.

## Combined upload rule

`TotalAttachmentSize(90)` still validates the combined size of newly uploaded files in the current request.

The update request adds the final-stored-set calculation on top of that rule.

## Example

Existing publication:

```text
8 attachments
60 MB stored
```

Edit uploads:

```text
3 new attachments
15 MB total
```

No existing attachments are deleted.

Final count would be:

```text
8 + 3 = 11
```

The update fails even though the current request itself contains only three new files.

## Runtime limits

PHP/server settings such as `upload_max_filesize`, `post_max_size`, and `max_file_uploads` still apply before Laravel receives the request. Deployment configuration must permit the request to reach Laravel for application validation messages to be returned.

## Main files

```text
app/Rules/TotalAttachmentSize.php
app/Http/Requests/StorePostRequest.php
app/Http/Requests/UpdatePostRequest.php
resources/js/Components/app/PostModal.vue
tests/Feature/AttachmentSecurityTest.php
tests/Feature/QualificationHardeningTest.php
```
