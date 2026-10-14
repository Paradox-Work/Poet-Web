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
82 tests passed
377 assertions
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

### Bookmarks

- visible publication bookmark/unbookmark;
- saved reading-list rendering;
- private-group outsider denial.

### Direct messaging

- starting/reusing a private conversation;
- message storage/retrieval;
- non-participant denial.

### Reporting / moderation

- report submission for visible content;
- non-admin moderation denial;
- site-admin report resolution;
- soft-deleted moderated posts.

### Collaborative drafts

- owner adds collaborator;
- collaborator autosaves shared draft;
- outsider cannot edit;
- group membership restriction for group-draft collaborators.

### Discovery sorting

- reaction-count ordering;
- comment-count ordering;
- no AI recommendation dependency.

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
20. responsive layout and dark/light theme;
21. bookmark/unbookmark and Saved page;
22. direct message from a profile, receive/send/poll;
23. report a post/comment/user;
24. grant a throwaway/demo user site-admin access and review report queue;
25. collaborative poem draft shared with another account;
26. Home newest/most-liked/most-discussed sorting;
27. public Privacy/Terms/Guidelines/Copyright pages;
28. invalid URL branded 404 behavior in a production-like environment;
29. skip-link keyboard navigation and reduced-motion preference.

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
