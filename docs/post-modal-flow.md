# Post Creation and Editing Flow

## Overview

`PostModal.vue` is reused for both creating and editing posts.

The presence of a post ID determines which operation is performed.

Both operations submit through Inertia's `form.post()` so attachments can be sent as multipart `FormData`.

For updates, Poet-Web uses Laravel method spoofing by including:

```text
_method = PUT
```

inside the submitted form data.

---

## Mode Selection

```text
post.id is null
└── create mode

post.id exists
└── edit mode
```

When the modal opens, its watcher copies the selected post state into the Inertia form:

```text
form.id
form.body
form.deleted_file_ids
form.attachments
form._method
```

New attachment state and previous validation errors are reset whenever another post is opened.

---

## Request Flow

```mermaid
flowchart TD
    A[PostModal]
    B{Does post have an ID?}
    C[Create mode]
    D[Edit mode]
    E[TiptapEditor]
    F[Collect newly selected attachments]
    G[form.post]
    H[POST /posts]
    I[Set _method = PUT]
    J[form.post]
    K[POST /posts/{post} with method spoofing]
    L[Laravel resolves as PUT /posts/{post}]
    M[StorePostRequest]
    N[UpdatePostRequest]
    O[PostController store]
    P[PostController update]

    A --> B
    B -- No --> C
    B -- Yes --> D
    C --> E
    D --> E
    E --> F
    C --> G
    G --> H
    H --> M
    M --> O
    D --> I
    I --> J
    J --> K
    K --> L
    L --> N
    N --> P
```

---

## Why `form.post()` Is Used for Updates

A normal JSON-style update could use a direct PUT request, but Poet-Web also supports file uploads.

The modal therefore submits multipart form data and uses Laravel's method spoofing for the update operation.

The current code performs:

```js
if (form.id) {
    form._method = 'PUT';

    form.post(
        route('post.update', form.id),
        options
    );
} else {
    form._method = 'POST';

    form.post(
        route('post.create'),
        options
    );
}
```

So the browser-level requests are:

```text
Create:
POST /posts
_method = POST

Update:
POST /posts/{post}
_method = PUT
```

Laravel interprets the update request as:

```text
PUT /posts/{post}
```

which matches the existing route.

---

## Rich Text Body

Both create and edit modes use the same:

```text
TiptapEditor.vue
```

through:

```vue
<TiptapEditor
    v-model="form.body"
/>
```

The post body is stored as HTML.

---

## URL Preview State

The modal also manages URL preview state:

```text
form.preview
form.preview_url
```

When an existing post is opened for editing, both values are copied from the post resource together with the body and attachment state.

The modal watches `form.body`. After a 500 ms debounce, it searches the Tiptap HTML for the first HTTP or HTTPS URL.

Detection checks:

```text
1. href="https://..."
2. plain-text https://...
```

If no URL remains in the body, both preview fields are cleared.

If a different URL is found, `PostModal.vue` requests preview metadata from:

```text
POST /posts/url-preview
```

and renders the result through:

```text
UrlPreview.vue
```

The preview fields are submitted with the same create or update form as the post body and attachments.

See: [URL preview flow](url-preview-flow.md)

---

## Attachment State

The modal supports:

```text
existing attachments
newly selected attachments
attachments marked for deletion
```

New files are stored locally in:

```text
attachmentFiles
```

Before submit, the underlying `File` objects are assigned to:

```text
form.attachments
```

Existing attachment IDs selected for removal are stored in:

```text
form.deleted_file_ids
```

---

## FormData

The request options use:

```js
forceFormData: true
```

so Inertia sends multipart data suitable for uploaded files.

The same modal can therefore submit both text and attachments without maintaining separate create/edit forms.

---

## Validation Errors

If Laravel validation fails, `onError` passes the errors to:

```text
processErrors()
```

Per-file attachment errors are mapped back to newly selected files, while array-level errors remain available through:

```text
form.errors.attachments
```

The modal stays open so the user can correct the submission.

---

## Successful Submission

When the request succeeds:

```js
onSuccess: () => {
    closeModal();
}
```

The modal closes and resets its local form/attachment state.

---

## Purpose

Using one modal for both operations avoids maintaining separate post editors.

The current architecture is:

```text
PostModal.vue
├── TiptapEditor
├── create state
├── edit state
├── attachment selection
├── attachment deletion state
├── validation display
└── multipart submission

Create
└── POST /posts

Update
└── POST /posts/{post} + _method=PUT
    └── Laravel PUT route
```
