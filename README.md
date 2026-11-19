# Client Edits Management System

A web app for one administrator to track clients and the edit requests they send.
Data is organized as **Client → Company → Project → Edit → Attachments**.

- Client pages show summary cards (companies, projects, active edits, unpaid total) and tabs for active edits, projects and companies.
- Every list has search and pagination; edits can also be filtered by status, priority and paid state.
- Each edit can have files attached (upload, download, delete).
- A client, company or project can't be deleted while it still has active edits (Pending, Confirmed or In Progress).

There are no roles: the only account is the administrator's.

## Stack

| Part | Technology |
|---|---|
| Backend | Laravel 13 (PHP 8.3+), Sanctum SPA authentication (cookie session), MySQL |
| Frontend | React 19, TypeScript, Vite, Redux Toolkit Query, React Router, Tailwind CSS 4 |
| Tests | PHPUnit (SQLite in memory) |

## Project structure

```
backend/
  app/Features/<Feature>/     one folder per feature: Authentication, Clients, Companies,
                              Projects, Edits, Attachments
    Controllers/ Requests/ DTOs/ Services/ Models/ Resources/ Routes/
  database/seeders/           admin account + sample data (one seeder per table)
  tests/                      feature and unit tests

frontend/src/
  client/                     API endpoints, one file per backend feature (RTK Query)
  lib/                        base query (Sanctum cookies + CSRF), query params, formatting
  errors/                     turns API errors into messages for the pages
  validators/                 form checks, same rules as the backend requests
  types/                      response and payload types
  components/                 <Feature>Components/, SharedComponents/, LayoutComponents/
  pages/                      one folder per section
  routes/                     router and ProtectedRoute
```

## Setup

You need PHP 8.3+, Composer, Node.js 20+ and MySQL.

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

In `backend/.env`, set the database connection and check these values:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=client_edits_management
DB_USERNAME=root
DB_PASSWORD=your_mysql_password

# The frontend's address, so Sanctum treats its requests as a logged-in session
SANCTUM_STATEFUL_DOMAINS=localhost:5173
SESSION_DOMAIN=null

# How long the admin stays logged in without a request, in minutes (1440 = one day).
# Each request restarts the countdown. Set SESSION_EXPIRE_ON_CLOSE=true to log out when the browser closes.
SESSION_LIFETIME=1440
SESSION_EXPIRE_ON_CLOSE=false

# The administrator account created by the seeder
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=choose_a_password
```

Create the tables and the data, then start the server:

```bash
php artisan migrate --seed
php artisan serve
```

The API runs at `http://localhost:8000`.

`--seed` creates the administrator and, outside production, sample data: 12 clients, 16 companies,
23 projects, 45 edits and 12 attachments. Running it again doesn't duplicate anything.
`php artisan migrate:fresh --seed` starts again from an empty database (it deletes all data).

### Frontend

```bash
cd frontend
npm install
npm run dev
```

The app runs at `http://localhost:5173`. Log in with `ADMIN_EMAIL` and `ADMIN_PASSWORD`
(`admin@example.com` / `password123` when `ADMIN_PASSWORD` isn't set).

The API address is in `frontend/.env` (`VITE_API_URL=http://localhost:8000`).
The frontend must run on port 5173: that's the address the backend allows (CORS and `SANCTUM_STATEFUL_DOMAINS`).
If Vite says 5173 is in use and picks another port, stop the other dev server first.

## Tests

```bash
cd backend
php artisan test
```

```bash
cd frontend
npm run lint
npm run build    # also type-checks
```

## How authentication works

The frontend and backend use Sanctum's cookie-based SPA authentication:

1. Laravel sets two cookies. `laravel_session` is the login session; JavaScript can't read it.
   `XSRF-TOKEN` is readable so the frontend can send it back.
2. Every request is sent with `credentials: 'include'`, so the browser includes the cookies.
3. For writes, the frontend copies `XSRF-TOKEN` into the `X-XSRF-TOKEN` header (axios does this automatically,
   `fetch` doesn't). If Laravel answers 419 (missing or expired token), it gets a fresh token from
   `/sanctum/csrf-cookie` and retries once.
4. A 401 from any request marks the admin as logged out, and the app goes to the login page.

## API overview

All routes are under `/api` and need a logged-in session, except login and `GET /auth/me`,
which answers `{ "data": null }` (200) when nobody is logged in.

| Feature | Endpoints |
|---|---|
| Auth | `POST /auth/login`, `POST /auth/logout`, `GET /auth/me` |
| Clients | `GET/POST /clients`, `GET/PATCH/DELETE /clients/{id}`, `GET /clients/{id}/summary` |
| Companies | `GET/POST /companies` (`?client_id=` filter), `GET/PATCH/DELETE /companies/{id}` |
| Projects | `GET/POST /projects`, `GET/PATCH/DELETE /projects/{id}`, `GET /projects/client/{id}`, `GET /projects/company/{id}`, `GET /projects/{id}/edits` |
| Edits | `GET/POST /edits` (`?status=&priority=&is_paid=&project_id=&client_id=&active=` filters), `GET/PATCH/DELETE /edits/{id}` |
| Attachments | `GET /attachments`, `GET/POST /edits/{id}/attachments`, `GET /attachments/{id}/download`, `DELETE /attachments/{id}` |

Lists accept `page`, `per_page` (default 10, max 100) and `search`.
Requests are limited to 60 per minute, and login attempts to 5 per minute.
