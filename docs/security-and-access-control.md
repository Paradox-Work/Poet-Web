# Security and Access Control

## Purpose

This document summarizes the authorization and data-protection rules that were hardened for the qualification-project review.

## Core publication visibility rule

Published standalone content is public.

Published group content is restricted to approved group members.

Drafts are never part of public timelines.

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
- standalone-publication attachments.

Guests may not perform write actions.

## Group security

Group publications require approved membership.

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
```
