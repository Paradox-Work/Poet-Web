# Implementation Notes

## Overview

Poet-Web is implemented as a Laravel + Vue/Inertia social platform for poetry publishing and community groups.

The current implementation distinguishes between:

- public standalone publications;
- public and private group publications;
- authenticated write/interact actions;
- guest read-only browsing.

The central rule is that backend authorization, not frontend visibility, decides whether protected data is accessible.

## Public and authenticated behavior

Guests may browse:

- the public Home/Explore feed;
- public standalone posts and poems;
- public profiles and profile publications;
- public profile photo galleries sourced from standalone publications;
- group discovery and group profile metadata;
- global search.

Authentication is required for state-changing actions such as publishing, following, joining groups, commenting, reacting, editing, invitations, and moderation.

## Publication visibility

`Post::isVisibleTo(?int $userId)` defines whether a specific published post is visible.

`Post::postsForTimeline(?int $userId)` applies the same visibility model to collections:

```text
standalone published post
→ public

public group published post
→ public

private group published post
→ approved member only

draft
→ not part of public timelines
```

This query is reused by the Home feed, search, dedicated publication pages, attachment authorization, and interaction checks.

## Feed filtering

`HomeController::index()` accepts three filter concepts:

```text
feed:   poems | posts | all
source: for_you | following | groups | mine
genre:  optional poem genre
```

Guests are forced to `source=for_you`. Authenticated users can use personalized source filters.

Results are paginated in groups of 10 and JSON responses are supported for infinite loading.

See [Home feed flow](home-feed-flow.md).

## Rich text

Poem/post body content is stored as HTML. `RichTextSanitizer` is applied through the Post model body attribute so unsafe markup is sanitized at the model boundary.

The publication tests verify sanitized rich-text persistence.

## Attachments

New post attachments are stored on Laravel's private `local` disk:

```text
storage/app/private/attachments/{post_id}/...
```

They are not served through `/storage`.

`PostAttachmentResource` generates controlled application routes:

```text
GET /posts/attachments/{attachment}
GET /posts/attachments/{attachment}/download
```

`PostAttachmentController` verifies publication visibility before serving a published attachment. Draft attachments are available only to their owner.

A migration moves existing post attachments from public storage to private storage when possible.

See [Attachment upload flow](attachment-flow.md) and [Attachment routes and authorization](attachment-download-route-model-binding.md).

## Attachment validation

Creation limits each request to:

- at most 10 attachments;
- at most 25 MB per attachment;
- at most 90 MB total upload size;
- allowed types from `StorePostRequest::$extensions`.

Updates also calculate the final stored attachment set:

```text
remaining existing attachments
+ new attachments
- deleted_file_ids
```

The resulting publication may contain at most 10 attachments and at most 90 MB total.

See [Attachment size validation](attachment-size-validation.md).

## Comments and reactions

Guests can read comments attached to visible public publications.

Creating, editing, deleting, replying, and reacting require authentication.

Protected group publication visibility is rechecked before interactions. A user who previously belonged to a group cannot keep interacting with an old group post merely because they know its post/comment ID.

See [Comment update/delete flow](comment-management-flow.md).

## Profiles and privacy

Profile routes are public.

Guests can see standalone profile publications and photos. Group posts do not appear in public profile content.

`UserResource` exposes public identity fields to other viewers, while private account fields such as email and email verification state are only included for the account owner.

Profile usernames are unique at both validation and database levels.

## Account deletion

Account deletion requires the current password.

Deletion performs cleanup/anonymization, hides the user's authored publications and owned groups, replaces personally identifying account fields, then soft-deletes the user record. This preserves referential integrity while preventing the deleted account from remaining active or publicly identifiable.

See [Security and access control](security-and-access-control.md).

## Localization

The core presentation flow supports Latvian and English without a third-party frontend localization dependency.

The shared `useI18n()` composable stores the selected locale in `localStorage`, applies the language to the document, and exposes reactive translations.

Latvian is the default interface language for a new browser profile.

The presentation-facing flow is localized across navigation, authentication, Home, search, publication editing, poem writing, profiles, comments, group discovery/settings, and the About/Rights page.

## Groups

Groups support:

- creation;
- approved/pending membership;
- public/private visibility;
- automatic or manual approval;
- invitations;
- admin/member roles;
- member removal;
- group publication restrictions;
- group moderation;
- cover and thumbnail images.

Public group publications are readable by guests. Private group publications require approved membership. Visibility and membership approval are separate settings.

Image replacement stores the new file and updates the database before deleting the previous image.

## Following

Follower relationships have both application-level duplicate handling and a database uniqueness guarantee.

Users cannot follow themselves.

## Global search

Search is public and searches:

```text
users:  name, username
groups: name, about
posts:  body, title, caption
```

Hashtag searches additionally use the JSON `hashtags` field.

Post search begins with `Post::postsForTimeline($request->user()?->id)`, which prevents private group results from leaking into guest or outsider searches.

See [Global search flow](global-search-flow.md).

## Continuous integration

`.github/workflows/tests.yml` runs on pushes and pull requests.

It:

1. installs PHP dependencies;
2. prepares a test environment;
3. runs migrations;
4. installs Node dependencies;
5. builds the Vite frontend;
6. runs the Laravel test suite.

The automated suite is extended as presentation features are added; the exact current result is reported by GitHub Actions and the testing guide.

## Documentation principle

Code is the source of truth. Documentation should describe the behavior that is actually enforced by current routes, controllers, requests, models, migrations, and tests.
