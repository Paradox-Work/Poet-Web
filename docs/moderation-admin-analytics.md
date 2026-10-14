# Reporting, Moderation, and Site Administration

## Overview

Poet-Web has two different moderation levels:

```text
group administration
→ manages one writing group

site administration
→ reviews platform-wide abuse reports and platform metrics
```

The site-wide role is represented by:

```text
users.is_site_admin
```

It is not inferred from group roles.

## Promoting a site administrator

For an existing account:

```bash
./vendor/bin/sail artisan poet:make-admin user@example.com
```

This sets:

```text
is_site_admin = true
suspended_at = null
```

The presentation demo seeder configures Elza as the demo administrator.

## User reports

Authenticated users can report:

- publications;
- comments;
- other users.

Supported reasons:

```text
spam
harassment
copyright
inappropriate
impersonation
other
```

A pending duplicate report from the same reporter for the same target is not duplicated.

## Visibility protection

A publication/comment may only be reported if the reporter is allowed to see its parent publication.

This is important for private groups:

```text
private post ID known by outsider
→ report target lookup still checks visibility
→ 404
```

Users cannot report their own user account through the user-report target.

## Admin dashboard

Route:

```text
GET /admin
```

Only `is_site_admin = true` accounts may open it.

The dashboard exposes high-level metrics:

- total active users;
- suspended users;
- published publications;
- groups;
- comments;
- direct conversations;
- direct messages;
- bookmarks;
- pending reports;
- new users in the last 7 days;
- publications in the last 7 days.

This is operational/admin analytics, not user profiling or recommendation scoring.

## Report actions

The report queue supports:

```text
dismiss
remove_content
suspend_user
note_only
```

Removing a reported publication uses soft deletion and records the administrator ID in `deleted_by`.

Reported comments can also be removed.

Suspension resolves the reported content/user back to the relevant author when possible.

A site administrator is protected from accidental suspension through this report action.

## Suspensions

A suspended account:

- cannot log in;
- is logged out when trying to use protected authenticated routes;
- can later be restored by a site administrator.

The dashboard displays recent suspended users and provides an unsuspend action.

## Reporting UI

Report forms are available for:

- posts through the content menu;
- comments through the comment menu;
- users from profile pages.

The form supports a reason plus optional details.

## Main files

```text
app/Models/Report.php
app/Http/Controllers/ReportController.php
app/Http/Controllers/AdminController.php
app/Http/Middleware/EnsureUserNotSuspended.php
app/Http/Requests/Auth/LoginRequest.php
database/migrations/2026_10_14_000007_create_moderation_tables.php
resources/js/Pages/Report/Create.vue
resources/js/Pages/Admin/Dashboard.vue
resources/js/Components/app/EditDeleteDropdown.vue
tests/Feature/ModerationTest.php
routes/console.php
routes/web.php
```
