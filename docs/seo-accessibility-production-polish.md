# SEO, Accessibility, Error Pages, and Product Polish

## SEO / sharing

The root Blade template supplies default:

- description;
- Open Graph site name/type/description;
- Twitter summary-card metadata.

Dedicated public publication pages override page-level metadata with:

- publication/author title;
- text description derived from caption/body;
- Open Graph article type.

No poem text is translated by Poet-Web.

## Sitemap

Public route:

```text
GET /sitemap.xml
```

includes:

- public product/legal pages;
- profiles;
- public groups;
- guest-visible standalone/public-group publications.

Private-group publications are excluded because the sitemap uses `Post::postsForTimeline(null)`.

## robots.txt

Crawler rules allow public content and discourage indexing private/account-specific areas such as:

- /admin/
- /messages/
- /drafts/
- /saved/
- /notifications/

## Accessibility

Implemented baseline improvements include:

- keyboard-accessible skip link;
- `#main-content` focus target;
- existing visible focus treatments;
- `prefers-reduced-motion` support;
- semantic labels on major controls;
- responsive layouts;
- descriptive profile/avatar alt text where available.

This is an accessibility pass, not a formal claim of complete WCAG conformance.

A full WCAG audit with automated and human assistive-technology testing would still be required before making such a claim.

## Branded error pages

Production-style Poet-Web pages exist for:

```text
403
404
419
429
500
```

They provide a clear Latvian explanation and a route back to the Home page.

When `APP_DEBUG=true`, Laravel's development exception page may intentionally replace the branded 500-style page so developers can debug.

## Loading / empty-state polish

The production-polish pass includes clearer states for:

- empty Home feeds;
- loading additional publications;
- empty bookmarks;
- empty direct-message conversations;
- empty moderation report queues.

Inertia's navigation progress indicator also uses the Poet-Web accent color.

## Public policy pages

Public routes now include:

```text
/about
/privacy
/terms
/community-guidelines
/copyright
```

These pages explain the intended product rules in readable language.

They are project/product documentation, not a substitute for jurisdiction-specific legal advice for a real commercial launch.

## Discovery sorting

Home supports deterministic, non-AI sorting:

```text
latest
popular    → reactions_count
discussed  → comments_count
```

This improves discovery without adding AI recommendations.

## Explicitly not added

The production-polish scope intentionally does NOT add:

- AI poem translation;
- AI recommendations;
- mobile application;
- subscriptions/payments.

Those remain possible future work rather than qualification-project dependencies.
