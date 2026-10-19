# Deploy Poet-Web to Railway (first working URL)

This deployment procedure is for the `docs/production-launch-readiness` branch until reviewed and merged. It uses Railway's **Railpack** PHP/Laravel provider, not Vercel. The original Dockerfile has been kept as `Dockerfile.legacy`, since Railway prioritizes a file named `Dockerfile` over automatic Laravel detection.

## 1. Create the application

1. Sign into [Railway](https://railway.com/), choose **New Project > Deploy from GitHub repo**, and select **Paradox-Work/Poet-Web**.
2. In the application service settings, set the source branch to **docs/production-launch-readiness** until the deployment changes are reviewed and merged.
3. Under Build, choose **Railpack**, not Dockerfile. There must be no custom Dockerfile path.
4. Add a **MySQL** service to the same Railway project. Do not expose its database port publicly.
5. In the application service Variables, reference the MySQL service's variables, not the Docker Sail defaults. For example, use Railway's service-reference picker to set `DB_HOST` from the MySQL service private host, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` from the matching MySQL variables. These exact references depend on the name of the MySQL service.

## 2. App variables

Set these in Railway's application Variables (replace placeholders):

```env
APP_NAME=Poet-Web
APP_ENV=production
APP_DEBUG=false
APP_URL=https://REPLACE_WITH_GENERATED_RAILWAY_DOMAIN
APP_KEY=base64:REPLACE_WITH_REAL_RANDOM_KEY
APP_LOCALE=lv
APP_FALLBACK_LOCALE=en
LOG_CHANNEL=stderr
DB_CONNECTION=mysql
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
MAIL_MAILER=log
RAILPACK_PHP_EXTENSIONS=pdo_mysql,gd
```

Generate the APP_KEY using a local command `php artisan key:generate --show` or `./vendor/bin/sail artisan key:generate --show` (the latter may rewrite the local `.env` unless you use the `--show` option). Never publish the real key, rotate it casually, or commit it to Git. The MAIL_MAILER=log setting is **only for private smoke testing**: password-reset and verification messages will not reach people; configure a real email provider before opening registration.

The database variables must refer to the **production MySQL service**, not `mysql` and `sail/password` from the developer's `.env.example`.

## 3. Files and volume

Before real people upload content, attach a Railway persistent volume to the app at **`/app/storage/app`**. This is where Poet-Web stores private publication attachments and public profile/group photos. The mount is different from the old Dockerfile's `/var/www/html`, because Railpack uses `/app`.

The `storage:link` command must link `public/storage` to `storage/app/public`. Railpack's Laravel startup script normally runs `storage:link` and migrations. Check startup logs, volume write permissions, uploaded-image display, and survival after redeploy before inviting users. Do not mount a volume over the whole `/app` directory.

Note: content is accessible only to the currently configured storage backend. Moving to multiple app replicas requires redesigning file storage (for instance S3-compatible object storage).

## 4. Domain and first launch

1. In the app service **Settings > Networking**, generate a Railway public domain.
2. Update `APP_URL` to that exact `https://...up.railway.app` URL.
3. Under service **Settings > Deploy > Healthcheck Path**, use `/up`.
4. Do not override the start command; let Railpack use its Laravel startup and FrankenPHP server.
5. Wait for the deployment to reach healthy, open the generated public URL, and test a guest home page, login, an image and a private attachment.
6. If a custom domain is purchased later, add it under Networking and copy Railway's DNS values into the domain registrar. After HTTPS is ready, change `APP_URL` and verify cookie/login redirects.

## 5. Troubleshooting

- Build failure: check Railway build logs, `npm ci`, Composer PHP extension errors, and that Railpack (not Dockerfile) is selected.
- 500: check `APP_KEY`, production MySQL variables, and application logs (never enable debug publicly).
- Database connection refused: check the **private** MySQL service reference, service readiness and deploy logs.
- Images disappear after redeploy: volume is absent/wrong mount path, app cannot write to it, or `storage:link` is missing.
- 419/CSRF or endless redirects: check HTTPS URL, proxy handling, `SESSION_SECURE_COOKIE`, and app domain.
- 404 or blank page: ensure the web root is `/app/public`, Vite assets were built, and there's no stale Vite hot file.

Before inviting public users, configure working transactional SMTP/email, backups and restore testing, moderation policies and rate limits. Check app and DB hosting costs; trial credits are not a permanent free hosting guarantee.

## Official references

- https://docs.railway.com/guides/laravel
- https://railpack.com/languages/php
- https://docs.railway.com/volumes
