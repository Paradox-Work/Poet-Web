# Group Settings Update Flow

## Overview

Approved group administrators can update:

```text
name
about
visibility
auto_approval
```

Route:

```text
PUT /groups/{group:slug}
route: group.update
```

## Authorization

`UpdateGroupRequest::authorize()` requires:

```text
authenticated user
+
group->isAdmin(current user)
```

`Group::isAdmin()` checks both:

```text
role = admin
status = approved
```

The frontend edit form is therefore only a UX layer; backend authorization remains authoritative.

## Validation

```text
name
→ required string max 255

auto_approval
→ required boolean

visibility
→ required string
→ public | private

about
→ nullable string max 5000
```

The same visibility values are accepted during group creation.

## Visibility

```text
public
→ published group content can be read without membership

private
→ group posts/members/photos require approved membership
```

Changing visibility affects the shared publication visibility query immediately.

## Automatic approval

```text
true
→ join request becomes approved immediately

false
→ join request becomes pending and waits for admin
```

Changing `auto_approval` does not itself alter existing member statuses.

## Slug behavior

The group slug is generated from the name when the group is created.

The model uses:

```text
doNotGenerateSlugsOnUpdate()
```

Therefore changing the display name does not unexpectedly change existing group URLs.

## Frontend

`Group/View.vue` initializes the Inertia settings form from current group data:

```text
name
auto_approval
visibility
about
```

The administrator can switch public/private visibility and automatic/manual approval independently.

The settings portion is localized in the Latvian/English presentation flow.

## Main files

```text
app/Http/Requests/StoreGroupRequest.php
app/Http/Requests/UpdateGroupRequest.php
app/Http/Controllers/GroupController.php
app/Models/Group.php
resources/js/Components/app/GroupModal.vue
resources/js/Pages/Group/View.vue
database/migrations/2026_10_14_000004_add_visibility_to_groups_table.php
routes/web.php
```
