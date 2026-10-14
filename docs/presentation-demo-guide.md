# Qualification Presentation Demo Guide

## Goal

This guide prepares a short, reliable Poet-Web demonstration for the qualification presentation. It focuses on functionality that is easy to explain and is backed by authorization rules and automated tests.

## Prepare demo data

Use the dedicated local-only seeder:

```bash
./vendor/bin/sail artisan db:seed --class=PresentationDemoSeeder
```

The seeder creates Latvian demo users, publications, comments, reactions, follows, public/private groups, approved memberships, a pending join request, bookmarks/reading-list data, a direct-message conversation, an accepted poem collaboration, and a pending site-moderation report. Elza is the demo site administrator.

Local demo accounts:

```text
elza@poetweb.lv   / Demo1234!
janis@poetweb.lv  / Demo1234!
marta@poetweb.lv  / Demo1234!
```

These credentials are only for a local demo environment.

## Recommended live-demo order

### 1. Guest experience

Start logged out.

Show:

- Latvian interface;
- Home/Explore;
- public standalone poems;
- search;
- public profile;
- public group;
- public group publication.

Then open the private group "Klusie Melnraksti" and show that its publication content is unavailable to the guest.

Presentation point:

> Poet-Web supports useful guest browsing without exposing private group content.

### 2. Language switch

Use the LV / EN control in the navigation.

Show that the chosen language persists between pages.

Presentation point:

> The application supports Latvian as its default presentation language and English as an alternative interface language.

### 3. Authentication and publishing

Log in as:

```text
elza@poetweb.lv
Demo1234!
```

Show:

- profile;
- follow state;
- writing studio;
- poem title/form/genres;
- autosave;
- publishing;
- content rating;
- hashtags.

Presentation point:

> Poet-Web separates reading from authoring. Publishing actions require authentication.

### 4. Public and private groups

Open:

```text
Cēsu Dzejas Telpa
```

It is public, so its publications are readable without membership.

Open:

```text
Klusie Melnraksti
```

It is private, so publications require approved membership.

Show group settings as an administrator and point out:

- visibility;
- automatic/manual membership approval;
- admin/member roles.

Presentation point:

> Group visibility and membership are independent controls: a group can be public/private and can also use automatic/manual member approval.

### 5. Interaction

Show:

- comment;
- reply;
- reaction;
- follow/unfollow.

Mention that the backend checks visibility before protected interactions.

### 6. Production-style extras (optional 60–90 seconds)

If presentation time allows, show only a few of these rather than turning the demo into a feature list:

- Library → bookmarked poem + reading list;
- Messages → existing Elza/Marta conversation;
- Collaborations → shared poem;
- report action → admin moderation queue;
- Home sorting → Newest / Trending / Most liked / Most discussed;
- Community Guidelines or Copyright page.

Presentation point:

> The core qualification features are supported by practical platform features such as personal collections, messaging, shared editing, and site-wide moderation.

For messaging, describe the current implementation accurately:

> The active conversation refreshes automatically through short polling.

Do not claim WebSockets when presenting this version.

### 7. Testing evidence

Run:

```bash
./vendor/bin/sail artisan test
```

Show the green result.

Also mention GitHub Actions runs:

- migrations;
- Vite production build;
- Laravel tests.

Presentation point:

> The project is not validated only by clicking through the interface; authorization and validation behavior is covered by automated tests.

## Backup plan

Before the presentation:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm ci
./vendor/bin/sail npm run build
./vendor/bin/sail artisan test
```

If the demo database becomes messy, use a disposable local database and reseed the presentation data.

## Slides that match the live demo

A compact presentation can use:

1. Problem / motivation
2. Goal
3. Main requirements
4. Alternatives
5. Technology choices
6. System architecture
7. ER diagram
8. Access model: guest / user / group member / admin
9. Public vs private groups
10. Security and validation
11. Testing evidence
12. Live demonstration
13. Conclusions / future work

The live demo should carry more weight than additional filler slides.
