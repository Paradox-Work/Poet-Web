# Implementation Notes

## Overview

This file summarizes the currently implemented Poet-Web features that affect the post feed and related social functionality.

It is intended as a compact technical overview. More detailed flows are documented in the dedicated files under `docs/`.

---

## Rich Text Editing with Tiptap

Poet-Web uses Tiptap for post body editing.

- `TiptapEditor.vue` is reused inside `PostModal.vue`.
- Post content is stored as HTML in `posts.body`.
- The editor supports formatted rich text instead of a plain textarea.
- `PostItem.vue` renders stored HTML when displaying the full post body.

---

## Unified Post Creation and Editing

`PostModal.vue` is reused for both creating and updating posts.

The modal determines its mode from the post ID:

```text
post.id is null
└── create post

post.id exists
└── edit existing post
```

Both operations use Inertia's `form.post()` so attachments can be submitted as multipart `FormData`.

Create:

```text
POST /posts
_method = POST
```

Update:

```text
POST /posts/{post}
_method = PUT
```

Laravel method spoofing routes the update request to the `PUT /posts/{post}` route.

See: [Post creation and editing flow](post-modal-flow.md)

---

## URL Preview Cards

Posts support saved URL preview cards for the first detected HTTP or HTTPS link in the post body.

`PostModal.vue` watches the Tiptap HTML body and waits 500 ms after changes before looking for the first URL. It checks linked `href` values first and then plain-text URLs.

When a new URL is detected, the modal requests:

```text
POST /posts/url-preview
```

The backend validates the URL, rejects hosts resolving to private or reserved IP ranges, fetches the remote page with a five-second timeout, and extracts Open Graph metadata from `og:*` meta tags.

The returned preview contains:

```text
title
description
image
```

Preview metadata and its source URL are stored on the post in:

```text
preview
preview_url
```

`preview` is stored as JSON and cast to an array by the `Post` model.

`UrlPreview.vue` is reused in both:

```text
PostModal.vue
PostItem.vue
```

If Open Graph title data exists, the component displays a preview card. If metadata is unavailable but a URL exists, it falls back to displaying the URL as a link.

See: [URL preview flow](url-preview-flow.md)

---

## Attachment Uploads

Backend attachment upload is fully implemented.

New attachments are stored under:

```text
storage/app/public/attachments/{post_id}
```

A `PostAttachment` record stores metadata including:

```text
post_id
name
path
url
mime
size
created_by
```

The controller stores uploaded files only after Laravel validation succeeds.

See: [Post attachment upload flow](attachment-flow.md)

---

## Attachment Validation

Create and update requests share the same allowed file-extension list.

Current application rules include:

```text
maximum new files per request: 10
maximum one-file size:         25 MB
maximum combined size:         90 MB
```

The combined limit is implemented through `TotalAttachmentSize`.

The allowed extension list is shared with Vue through Inertia so `PostModal.vue` can show an immediate warning when an unsupported extension is selected.

See:

- [Attachment validation flow](attachment-validation-flow.md)
- [Attachment size validation](attachment-size-validation.md)

---

## Attachment Editing

When editing a post, `PostModal.vue` displays both:

```text
existing database attachments
+
newly selected files
```

Existing files selected for removal are tracked in:

```text
deleted_file_ids
```

New files are submitted through:

```text
attachments
```

The user can also undo a pending deletion before saving.

After the database transaction succeeds, selected physical files are removed from storage.

---

## Attachment Feed Component

Attachment rendering has been extracted from `PostItem.vue` into:

```text
PostAttachments.vue
```

The component:

- renders image previews;
- renders video previews with a play indicator;
- renders other files with an attachment icon and filename;
- provides download links;
- emits the clicked attachment index;
- displays a maximum of four previews in the feed;
- displays `+X more` on the fourth preview when additional attachments exist.

Video and image detection is handled by:

```text
isImage()
isVideo()
```

in:

```text
resources/js/helpers.js
```

See: [Post attachments display flow](post-attachments-flow.md)

---

## Attachment Preview Modal

Clicking an attachment eventually opens `AttachmentPreviewModal.vue`.

The event chain is:

```text
PostAttachments.vue
    ↓ index
PostItem.vue
    ↓ post + index
PostList.vue
    ↓
AttachmentPreviewModal.vue
```

The modal receives the complete attachment array, so users can navigate to attachments that are not displayed in the four-item feed preview.

The modal also supports playable video attachments.

Videos are detected by MIME type and rendered with the native HTML video player using:

```text
controls
autoplay
playsinline
```

Images, videos, and generic files all reuse the same modal navigation flow.

See: [Attachment preview flow](attachment-preview-flow.md)

---

## Attachment Downloads

The route:

```text
GET /posts/attachments/{attachment}/download
```

uses Laravel route model binding to resolve the attachment and `Storage::disk('public')->download()` to return the physical file using its original filename.

See: [Attachment downloads and route model binding](attachment-download-route-model-binding.md)

---

## Post and Comment Reactions

The original post-only reaction implementation has been refactored into a shared polymorphic system.

The `reactions` table identifies its target using:

```text
object_id
object_type
```

Both `Post` and `Comment` define a polymorphic `reactions()` relationship.

Currently the supported reaction type is:

```text
like
```

Users can like and unlike both posts and comments without reloading the page.

See: [Reaction system flow](reaction-system-flow.md)

---

## Comments

Authenticated users can:

- create comments;
- view comments loaded with posts;
- edit their own comments;
- delete their own comments;
- like and unlike comments.

Comment creation uses:

```text
POST /posts/{post}/comments
```

The comments table also now contains a nullable self-referencing `parent_id` column to prepare the database for threaded replies.

The reply UI and reply-creation logic are not implemented yet; only the database field has been prepared.

See:

- [Post comments flow](post-comments-flow.md)
- [Comment update and delete flow](comment-management-flow.md)

---

## Groups

Poet-Web currently supports:

- group creation;
- creator membership as an approved admin;
- loading authenticated-user groups;
- client-side group search;
- slug-based group profile pages;
- cover and thumbnail uploads;
- approved-admin authorization for group image changes.

See:

- [Group loading flow](group-loading-flow.md)
- [Group profile flow](group-profile-flow.md)

---

## Following Users

Authenticated users can follow and unfollow other users from profile pages.

The system provides:

- follower count;
- current-user follow state;
- profile navigation from post authors;
- backend self-follow protection;
- application-level duplicate avoidance through `firstOrCreate()`.

See: [User follow and unfollow flow](user-follow-flow.md)

---

## Global Search

Authenticated users can search across:

```text
users
groups
posts
```

The navigation bar contains a global search field that opens:

```text
GET /search/{search?}
```

User matches are based on:

```text
name
username
```

Group matches are based on:

```text
name
about
```

Post matches are based on:

```text
body
```

Post search starts from:

```php
Post::postsForTimeline($userId)
```

so private group posts remain hidden from users who are not approved members.

Post results are paginated in groups of 20 and reuse `PostList.vue`, including its existing JSON-based infinite scrolling.

User and group results reuse:

```text
UserListItem.vue
GroupItem.vue
```

See: [Global search flow](global-search-flow.md)

Post content also supports hashtag-based search.

Hashtags inside rendered post content are converted into clickable search links by `PostItem.vue`.

For example:

```text
#poetry
```

links to:

```text
/search/%23poetry
```

Search values are URL encoded because `#` would otherwise be interpreted by the browser as a URL fragment and would not be sent to Laravel.

The same encoding is applied when searching through the navigation search input.

When the search value starts with `#`, `Search.vue` hides user and group results and displays post results only.

Hashtag searching reuses the existing global post-search query and therefore continues to use:

```php
Post::postsForTimeline($userId)
```

so private group post visibility remains enforced.

See: [Global search flow](global-search-flow.md)

---

## Documentation Principle

The current application code is the source of truth.

When a feature changes, its related documentation should be updated so route names, request methods, component responsibilities, validation limits, and database relationships remain consistent with the implementation.
