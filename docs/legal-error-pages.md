# Legal, Community, and Error Pages

## Public policy pages

Route:

```text
GET /legal/{page}
```

Allowed pages:

```text
privacy
terms
guidelines
copyright
```

Unknown values return 404.

The current policy text is a project-facing implementation and should not be represented as professional legal advice.

## Community guidelines

The guidelines explain:

- respect for authors;
- constructive feedback;
- mature-content marking;
- reporting unsafe/inappropriate material.

## Copyright page

The application states that authors retain rights to their work.

Publishing to Poet-Web is not described as transferring copyright ownership to the platform.

The page also connects copyright concerns to the reporting flow.

## Privacy page

The privacy page summarizes data types used by current implemented features, including:

- account information;
- publications;
- group membership;
- reactions/comments;
- direct messages;
- bookmarks;
- session information.

It also explains the protected nature of private groups and direct messages.

## Terms

The terms page communicates expected user behavior and moderation consequences.

## Branded error pages

Poet-Web provides styled pages for:

```text
403 Access denied
404 Not found / protected content
419 Session expired
429 Too many requests
500 Server error
```

The pages:

- use Poet-Web visual styling;
- support Latvian/English based on browser preference;
- use `noindex`;
- provide a safe Home link independent of named-route generation.

## Why 404 is used for some authorization failures

Private content often deliberately returns:

```text
404
```

instead of 403.

That prevents an outsider from learning whether a protected publication/comment/attachment ID exists.

The branded 404 explains the result without leaking protected details.

## Main files

```text
resources/js/Pages/Legal/Show.vue
resources/views/errors/layout.blade.php
resources/views/errors/403.blade.php
resources/views/errors/404.blade.php
resources/views/errors/419.blade.php
resources/views/errors/429.blade.php
resources/views/errors/500.blade.php
routes/web.php
```
