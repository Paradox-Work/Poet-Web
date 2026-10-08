# Poet-Web

Poet-Web is a social platform for poets, readers, and poetry communities.

The application allows users to publish poems and posts, join groups, interact through comments and reactions, follow other users, manage profile media, and browse group content.

This project is being developed as a qualification project.

## Tech Stack

- Laravel 13
- PHP 8.3
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

## Main Features

- User authentication and registration
- User profiles
- Profile cover and avatar images
- Follow / unfollow users
- Create, edit, and soft-delete posts
- Upload post attachments
- Nested comments and replies
- Comment and post reactions
- Groups and group membership
- Group invitations and join requests
- Group administrators and member roles
- Group posts restricted to approved members
- Pin posts to profiles or groups
- Notifications
- Photo galleries
- Dark and light themes
- Search

## Installation with Docker

### 1. Clone the repository

```bash
git clone https://github.com/Paradox-Work/Poet-Web.git
cd Poet-Web
```

### 2. Install Composer dependencies

If Composer is not installed locally, the Laravel Sail Composer image can be used:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

If Composer is installed locally:

```bash
composer install
```

### 3. Create the environment file

```bash
cp .env.example .env
```

### 4. Start Laravel Sail

```bash
./vendor/bin/sail up -d
```

### 5. Generate the application key

```bash
./vendor/bin/sail artisan key:generate
```

### 6. Run the database migrations

```bash
./vendor/bin/sail artisan migrate
```

### 7. Install frontend dependencies

```bash
./vendor/bin/sail npm install
```

### 8. Start the frontend development server

```bash
./vendor/bin/sail npm run dev
```

The application can then be opened using the URL configured for the local Laravel Sail environment.

## Useful Development Commands

Start the containers:

```bash
./vendor/bin/sail up -d
```

Stop the containers:

```bash
./vendor/bin/sail down
```

Run Artisan commands:

```bash
./vendor/bin/sail artisan <command>
```

Run tests:

```bash
./vendor/bin/sail artisan test
```

Build frontend assets:

```bash
./vendor/bin/sail npm run build
```

## Repository

GitHub: https://github.com/Paradox-Work/Poet-Web
