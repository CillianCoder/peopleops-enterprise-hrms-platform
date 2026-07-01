# Platform Foundation

This foundation implements the first secure PeopleOps access flow.

## Workflow

1. When the `users` table is empty, `/register` allows one initial signup.
2. The first registered user is assigned the protected `super_admin` role.
3. Further public signup attempts are rejected server-side.
4. The administrator must complete company onboarding before dashboard access.
5. Company details include identity, contact, location, timezone, currency, and an optional logo.
6. Company logos are stored on the configured private disk, normally Cloudflare R2, and streamed through an authenticated Laravel route.
7. Administrators manage all later users from `/admin/users`; new users receive a password setup email.

## Security Rules

- Login is rate-limited to five attempts per email and IP combination.
- Password reset uses Laravel's token broker.
- Passwords require at least 12 characters with mixed case, numbers, and symbols.
- The initial `super_admin` account cannot be edited through managed-user screens.
- Managed users cannot be assigned `super_admin`.
- User and company changes are activity logged through Spatie Activitylog.
- File uploads validate image type and size before storage.

## Required Setup

Run:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

Then update `.env` with Neon PostgreSQL, Redis, mail, and Cloudflare R2 credentials.

The foundation targets Laravel 12 because it is the latest compatible major for the approved package set currently used here. Move to Laravel 13 only after the full package set resolves cleanly and backend tests pass.

Excel import/export support is intentionally not installed in this foundation because `maatwebsite/excel` 3.x does not support PHP 8.5 and the PHP 8.5-compatible 4.x line is currently development-only. Add it when the import/export module is implemented against a stable compatible release.

## Validation

Frontend validation run during implementation:

```bash
npm run lint
npm run build
```

Backend validation to run once PHP and Composer are available:

```bash
composer install
php artisan test
./vendor/bin/pint --test
```
