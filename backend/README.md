# ScholarSync Backend

Laravel API backend for the ScholarSync Vue frontend. It uses PostgreSQL for application data and exposes camelCase JSON fields that match the current frontend mock data.

## Requirements

- PHP 8.2+
- Composer
- PostgreSQL 14+

## PostgreSQL Setup

Create the database first:

```sql
CREATE DATABASE scholarsync;
```

Then update `.env` if your local PostgreSQL credentials differ:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=scholarsync
DB_USERNAME=postgres
DB_PASSWORD=
```

## Run Locally

```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The API will be available at `http://localhost:8000/api`.

The seeder creates user accounts only. Seeded login accounts use the password `password`.

- Admin: `camille.navarro@scholarsync.edu`
- Student: `alyssa.mendoza@college.edu`

## Main Endpoints

- `GET /api/test`
- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/dashboard/student`
- `GET /api/dashboard/admin`
- `GET|POST /api/scholarships`
- `GET|POST /api/applications`
- `PATCH /api/applications/{id}/status`
- `GET|POST /api/documents`
- `PATCH /api/documents/{id}/status`
- `GET|POST /api/announcements`
- `GET|POST /api/compliance-records`
- `GET /api/analytics`
- `GET /api/reports/applications`

## Tests

```bash
php artisan test
```
