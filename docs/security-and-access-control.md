# Security and Access Control

## Purpose

This document summarizes the authorization and data-protection rules that were hardened for the qualification-project review.

## Core publication visibility rule

Published standalone content is public.

Published group content follows the group's visibility setting:

```text
public group
→ readable by guests and authenticated users

private group
→ approved members only
```

Drafts are never part of public timelines. A draft may be edited by its owner or an explicitly attached collaborator, but publishing remains owner-controlled.

The rule is represented by:

```php
Post::isVisibleTo(?int $userId)
Post::postsForTimeline(?int $userId)
```

The same rule is reused instead of reimplementing access decisions independently in each feature.

## Where visibility is enforced

Publication visibility is applied to:

- Home feed;
- global search;
- dedicated publication pages;
- attachment view/download;
- post reactions;
- comment creation;
- comment reactions;
- comment editing;
- comment deletion.

This prevents ID guessing or stale URLs from bypassing group membership rules.

## Guest permissions

Guests may read public content:

- Home/Explore;
- standalone publications;
- public profiles;
- standalone profile photos;
- group discovery/profile metadata;
- search results;
- public comments;
- standalone-publication attachments;
- public-group publications and their authorized attachments.

Guests may not perform write actions.

## Group security

Group visibility and membership approval are separate controls.

```text
visibility = public
→ publication reading does not require membership

visibility = private
→ publication reading requires approved membership

auto_approval = true/false
→ controls how a user becomes a member
```

Publishing into any group still requires approved membership.

Tests cover:

- pending membership vs approved membership;
- only admins resolving join requests;
- only approved members publishing into groups;
- group admins moderating group publications;
- invitation ownership;
- invitation expiry;
- non-admin invitation denial;
- outsiders being unable to view/interact with protected content.

A former group member loses interaction access even if they know an old post or comment ID.

## Direct-message security

Conversation routes require authentication and then independently verify participant membership.

A non-participant receives 404 even when they know a conversation ID.

Message bodies are limited to 5000 characters and whitespace-only messages are rejected.

## Bookmark security

Bookmark toggles first verify that the publication is visible to the current user.

Saved-page queries begin from the same visibility query, so losing access to protected content removes it from the returned reading list.

## Reporting and site-admin security

Post/comment reports require visibility of the target.

Site moderation is protected by a separate `users.is_admin` flag, not by group-admin status.

The moderation controller rechecks site-admin status on both dashboard and resolution actions.

## Collaborative-draft security

A collaborator must be explicitly listed in `post_collaborators`.

Only the draft owner can add/remove collaborators and publish the draft.

For group drafts, collaborators must already be approved members of that group.

Collaborators cannot change a shared draft's existing group/type scope during autosave.

## Attachment security

Post attachments are stored privately and returned only through controlled Laravel routes.

Draft attachments require ownership.

Published attachment access follows parent-publication visibility.

## Profile privacy

Public profile API resources omit private account fields such as email and verification timestamp.

Those fields are included only when the authenticated user is requesting their own user resource.

## Username integrity

Usernames are protected by:

1. profile validation using a unique rule;
2. a database unique index.

The migration handles pre-existing duplicate values before creating the index.

## Account deletion

Account deletion requires the current password.

The process:

1. removes relationship records that should not remain active;
2. hides the user's authored publications;
3. hides groups owned by the user;
4. anonymizes identifying account fields;
5. soft-deletes the user;
6. invalidates the authenticated session.

Soft deletion avoids breaking historical foreign-key relationships while still removing the account from normal queries.

## Rich-text safety

Post body HTML is sanitized through `RichTextSanitizer` at the model boundary.

Automated publication tests verify that mature/HTML publication content is stored sanitized.

## Image replacement safety

Profile/group image updates follow a safer replacement sequence:

```text
store new file
→ update database pointer
→ delete old file
```

If the database update fails, newly stored replacement files are removed and the previous image is retained.

## Defense in depth

Frontend visibility is not considered authorization.

Buttons and controls are hidden for usability, but routes/controllers/form requests independently reject unauthorized operations.

## Related tests

```text
AttachmentSecurityTest
DraftTest
FollowTest
GroupAccessTest
GroupInvitationTest
ProfileTest
PublicationTest
QualificationHardeningTest
SearchTest
VisibilitySecurityTest
GroupVisibilityTest
PresentationDemoSeederTest
ProductionPolishTest
```
