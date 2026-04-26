# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

OpenTogG is a time tracking application (Toggl alternative) built with Laravel 13, Livewire 4, and Tailwind CSS 4. It features real-time timers, project organization via "vectors," tags, reporting with activity heatmaps, CSV/PDF export, and a Toggl API v9 compatibility layer for StreamDeck integration.

## Development Commands

```bash
# Full setup (install deps, .env, migrate, build assets)
composer setup

# Local dev (concurrent: artisan serve, queue, pail logs, vite HMR)
composer dev

# Testing
composer test                           # clear config + run tests
php artisan test --filter=TestName      # single test

# Code formatting
./vendor/bin/pint

# API docs generation
php artisan swagger:generate

# Docker workflow (see Makefile)
make up / make down / make restart
make migrate / make fresh / make seed
make test
make deploy                             # build + rsync + cache to staging
```

## Architecture

### Models & Conventions

- **BaseModel** (`app/Models/BaseModel.php`): All domain models extend this. It provides:
  - UUID-based `external_id` (auto-generated on create)
  - `external_id` as route key (not internal `id`)
  - Custom timestamps: `createAt` / `updateAt` (camelCase, not Laravel default)
- **Models**: User, Vector (projects/categories), Tag, TimeEntry
- **Running timer**: A TimeEntry with `stopped_at = null`

### Livewire Components

Interactive UI is built with Livewire 4 components in `app/Livewire/`. Components communicate via dispatched events (e.g., `timer-stopped`, `entry-stopped`). Key components:
- **Timer** — start/stop timer, syncs state across devices
- **TimeLog** — today's completed entries with inline edit
- **ManualEntry** — retroactive time entry creation
- **Reports** — 52-week activity heatmap, stats, Chart.js integration

### API Structure

- **`/api/v1/`** — Primary REST API, authenticated via Sanctum bearer tokens. Resources: vectors, tags, time-entries, export/import.
- **`/api/v9/`** — Toggl API v9 compatibility (Basic Auth via `TogglBasicAuth` middleware). Maps vectors to Toggl "workspaces" for StreamDeck and other Toggl clients.

### Frontend

- Dark theme with custom Tailwind colors defined in `resources/css/app.css` (surface, accent, danger, success palette)
- Chart.js for report visualizations
- Blade layouts: `components/layouts/app.blade.php` (auth), `guest.blade.php` (login)

### Auth

- Google OAuth 2.0 via Laravel Socialite (web login)
- Sanctum personal access tokens (API)
- Basic Auth for Toggl v9 compat layer

### i18n

Translation files in `lang/en/` and `lang/pt_BR/`. UI strings referenced as `app.key_name`.

### Testing

- PHPUnit with SQLite in-memory database
- Config in `phpunit.xml` — overrides session/cache to `array`

### Deployment

Staging deploy via `make deploy`: builds assets, rsyncs to remote (excluding .git, vendor, node_modules via `.rsync-exclude`), copies `.env.testing` as `.env`, runs migrations and cache commands over SSH.
