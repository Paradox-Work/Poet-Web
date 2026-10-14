# Group Discovery and Loading Flow

## Overview

Groups are loaded through the dedicated groups route:

```text
GET /groups
route: group.index
```

The page supports both guests and authenticated users.

The current implementation no longer depends on a Home-page sidebar for the main group discovery flow.

## Controller

```text
app/Http/Controllers/GroupController.php
GroupController::index()
```

The controller reads:

- the optional authenticated user;
- an optional search string;
- the current group-discovery page.

The search string is trimmed and capped at 80 characters.

## Authenticated user's groups

For an authenticated user, `joinedGroups` contains groups where the user has:

```text
group_users.user_id = current user
status = approved
```

Each group also loads:

- current user's membership;
- approved member count.

The result is ordered by group name.

Guests receive an empty `joinedGroups` collection.

## Discover groups

`discoverGroups` contains groups available for browsing.

For authenticated users, groups where the user is already an approved member are excluded from discovery.

For guests, all non-deleted groups may appear in discovery.

Search matches:

```text
groups.name
groups.about
```

The result is paginated:

```text
12 groups per page
page parameter: groups_page
```

## Public/private status

Group discovery exposes the group's:

```text
visibility = public | private
```

as metadata.

This lets the interface clearly distinguish:

- a public group whose publications can be read by visitors;
- a private group whose protected content requires approved membership.

Group metadata itself remains browseable so a user can decide whether to request membership.

## Membership state

When the current-user relationship is loaded, the group summary can expose:

```text
status
role
```

Examples:

```text
approved + admin
approved + member
pending + member
```

## Resource

`GroupSummaryResource` is used for discovery/list cards.

Important fields include:

```text
id
name
slug
status
role
thumbnail_url
cover_url
visibility
auto_approval
about
description
member_count
```

## Frontend

```text
resources/js/Pages/Group/Index.vue
```

The page provides:

- Your groups rail;
- group discovery grid;
- group search;
- member counts;
- public/private badge;
- join/request action;
- pagination;
- new-group action for authenticated users.

Guests can browse groups but are sent to login when they try to join.

## Group creation

The group modal sends:

```text
name
about
auto_approval
visibility
```

to:

```text
POST /groups
route: group.create
```

The creator is inserted into `group_users` as:

```text
status = approved
role = admin
```

After creation, the groups page reloads only the required Inertia properties.

## Main files

```text
app/Http/Controllers/GroupController.php
app/Http/Resources/GroupSummaryResource.php
app/Models/Group.php
resources/js/Pages/Group/Index.vue
resources/js/Components/app/GroupModal.vue
routes/web.php
```
