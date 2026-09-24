# Implementation Notes

## Rich text editing with Tiptap

- Replaced plain textarea editing with Tiptap.
- Post content is stored as HTML in `posts.body`.
- `TiptapEditor.vue` is reused inside `PostModal.vue`.
- Added formatting for headings, bold, italic, lists, quotes, links, undo/redo.

## Unified post creation and editing

- `PostModal.vue` now handles both creating and updating posts.
- If `post.id` is `null`, a POST request is sent.
- If `post.id` exists, a PUT request is sent.
- `CreatePost.vue` now acts mainly as a launcher for the modal.

See: [PostModal flow](post-modal-flow.md)

## Attachment selection and previews

- Added `attachmentFiles` state in `PostModal.vue`.
- Images are previewed locally using `FileReader`.
- Non-image files show their filename and attachment icon.
- Files can be removed before submission.
- Backend upload is not implemented yet.