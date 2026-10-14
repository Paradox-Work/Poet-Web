# Testing and Quality Assurance

## Test approach

Poet-Web primarily uses Laravel feature tests because the qualification-review risks are request-level behaviors:

- authentication;
- validation;
- authorization;
- database persistence;
- visibility;
- route behavior;
- file access.

Feature tests create test data, authenticate when needed, call real Laravel routes, and assert HTTP/database outcomes.

## Current verified checkpoint

The current qualification build on `main` is verified by GitHub Actions with:

```text
91 tests passed
439 assertions
0 failures
```

The same CI run also completed:

- database migrations;
- `npm ci`;
- Vite production build.

## Important covered areas

### Authentication

- login;
- invalid-password rejection;
- logout;
- registration;
- email verification;
- password reset/update/confirmation.

### Publications

- ownership for update/delete;
- rich-text sanitization;
- empty-publication rejection;
- publication visibility.

### Drafts

- draft ownership;
- group draft membership requirement;
- owner-only draft attachment access.

### Groups

- public vs private publication visibility;
- guest access to public group content;
- denial of private-group content to guests/outsiders;
- administrator visibility updates;
- visibility validation;
- join request status;
- admin-only request resolution;
- approved-member publishing;
- group-admin moderation;
- invitation ownership;
- invitation expiration;
- POST-based acceptance;
- non-admin invitation denial.

### Follows

- self-follow rejection;
- repeated follow handling;
- database duplicate prevention.

### Attachments

- permitted-member access;
- outsider denial;
- draft owner-only access;
- private-disk storage for new uploads;
- guest denial for protected private-group attachments.

Public standalone/public-group attachment access inherits the shared publication visibility policy.

### Search

- public search;
- visibility-preserving publication results;
- independent user/group/post pagination.

### Guest access

- public Home/Explore;
- public search;
- public profile publications;
- dedicated public publication page;
- omission of private user fields.

The bilingual interface itself is additionally verified by the production frontend build and manual UI smoke testing; the PHP feature suite is not a browser localization test framework.

### Presentation demo data

- presentation seeder executes;
- public/private demo groups are created;
- Latvian demo publication data is created.

### Personal library

- visible-publication bookmark toggling;
- private-group bookmark denial for outsiders;
- reading-list ownership enforcement.

### Direct messaging

- deterministic/idempotent direct conversation creation;
- participant send/poll behavior;
- outsider conversation denial.

### Moderation

- visible-content reports;
- duplicate pending-report prevention;
- private-group report visibility protection;
- administrator content removal;
- suspended-login denial;
- ordinary-user admin-dashboard denial.

### Collaboration

- poem owner invitation;
- invitee acceptance;
- accepted-collaborator editing;
- outsider edit denial;
- private-group collaborator membership enforcement.

### Discovery / public pages

- popularity sorting;
- public legal page;
- sitemap response.

### Revoked private-group access

Tests verify that a former private-group member cannot continue interacting with protected publications/comments after membership removal.

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
→ prepare test environment
→ migrate SQLite test database
→ npm ci
→ npm run build
→ php artisan test
```

## Local verification

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run build
./vendor/bin/sail artisan test
```

For normal frontend development:

```bash
./vendor/bin/sail npm run dev
```

Run `npm ci` when JavaScript dependencies/lockfile need to be freshly synchronized; it is not necessary after every code-only pull.

## Manual smoke testing

Automated tests do not replace browser testing.

Before presentation/submission, manually verify:

1. Latvian default interface and LV/EN switching;
2. guest Home/Explore;
3. guest search;
4. guest profile;
5. guest dedicated standalone publication;
6. public-group publication as guest;
7. private-group content denied as guest/outsider;
8. registration/login;
9. create/edit/delete standalone post;
10. poem writing + autosave + publishing;
11. attachment preview/download;
12. follow/unfollow;
13. group creation with public/private visibility;
14. automatic/manual join flow;
15. invitation accept/decline;
16. role change/member removal;
17. comments/replies/reactions;
18. profile update/images;
19. search pagination;
20. direct messaging between two accounts;
21. bookmark and reading-list flow;
22. collaborator invite/accept/edit flow;
23. report submission;
24. admin report resolution and suspension/restore with a demo admin;
25. newest/trending/popular/discussed sorting;
26. legal/community pages;
27. branded 404/403 behavior;
28. sitemap/metadata in a production-like environment;
29. responsive layout and dark/light theme.

## What passing tests mean

Passing tests prove only the behavior that is covered under the tested conditions.

Final qualification confidence therefore combines:

```text
automated regression tests
+
frontend production build
+
database migration check
+
manual browser smoke test
+
documentation/code review
```
