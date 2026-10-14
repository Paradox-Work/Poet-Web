# Testing and Quality Assurance

## Test approach

Poet-Web primarily uses Laravel feature tests because the qualification-review concerns are request-level behaviors:

- authentication;
- validation;
- authorization;
- database persistence;
- visibility;
- route behavior;
- file access.

Feature tests act like automated HTTP clients. They create test data, authenticate when needed, call real Laravel routes, and assert responses/database state.

## Current automated suite

At the qualification hardening checkpoint, GitHub Actions completed successfully with:

```text
70 tests passed
286 assertions
0 failures
```

The Vite production build also completed successfully.

## Important covered areas

### Authentication

- login;
- invalid password rejection;
- logout;
- registration;
- email verification;
- password reset/update/confirmation.

### Publications

- ownership for update/delete;
- rich-text sanitization;
- empty publication rejection;
- group publication visibility.

### Drafts

- draft ownership;
- group draft membership requirement;
- owner-only draft attachment access.

### Groups

- public vs private group publication visibility;
- guest access to public group content;
- denial of private group content to guests/outsiders;
- admin visibility updates and validation;
- join request status;
- admin-only approval;
- approved-member publication;
- moderator/admin deletion behavior;
- invitation ownership/expiry;
- invitation acceptance using POST.

### Follows

- self-follow rejection;
- repeated follow handling;
- database duplicate prevention.

### Attachments

- approved member access;
- outsider denial;
- owner-only draft files;
- private-disk storage for new uploads;
- guest denial for group-only files.

### Search

- public search;
- visibility-preserving publication results;
- separate pagination for users/groups/posts.

### Guest access

- Latvian-first guest interface with English switch (frontend build verified);
- public Home feed;
- public search;
- public profile publications;
- no private user fields.

### Presentation demo data

- local presentation seeder executes successfully;
- public and private demo groups are created;
- Latvian demo publication data is created.

### Revoked group access

Tests verify that former members cannot continue interacting with member-only publications/comments after their membership is removed.

## Continuous integration

Workflow:

```text
.github/workflows/tests.yml
```

Runs on:

```text
push
pull_request
```

Pipeline:

```text
checkout
→ setup PHP
→ setup Node
→ composer install
→ create test env/key
→ migrate SQLite test database
→ npm ci
→ npm run build
→ php artisan test
```

## Local verification

With Sail running:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm ci
./vendor/bin/sail npm run build
./vendor/bin/sail artisan test
```

For normal frontend development:

```bash
./vendor/bin/sail npm run dev
```

## Manual smoke testing

Automated backend tests do not replace browser testing.

Before a demonstration/submission, manually test:

1. guest Home/Explore;
2. guest search;
3. guest profile and dedicated public publication;
4. registration/login;
5. create/edit/delete standalone post;
6. create poem;
7. attachment image/file preview and download;
8. follow/unfollow;
9. create/join group;
10. manual join approval and invitation;
11. group publication visibility from member vs outsider;
12. comment/reply/reaction;
13. profile update/images;
14. search pagination;
15. logout and guest navigation;
16. responsive/mobile layout;
17. light/dark theme.

## What passing tests mean

Passing tests demonstrate that the covered behavior is working under the tested conditions. They do not prove that the application has no bugs.

That is why the final qualification check combines:

- automated regression tests;
- frontend production build;
- database migration test;
- manual browser smoke testing;
- code/documentation review.
