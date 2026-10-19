# Poet-Web: Production Launch Checklist

This checklist describes what remains before giving real users access to Poet-Web. **It is not a claim that the current Docker image has been tested or that the site has been deployed.** Development continues in Sail; the production host must use its own isolated database and persistent uploaded-file storage.

## What the repository currently does

- Laravel 13, PHP 8.3+, Vue 3/Inertia, Vite, MySQL.
- The root `Dockerfile` builds PHP dependencies and Vite assets, but currently starts `php artisan serve`. Replace that development-oriented HTTP server with a production web server (Nginx + PHP-FPM, FrankenPHP, or a host's supported Laravel runtime) before public launch. Do not serve the project root: the web document root must be `public/`.
- `compose.yaml` is Laravel Sail's **development** stack; do not publish its MySQL, Redis, Mailpit, Meilisearch, or Selenium ports on the public internet.
- Publication attachments live under `storage/app/private`, while user and group images live under `storage/app/public`. Both require persistence across deployments. Laravel's private attachment routes perform access checks and should continue to do so.
- `PresentationDemoSeeder` is restricted to local/testing and has known demo logins. Do not run it or copy a development database to production.

## Before deploying

1. Confirm which Git branch/commit contains the intended finished version, and merge reviewed changes deliberately. Do not blindly switch your local branch to `main`.
2. Run `./vendor/bin/sail artisan test`, `./vendor/bin/sail npm ci`, and `./vendor/bin/sail npm run build` locally. Independently verify the build pipeline and any integration/browser tests on the deployment commit.
3. Verify the production image builds with only repository-tracked files, no local `.env`, `vendor`, `node_modules`, or `public/hot` injected from the developer's computer. The root `.dockerignore` protects the build context.
4. Choose a host running PHP 8.3+ with Laravel's required extensions, a separate MySQL service, and persistent object/file storage. For managed providers, follow that provider's runtime and health-check guidance. If using Railway with the current root Dockerfile, **Railway will use that Dockerfile rather than automatically running the platform's native PHP/Caddy build**.
5. Provide correct persistent file storage. For a **single-instance Railway deployment using the current Dockerfile's workdir**, a volume mounted at `/var/www/html/storage/app` can preserve both private attachments and public uploaded images. Confirm write access and the public `storage:link` symlink; test an image upload before and after redeploying. For multi-instance deployments, adapt upload code and resources to S3-compatible storage with authenticated private attachment delivery instead.
6. Configure application secrets as **hosting-provider environment variables**, never in Git or the Docker image. Generate `APP_KEY` once, store it safely, and retain it across redeployments. Use a **fresh production database** and do not copy the development credentials.
7. Configure a real outgoing-email provider (SMTP or another supported mailer). Mailpit is local-development only. Test registration verification, password reset, and any post notifications.
8. Set up TLS/HTTPS using the host's certificate handling. Point domain DNS A/CNAME records at the exact destination issued by the provider. Check proxy trust, HTTPS redirects, generated absolute links, and `SESSION_SECURE_COOKIE=true`.
9. Configure database and file backups. Record a restoration procedure and test it. Do not use `migrate:fresh` or destructive resets on a live database.
10. Size request upload limits for Poet-Web's configured files (up to 25 MB each and 90 MB total). Check PHP `upload_max_filesize`, `post_max_size`, and any reverse-proxy request limit.

## Production environment template (values are examples, not credentials)

Supply these in the host's secret/configuration dashboard; do **not** commit a populated `.env`:

```dotenv
APP_NAME=Poet-Web
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=base64:REPLACE_WITH_GENERATED_SECRET
LOG_CHANNEL=stderr
DB_CONNECTION=mysql
DB_HOST=REPLACE_WITH_PRIVATE_DATABASE_HOST
DB_PORT=3306
DB_DATABASE=REPLACE_WITH_DATABASE_NAME
DB_USERNAME=REPLACE_WITH_DATABASE_USER
DB_PASSWORD=REPLACE_WITH_DATABASE_PASSWORD
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
MAIL_MAILER=smtp
MAIL_HOST=REPLACE_WITH_REAL_MAIL_SERVER
MAIL_PORT=587
MAIL_USERNAME=REPLACE_WITH_SMTP_USER
MAIL_PASSWORD=REPLACE_WITH_SMTP_PASSWORD
MAIL_FROM_ADDRESS=REPLACE_WITH_VERIFIED_SENDER
```

The `APP_URL` must match the **real** HTTPS URL; replace the illustrative hostname rather than deploying with `your-domain.example`. For Railway MySQL, reference database service variables instead of pasting the `.env.example` development values.

Generate the secret with `php artisan key:generate --show` in a secure local environment, then insert the output in the hosting secret store. Do not regenerate this key on later deployments.

## Deployment and verification

- Provision the MySQL service, set environment variables, and confirm the database is reachable.
- Use a **release step** (not a Docker image build step) to run `php artisan migrate --force`. Run `php artisan optimize` only once the actual runtime environment is in place. Recheck config caching after changing variables.
- Run `php artisan storage:link` if the host uses a local public filesystem. Confirm the symlink survives fresh deploys; do not point the public web root at `storage/app/private`.
- If jobs are dispatched asynchronously, run an independent `php artisan queue:work` service with a restart policy. Set up a scheduler only for actual scheduled tasks.
- Configure HTTP health checks against the already implemented `GET /up`, then verify `/`, login, password reset, profile images, public posts, private-group authorization, attachments, comments, and direct messages.
- Upload a temporary image and attachment, redeploy, and confirm **both** survive. Try to access a private-group attachment as a guest and verify it is denied.
- Confirm `APP_DEBUG=false`, HTTPS, proper security headers, email delivery, and backups before allowing public registrations.
- Make an initial backup and document rollback steps before deploying migrations that change the schema.

## Hosting notes

### Railway

- Link the intended Git branch to a service.
- Provision a separate MySQL service and use its private connection variables.
- Attach persistent storage to the app **before accepting user uploads**.
- Review/replace the current `Dockerfile` web-server command before publishing. A Dockerfile takes precedence over provider auto-detection when chosen as the build source.
- Set the service health-check path to `/up` and expose an assigned Railway domain first; attach a custom domain after testing.
- Send app logs to stderr. Add a worker/scheduler service only if needed.

See https://docs.railway.com/guides/laravel and https://docs.railway.com/volumes.

### Laravel Cloud

- Link the repository and use the platform-native build/deploy workflow.
- Create a managed MySQL database.
- **Do not rely on container-local upload storage persisting.** First migrate public uploads and authorization-checked private attachments to supported object storage, installing the necessary Flysystem adapter and checking every attachment/image URL.
- Set build commands, release migration command, runtime variables, mail, and domain/TLS through the provider.

See https://laravel.com/cloud/deploy-a-laravel-app.

## Open work before declaring launch-ready

- Select and test the final production server/runtime. The checked-in Dockerfile's `artisan serve` command is still a development-mode server.
- Select a hosting platform, real domain (or temporary platform domain), database and file persistence plan.
- Execute integration tests and restore-from-backup checks **on the actual deployed environment**.
- Review privacy/copyright/moderation policy and confirm consent/disclosure appropriate for public user content.
