# Discovery, SEO, Sharing, and Accessibility

## Discovery sorting

Home/Explore supports:

```text
newest
trending
popular
discussed
```

### Newest

Normal reverse chronological order.

### Popular

Orders primarily by reaction count.

### Most discussed

Orders primarily by comment count.

### Trending

Uses reaction count, then comment count, then recency.

This is transparent popularity/discussion sorting. It is not an AI or personalized recommendation engine.

All sorting occurs after the shared publication visibility query, so a ranking option cannot expose private-group content.

## Public sharing metadata

Public publication pages include:

- page title;
- description;
- Open Graph title;
- Open Graph description;
- Open Graph URL;
- Open Graph image when available;
- Twitter/X large-card metadata.

Profiles and groups include similar Open Graph metadata.

A private group's publication is still inaccessible to a guest/crawler, so metadata rendering does not create another privacy path.

## Sitemap

Public route:

```text
GET /sitemap.xml
```

The sitemap includes:

- Home;
- groups index;
- About/legal pages;
- public user profiles;
- public group profiles;
- standalone public publications;
- public-group publications.

Private-group publications are not added.

## robots.txt

Public route:

```text
GET /robots.txt
```

It advertises the sitemap and disallows crawling account/private-work areas such as:

- admin;
- messages;
- library;
- drafts;
- collaborations;
- report flows.

## Accessibility improvements

The shared layout includes:

- skip-to-content link;
- keyboard-focusable main content;
- descriptive hamburger state with `aria-expanded`;
- visible focus utilities;
- Inertia navigation status;
- `aria-live` messaging/flash regions.

The stylesheet honors:

```css
@media (prefers-reduced-motion: reduce)
```

by minimizing transitions/animations.

Existing forms use labels or screen-reader labels in key presentation flows.

This should be described as an accessibility improvement pass, not a formal WCAG compliance certification.

## Loading and feedback

Global navigation shows a thin loading indicator.

Session flash success/error messages are surfaced by the shared layout with an `aria-live` region.

Feature pages also include intentional empty states for:

- no conversations;
- no messages;
- no bookmarks;
- no reading lists;
- no collaboration invites;
- no reports.

## Main files

```text
app/Http/Controllers/HomeController.php
app/Http/Controllers/SitemapController.php
resources/js/Pages/Home.vue
resources/js/Pages/Post/View.vue
resources/js/Pages/Profile/View.vue
resources/js/Pages/Group/View.vue
resources/js/Layouts/AuthenticatedLayout.vue
resources/views/app.blade.php
resources/css/app.css
tests/Feature/DiscoveryAndPublicPagesTest.php
routes/web.php
```
