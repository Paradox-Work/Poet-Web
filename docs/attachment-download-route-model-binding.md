cat > docs/attachment-download-route-model-binding.md <<'EOF'
# Laravel Attachment Downloads and Route Model Binding

## Purpose

This note explains how Poet-Web downloads a post attachment and how Laravel connects:

1. the attachment ID in the URL,
2. the `PostAttachment` database row,
3. the physical file stored on disk,
4. the controller method that returns the download.

---

## 1. Complete flow

```text
attachment.id = 1
        ↓
route('post.download', 1)
        ↓
/posts/attachments/1/download
        ↓
Laravel matches the route
        ↓
{attachment} = 1
        ↓
PostAttachment $attachment
        ↓
Laravel loads PostAttachment with ID 1
        ↓
$attachment->path finds the physical file
        ↓
$attachment->name provides the original filename
        ↓
Storage::disk('public')->download(...)
        ↓
Browser downloads the file