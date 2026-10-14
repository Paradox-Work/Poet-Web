# About and Author-Rights Page

## Overview

Poet-Web includes a public About page:

```text
GET /about
route: about
```

The page can be opened by guests and authenticated users.

Its purpose is to make the project's motivation and rights model visible inside the application instead of existing only in qualification documentation.

## Content

The page contains four main sections.

### Why Poet-Web?

Explains the project motivation:

- poetry is often stored across disconnected locations;
- generic social platforms are not designed specifically for poets;
- Poet-Web combines publishing, storage, community, and discussion.

### Author rights

The application states that authors retain rights to the work they submit.

The page does not claim that Poet-Web acquires ownership simply because a work is published on the platform.

### Access levels

The page summarizes:

```text
guest
→ read public content

authenticated user
→ publish and interact

private-group member
→ access protected group content
```

### Technology

The page names the core implementation stack:

- Laravel;
- Vue;
- Inertia;
- Tailwind CSS;
- MySQL;
- Docker/Sail;
- automated tests and CI.

## Localization

All About-page content is provided through the shared Latvian/English translation layer.

This makes it suitable for the live qualification demonstration.

## Main files

```text
routes/web.php
resources/js/Pages/About.vue
resources/js/i18n.js
```

## Qualification value

The page gives the presentation a direct connection between:

```text
problem
→ project goal
→ implemented product
→ rights/access decisions
```

It is especially useful when explaining why Poet-Web is more than a generic social-feed clone.
