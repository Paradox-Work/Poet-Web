# Poet-Web

Poet-Web is a social platform for poets, readers, and poetry communities, developed as a qualification project.

Visitors can browse public poetry and posts before registering. Authenticated users can publish content, follow writers, join groups, comment, react, manage profiles, and participate in group communities.

## Tech Stack

- Laravel 13
- PHP 8.3+ (Laravel Sail currently uses PHP 8.5)
- Vue.js 3
- Inertia.js
- Tailwind CSS
- MySQL
- Laravel Sail / Docker
- Vite
- Laravel Breeze
- Laravel Sanctum
- Headless UI
- Tiptap
- Pest / PHPUnit

## Main Features

- Latvian-first interface with persistent LV / EN language switch
- Guest browsing of public feed, search, profiles, groups, and public publications
- User registration, authentication, password reset, and email verification
- User profiles with cover and avatar images
- Follow / unfollow users
- Create, edit, pin, and soft-delete posts and poems
- Rich-text poem/post content with server-side sanitization
- Private attachment storage with authorization-controlled view/download routes
- Nested comments and replies
- Comment and post reactions
- Groups, invitations, join requests, roles, and moderation
- Public and private groups with independent membership approval
- Private group publications restricted to approved members
- Notifications
- Photo galleries
- Dark and light themes
- Global search for users, groups, and visible publications
- Automated Laravel feature tests and GitHub Actions CI

## Access Model

Standalone published posts are public.

Group visibility is explicit. Public group publications are readable by guests; private group publications require approved membership. This rule is enforced by the backend and reused by the home feed, search, dedicated publication pages, reactions, comments, and attachment access.

Guests can browse public content, but actions that change data still require authentication, including:

- publishing;
- following;
- joining groups;
- reacting;
- commenting;
- editing;
- moderation.

See [docs/security-and-access-control.md](docs/security-and-access-control.md).

## Qualification Presentation Demo

A local presentation dataset can be created with:

```bash
./vendor/bin/sail artisan db:seed --class=PresentationDemoSeeder
```

The seeder creates Latvian demo users, poems, comments, reactions, follow relationships, a public group, a private group, approved memberships, and a pending join request.

See:

- [Presentation demo guide](docs/presentation-demo-guide.md)
- [Presentation ER diagram](docs/database-er-diagram.md)

## Pulling the Latest Version

If the repository is already cloned:

```bash
git status
git switch main
git pull origin main
```

If `git status` shows local changes you want to keep, commit or stash them before pulling.

After pulling the current qualification-project hardening changes, run:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm ci
./vendor/bin/sail npm run dev
```

Then open the URL configured by `APP_URL` (normally `http://localhost`).

## Fresh Installation with Laravel Sail

### 1. Clone

```bash
git clone https://github.com/Paradox-Work/Poet-Web.git
cd Poet-Web
```

### 2. Install Composer dependencies

With local Composer:

```bash
composer install
```

Or using a Sail Composer image:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

### 3. Create the environment file

```bash
cp .env.example .env
```

### 4. Start Sail

```bash
./vendor/bin/sail up -d
```

### 5. Generate the application key

```bash
./vendor/bin/sail artisan key:generate
```

### 6. Run database migrations

```bash
./vendor/bin/sail artisan migrate
```

### 7. Install and start frontend dependencies

```bash
./vendor/bin/sail npm ci
./vendor/bin/sail npm run dev
```

## Verification Commands

Run backend/feature tests:

```bash
./vendor/bin/sail artisan test
```

Build production frontend assets:

```bash
./vendor/bin/sail npm run build
```

The hardening release was verified with 63 passing tests and 244 assertions, plus a successful Vite production build in GitHub Actions.

See [docs/testing-and-quality-assurance.md](docs/testing-and-quality-assurance.md).

## Production Docker Image

The root `Dockerfile` builds the application without generating a project secret, creating a deployment `.env`, or mutating the production database during image build.

Production configuration must be supplied by the deployment environment. At minimum, configure `APP_KEY`, `APP_URL`, and the database settings.

Run migrations as a release/deployment step:

```bash
php artisan migrate --force
```

Local Sail development uses MySQL from `compose.yaml`. Automated tests may use SQLite for isolated and fast test execution.

## Documentation

Technical flows and security notes are stored in `docs/`.

Start with:

- [Documentation index](docs/README.md)
- [Implementation notes](docs/implementation-notes.md)
- [Security and access control](docs/security-and-access-control.md)
- [Testing and quality assurance](docs/testing-and-quality-assurance.md)
- [Presentation demo guide](docs/presentation-demo-guide.md)
- [ER diagram for presentation](docs/database-er-diagram.md)

## Repository

GitHub: https://github.com/Paradox-Work/Poet-Web
