# Dedicated Publication Page Flow

## Overview

Every visible published post/poem has a dedicated route:

```text
GET /posts/{post}
route: post.view
```

The route is public, but the requested publication still passes the shared backend visibility policy.

## Visibility

`PostController::view()` re-queries through:

```php
Post::postsForTimeline($request->user()?->id)
```

and then selects the requested ID.

Therefore:

```text
standalone published publication
→ guest-accessible

public-group publication
→ guest-accessible

private-group publication
→ approved member only

draft
→ unavailable through this public published-post route
```

Knowing a numeric publication ID does not bypass these rules.

## Controller flow

```text
GET /posts/{post}
→ route model binding
→ read optional current user
→ postsForTimeline(user)
→ filter requested ID
→ 404 if invisible
→ PostResource
→ Post/View.vue
```

## Shared resource/component behavior

The dedicated page reuses the normal publication UI.

It can therefore display:

- rich-text body;
- poem metadata;
- author/group;
- attachments;
- reaction count;
- comments/replies;
- owner/admin management controls.

Guests can read visible content, but authenticated-only interaction controls still route through protected endpoints.

## Attachments

Attachment preview/download URLs are authorization-controlled application routes rather than direct storage URLs.

A public publication attachment may therefore be opened by a guest while a private-group attachment remains protected.

## Sharing

Visible publication URLs can be copied/shared.

The recipient's own access is checked when the URL is opened.

```text
share URL
→ recipient opens
→ backend visibility rule
→ content OR 404
```

## Notifications

Content notifications can link to the dedicated publication route so users land on the affected content directly.

A soft-deleted publication is no longer available through normal route-model binding.

## Main files

```text
app/Http/Controllers/PostController.php
app/Models/Post.php
app/Http/Resources/PostResource.php
resources/js/Pages/Post/View.vue
resources/js/Components/app/PostItem.vue
resources/js/Components/app/PostModal.vue
resources/js/Components/app/AttachmentPreviewModal.vue
resources/js/Components/app/EditDeleteDropdown.vue
routes/web.php
```
