## Attachment Preview Interaction

The attachment preview uses a full-screen modal for displaying post attachments.

The modal separates the screen into two conceptual areas:

```text
full-screen backdrop
└── transparent preview wrapper
    ├── previous attachment control
    ├── attachment
    ├── next attachment control
    └── close control
```

### Backdrop Closing

The full-screen `DialogPanel` handles clicks on its own background using:

```vue
@click.self="closeModal"
```

The `.self` modifier is important because the modal should close only when the user clicks the empty area surrounding the attachment.

Therefore:

```text
click empty backdrop
→ close preview

click attachment
→ remain open

click previous/next control
→ change attachment

click close control
→ close preview
```

This allows the unused space surrounding the attachment to act as an additional and intuitive way to exit the preview.

Headless UI's `Dialog` also continues to handle normal dialog closing behavior through its `@close` event.

### Preview Wrapper

The attachment is placed inside a relatively positioned transparent wrapper.

The wrapper provides space for controls around the attachment without requiring those controls to be positioned relative to the entire browser viewport.

Conceptually:

```text
transparent preview wrapper

        [ close ]

[ previous ] [ attachment ] [ next ]
```

This keeps navigation and close controls visually associated with the attachment while leaving the surrounding full-screen area available as the closeable backdrop.

### Attachment Sizing

Images use maximum viewport-relative dimensions and `object-contain`.

This allows the complete image to remain visible without stretching or cropping it.

Conceptually:

```text
large image
↓
scale down to available viewport space
↓
preserve aspect ratio
↓
display complete image
```

The attachment therefore remains centered while adapting to different screen and image dimensions.

### Navigation Controls

Previous and next controls are displayed only when a corresponding attachment exists.

```text
first attachment
→ no previous control

middle attachment
→ previous + next controls

last attachment
→ no next control
```

The current attachment index is managed through the existing `v-model:index` flow.

Changing the index updates the computed current attachment without closing or recreating the preview.

### Close Control

The close control is positioned relative to the preview wrapper rather than the outer viewport.

This keeps the close action visually associated with the displayed content instead of placing it at the far corner of the browser window.

The control uses a high-contrast background and white icon so it remains visible over attachments with different colors and brightness levels.

### Result

The attachment viewer supports several intuitive interactions:

```text
click backdrop
→ close

click X
→ close

click previous
→ previous attachment

click next
→ next attachment

Escape / dialog close event
→ close
```

The updated layout reduces unused modal space, keeps controls close to the attachment, and makes the full-screen preview behave more like a conventional image gallery.

---

## Profile Photo Gallery Reuse

The same attachment preview modal is also reused by the user-profile and group-profile photo galleries.

`TabPhotos.vue` keeps:

```text
currentPhotoIndex
showModal
```

and passes the complete visible photo array into:

```text
AttachmentPreviewModal.vue
```

When a gallery image is clicked:

```text
click image
→ set selected photo index
→ open AttachmentPreviewModal
→ preview selected image
```

Because the modal already supports previous and next navigation, users can move through the full visible gallery without leaving the profile page.

The photo grid also exposes the existing attachment download route while stopping the download click from opening the preview modal.
