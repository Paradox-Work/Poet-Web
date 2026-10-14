# Poet-Web Technical Documentation

This directory documents the implemented behavior of Poet-Web. The application code remains the source of truth; documentation should be updated when routes, validation, authorization, storage, or UI flows change.

## Current architecture and quality

- [Implementation notes](implementation-notes.md)
- [Security and access control](security-and-access-control.md)
- [Testing and quality assurance](testing-and-quality-assurance.md)

## Public browsing and content discovery

- [Home feed flow](home-feed-flow.md)
- [Global search flow](global-search-flow.md)
- [Profile content flow](profile-content-flow.md)
- [Dedicated post page flow](post-dedicated-page-flow.md)

## Attachments

- [Attachment upload flow](attachment-flow.md)
- [Attachment validation flow](attachment-validation-flow.md)
- [Attachment size validation](attachment-size-validation.md)
- [Attachment preview flow](attachment-preview-flow.md)
- [Attachment display flow](post-attachments-flow.md)
- [Attachment routes and authorization](attachment-download-route-model-binding.md)

## Comments and reactions

- [Post comments flow](post-comments-flow.md)
- [Comment update/delete flow](comment-management-flow.md)
- [Comment replies flow](comment-replies-flow.md)
- [Reaction system flow](reaction-system-flow.md)

## Groups

- [Group loading flow](group-loading-flow.md)
- [Group profile flow](group-profile-flow.md)
- [Group join flow](group-join-flow.md)
- [Membership approval flow](group-membership-approval-flow.md)
- [Invitation flow](group-invitation-flow.md)
- [Role management](group-role-management.md)
- [Member removal flow](group-member-removal-flow.md)
- [Group post flow](group-post-flow.md)
- [Group update flow](group-update-flow.md)

## Social and notification flows

- [User follow flow](user-follow-flow.md)
- [Content notification flow](content-notification-flow.md)

## Maintenance rule

When a feature changes, check both the implementation and the matching document. In particular, authorization and storage documentation must not be treated as optional because stale documentation can describe unsafe behavior that no longer exists.
