# Poet-Web Technical Documentation

This directory documents the implemented behavior of Poet-Web at the current qualification-project checkpoint.

The application code and automated tests remain the source of truth. When routes, validation, authorization, storage, or UI behavior changes, the matching documentation should be updated in the same change.

## Start here

- [Current project status](project-status.md)
- [Implementation notes](implementation-notes.md)
- [Security and access control](security-and-access-control.md)
- [Testing and quality assurance](testing-and-quality-assurance.md)

## Qualification presentation

- [Presentation demo guide](presentation-demo-guide.md)
- [Presentation ER diagram](database-er-diagram.md)
- [Localization flow](localization-flow.md)
- [About and author-rights page](about-rights-flow.md)

## Public browsing and discovery

- [Home / Explore feed](home-feed-flow.md)
- [Global search](global-search-flow.md)
- [Profile content](profile-content-flow.md)
- [Profile and group photo galleries](profile-photo-gallery-flow.md)
- [Dedicated publication page](post-dedicated-page-flow.md)
- [Infinite publication loading](post-load-more-flow.md)

## Publishing and drafts

- [Post modal flow](post-modal-flow.md)
- [Local writing suggestions](local-writing-suggestions.md)
- [Post moderation](post-moderation-flow.md)

## Attachments

- [Attachment upload flow](attachment-flow.md)
- [Attachment validation](attachment-validation-flow.md)
- [Attachment size validation](attachment-size-validation.md)
- [Attachment preview](attachment-preview-flow.md)
- [Attachment display](post-attachments-flow.md)
- [Attachment routes and authorization](attachment-download-route-model-binding.md)

## Comments and reactions

- [Comments and replies](post-comments-flow.md)
- [Comment tree construction](comment-replies-flow.md)
- [Comment update/delete authorization](comment-management-flow.md)
- [Reaction system](reaction-system-flow.md)

## Groups

- [Group discovery/loading](group-loading-flow.md)
- [Group profile](group-profile-flow.md)
- [Group join flow](group-join-flow.md)
- [Membership approval](group-membership-approval-flow.md)
- [Invitation flow](group-invitation-flow.md)
- [Role management](group-role-management.md)
- [Member removal](group-member-removal-flow.md)
- [Group publication flow](group-post-flow.md)
- [Group settings update](group-update-flow.md)

## Social, library, collaboration, and messaging

- [User follow flow](user-follow-flow.md)
- [Content notification flow](content-notification-flow.md)
- [Direct messaging](direct-messaging-flow.md)
- [Bookmarks and reading lists](library-reading-lists.md)
- [Poem collaboration](collaboration-flow.md)

## Production-style platform features

- [Reporting, moderation, and admin analytics](moderation-admin-analytics.md)
- [Discovery, SEO, sharing, and accessibility](discovery-seo-accessibility.md)
- [Legal, community, and error pages](legal-error-pages.md)

## Development tools

- [Laravel Telescope development notes](telescope-development.md)

## Maintenance rule

Do not describe frontend visibility as authorization.

For protected behavior, documentation should identify the backend rule/controller/request that actually enforces access.

Similarly, do not describe public/private group access, attachment storage, test counts, or localization coverage from memory: keep those claims synchronized with current code and CI.
