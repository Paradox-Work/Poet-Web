# Reporting, Moderation, and Site Administration

## Overview

Poet-Web now has two moderation layers:

1. group administrators moderate content within their own groups;
2. site administrators review reports across the whole platform.

Site administration is intentionally separate from group roles.

## Site-admin flag

`users.is_admin` identifies a site administrator.

The flag is only exposed through `UserResource` when a user receives their own authenticated user resource.

A local/deployment administrator can grant access with:

```bash
php artisan app:make-admin <username>
```

With Sail:

```bash
./vendor/bin/sail artisan app:make-admin <username>
```

Remove access with:

```bash
./vendor/bin/sail artisan app:make-admin <username> --remove
```

The presentation demo seeder marks the Elza demo account as a site administrator.

## Submitting a report

Authenticated route:

```text
POST /reports
route: reports.store
```

Supported targets:

```text
post
comment
user
```

Supported reasons:

```text
spam
harassment
copyright
inappropriate
impersonation
other
```

Optional details are limited to 2000 characters.

## Visibility protection

Posts/comments must be visible to the reporter before a report is accepted.

This prevents the report endpoint from becoming a side channel for discovering protected private-group content.

User reports require an existing user.

A reporter has one active report row per target. Re-submitting updates the same record rather than generating duplicates.

## Moderation dashboard

Route:

```text
GET /admin/moderation
route: admin.moderation
```

The backend explicitly checks:

```text
current user is_admin = true
```

Non-admins receive 403.

The dashboard shows useful product metrics:

- users;
- published posts;
- groups;
- comments;
- pending reports;
- messages;
- bookmarks.

It also shows the report queue and a link to the reported target when the target still exists.

## Resolving reports

Site administrators may:

```text
dismiss
remove_content
```

For reported posts/comments, `remove_content` deletes the target.

Post moderation records the acting administrator in `deleted_by` before soft deletion.

Reports are then marked:

```text
dismissed
actioned
```

and store:

- reviewed_by;
- reviewed_at.

User reports can currently be reviewed/dismissed, but the dashboard does not implement account suspension/banning. That is intentionally left as a possible future moderation extension.

## Frontend reporting

The reusable `ReportButton.vue` is integrated into:

- publications;
- comments;
- other users' profiles.

## Main files

```text
database/migrations/2026_10_14_000005_add_site_admin_to_users_table.php
database/migrations/2026_10_14_000008_create_reports_table.php
app/Models/Report.php
app/Http/Controllers/ReportController.php
app/Http/Controllers/ModerationController.php
app/Console/Commands/MakeSiteAdmin.php
resources/js/Components/app/ReportButton.vue
resources/js/Pages/Admin/Moderation.vue
routes/web.php
```
