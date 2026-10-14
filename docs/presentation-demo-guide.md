# Qualification Presentation Demo Guide

## Goal

This guide prepares a short, reliable Poet-Web demonstration for the qualification presentation. It focuses on functionality that is easy to explain and is backed by authorization rules and automated tests.

## Prepare demo data

Use the dedicated local-only seeder:

```bash
./vendor/bin/sail artisan db:seed --class=PresentationDemoSeeder
```

The seeder creates Latvian demo users, publications, comments, reactions, follows, public/private groups, approved memberships, and a pending join request.

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

### 6. Real-platform features

For a stronger technical demonstration, briefly show two or three rather than every new feature:

- Save a poem and open the Saved reading list;
- open another writer's profile and start a direct message;
- share a poem draft with a collaborator;
- report a demo publication and open the moderation queue as Elza;
- change Home sorting between newest, most liked, and most discussed.

Elza from the demo seeder is a site administrator.

Presentation point:

> Poet-Web now includes the surrounding product features expected from a real user-generated-content platform: private messaging, saved reading, moderation, collaboration, discovery, policy pages, and production error/SEO behavior.

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
11. Real-platform features: messages / bookmarks / moderation
12. Testing evidence
13. Live demonstration
14. Conclusions

The live demo should carry more weight than additional filler slides.
