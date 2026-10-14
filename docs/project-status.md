# Poet-Web Current Project Status

This document is the high-level technical snapshot of Poet-Web after the qualification-project hardening and presentation-polish work.

## Product purpose

Poet-Web is a Laravel + Vue/Inertia platform for:

- publishing poems and normal posts;
- storing drafts;
- following authors;
- comments, replies, and reactions;
- poetry-focused profiles;
- groups and group membership;
- private and public group publishing;
- attachments;
- search;
- notifications;
- guest browsing;
- saved publications;
- one-to-one direct messaging;
- reporting/site moderation;
- collaborative poem drafts;
- non-AI discovery sorting;
- public legal/community pages;
- sitemap/social metadata;
- branded production error pages.

The current interface is Latvian-first with an English alternative for the main presentation flow.

## Main stack

```text
Backend:        Laravel 13 / PHP
Frontend:       Vue 3 + Inertia
Styling:        Tailwind CSS
Database:       MySQL in Sail development
Containers:     Docker / Laravel Sail
Build:          Vite
Authentication: Laravel Breeze
Editor:         Tiptap
Testing:        Pest / PHPUnit
CI:             GitHub Actions
```

## Access model

### Guest

A guest may:

- browse the Home/Explore feed;
- search;
- open public profiles;
- open standalone public publications;
- open public group publications;
- browse group discovery and group metadata;
- read comments attached to visible publications;
- view/download attachments belonging to visible published content.

A guest may not perform state-changing actions such as:

- publishing;
- following;
- joining groups;
- commenting;
- reacting;
- editing;
- moderating.

### Authenticated user

An authenticated user can additionally:

- create posts and poems;
- maintain drafts;
- follow/unfollow users;
- comment and reply;
- react;
- join/request to join groups;
- accept/decline invitations;
- update their profile;
- save visible publications;
- exchange direct messages;
- report visible users/posts/comments;
- collaborate on drafts explicitly shared with them.

### Approved group member

An approved group member can publish into that group.

For a private group, approved membership is also required to read its publications, members, and group photos.

### Group administrator

An approved administrator can:

- update group settings;
- change public/private visibility;
- change automatic/manual approval;
- update group images;
- invite users;
- approve/reject join requests;
- promote/demote members;
- remove members;
- moderate group publications;
- pin group publications.

## Publication visibility

`Post::isVisibleTo(?int $userId)` and `Post::postsForTimeline(?int $userId)` are the central visibility rules.

```text
standalone published post
→ public

public-group published post
→ public

private-group published post
→ approved group member only

draft
→ owner-only through draft-specific flows
```

The same visibility boundary is reused for:

- Home;
- search;
- dedicated publication pages;
- attachments;
- reactions;
- comments;
- comment reactions;
- comment editing/deletion.

## Groups

Groups have two independent settings:

```text
visibility
→ public | private

auto_approval
→ true | false
```

This means a group can be:

- public + automatic approval;
- public + manual approval;
- private + automatic approval;
- private + manual approval.

Visibility controls who can read group content.

Automatic approval controls how a user becomes an approved member.

Publishing into a group always requires approved membership.

## Direct messaging

Authenticated users can start one-to-one private conversations from another user's profile.

Conversation access is participant-only. Open conversations poll the message endpoint every five seconds, providing live updates without requiring a websocket server.

Unread state is tracked with `conversation_user.last_read_at`.

See [Direct messaging](direct-messaging-flow.md).

## Saved reading list

Authenticated users can bookmark visible publications.

Bookmark access reuses publication visibility, so private-group content cannot be saved by an outsider through a guessed ID.

See [Bookmarks / reading list](bookmarks-reading-list.md).

## Reporting and site moderation

Authenticated users can report posts, comments, and users for defined reasons.

A separate `users.is_admin` site role protects the moderation dashboard. Administrators can review reports, dismiss them, or remove reported posts/comments.

The dashboard also shows basic product metrics.

See [Reporting and moderation](reporting-and-moderation.md).

## Publication system

Publications support:

- normal posts;
- poems;
- rich-text body content;
- poem title;
- caption;
- poem form;
- poem genres;
- hashtags;
- general/mature content rating;
- attachments;
- draft/autosave workflow;
- explicit shared draft collaborators;
- editing;
- soft deletion;
- pinning.

Rich-text body HTML is sanitized through `RichTextSanitizer`.

## Attachments

New publication attachments are stored on Laravel's private `local` disk.

They are served through:

```text
GET /posts/attachments/{attachment}
GET /posts/attachments/{attachment}/download
```

The controller checks the parent publication visibility before returning bytes.

Draft attachments require the draft owner.

Published public content can expose attachments to guests; private-group attachments require approved membership.

## Comments and reactions

Comments support:

- top-level comments;
- nested replies through `parent_id`;
- editing;
- deletion;
- comment reactions;
- recursive frontend rendering.

Reactions use a polymorphic `reactions` table and currently support the `like` reaction for both posts and comments.

Protected interactions always recheck publication visibility on the backend.

## Profiles and following

Profiles are public.

They support:

- cover image;
- avatar;
- public standalone publications;
- public photo gallery;
- followers/following;
- follow/unfollow;
- pinned standalone publication;
- owner-only profile settings.

Private account fields such as email are not exposed to other viewers.

Usernames are unique at both validation and database levels.

## Discovery

Home can sort visible content without AI:

```text
latest
popular (reaction count)
discussed (comment count)
```

No AI recommendation or AI translation system is part of the current scope.

## Search

Global search covers:

- user name/username;
- group name/about text;
- visible publication body/title/caption;
- hashtags when the search begins with `#`.

Users, groups, and publications have independent pagination parameters.

Publication search starts from the shared visibility query so private-group content cannot leak through search.

## Account deletion

Account deletion:

- requires the current password;
- removes active relationships that should not survive;
- hides authored publications;
- hides groups owned by the user;
- anonymizes identifying account fields;
- soft-deletes the user;
- invalidates the session.

The soft-delete approach preserves foreign-key integrity for historical relationships.

## Production web polish

Poet-Web now also includes:

- public Privacy/Terms/Community Guidelines/Copyright pages;
- sitemap.xml and robots.txt;
- publication-level Open Graph metadata;
- branded 403/404/419/429/500 pages;
- keyboard skip navigation;
- reduced-motion support;
- improved loading/empty states.

See [SEO, accessibility, and production polish](seo-accessibility-production-polish.md).

## Localization

The main presentation flow is bilingual:

```text
default: Latvian
alternative: English
```

The language preference is stored in browser `localStorage`.

The current translation layer is a lightweight custom Vue composable, not `vue-i18n`.

See [Localization flow](localization-flow.md).

## About / author-rights page

The public About page explains:

- why Poet-Web exists;
- author-rights intent;
- guest/authenticated/private-group access levels;
- technology choices.

See [About and rights flow](about-rights-flow.md).

## Presentation demo data

`PresentationDemoSeeder` creates a reproducible local demo environment with:

- Latvian users;
- poems and posts;
- comments/replies;
- reactions;
- follows;
- public/private groups;
- approved members;
- pending membership.

See [Presentation demo guide](presentation-demo-guide.md).

## Quality checkpoint

The current main qualification build is verified by GitHub Actions with:

```text
82 tests passed
399 assertions
0 failures
```

The same workflow also verifies:

- database migrations;
- `npm ci`;
- Vite production build.

See [Testing and quality assurance](testing-and-quality-assurance.md).
