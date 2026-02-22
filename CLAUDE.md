# Elma Note – CLAUDE.md

## Projektübersicht

Evernote-Klon mit Laravel 11 Backend, Vue 3 + Inertia.js Frontend, MySQL Datenbank.
Deployment auf Railway via `railway up`.

## Tech Stack

| Schicht    | Technologie                         |
|------------|-------------------------------------|
| Backend    | Laravel 11 (PHP 8.4)               |
| Frontend   | Vue 3 + Inertia.js + Tiptap         |
| Datenbank  | MySQL 8.4                           |
| Build      | Vite 7 + `@vitejs/plugin-vue` v6    |
| Auth       | Laravel Breeze (Inertia/Vue)        |
| Deployment | Railway + Dockerfile (Multi-Stage)  |

## Wichtige Konventionen

- **Datenbank: MySQL** – kein PostgreSQL (hatte Railway-Probleme)
- **Inertia.js** als Brücke zwischen Laravel und Vue – kein separates SPA, keine REST-API
- **Eine Railway-Service** für Backend + Frontend (kein Split-Deployment)
- Pages liegen in `resources/js/Pages/`, nicht in `resources/views/`
- Das Blade-Layout für Inertia ist `resources/views/app.blade.php`
- `@vitejs/plugin-vue` muss Version **6.x** sein (v5 ist mit Vite 7 inkompatibel)

## Projektstruktur

```
app/
  Http/Controllers/
    NoteController.php       # CRUD + Trash/Restore
    NotebookController.php
    TagController.php
  Models/
    Note.php                 # SoftDeletes, Fulltext-Scope
    Notebook.php
    Tag.php
    User.php                 # notes(), notebooks(), tags() Relations
  Policies/
    NotePolicy.php           # Ownership-Check (user_id)
    NotebookPolicy.php
    TagPolicy.php

resources/js/
  Layouts/
    AppLayout.vue            # Sidebar mit Notebooks, Tags, Navigation
  Components/
    Editor.vue               # Tiptap Rich-Text-Editor
  Pages/
    Notes/
      Index.vue              # Notizliste + Suche
      Show.vue               # Editor-Ansicht (autosave via patch)
      Create.vue
      Trash.vue

routes/
  web.php                    # Unsere App-Routes (Breeze hat das überschrieben – wiederhergestellt)
  auth.php                   # Von Breeze generiert
```

## Lokale Entwicklung

```bash
make init    # Einmalig: Docker starten, dependencies, migrate
make start   # Docker starten + Vite dev server
make stop    # Docker stoppen
make migrate # Neue Migrations ausführen
make fresh   # DB komplett neu aufsetzen (Achtung: löscht Daten)
```

Manuell:
```bash
docker compose up -d
docker compose exec app php artisan migrate
npm run dev
```

Laravel läuft auf http://localhost:8000, Vite auf http://localhost:5173.

## Railway Deployment

```bash
railway login
railway init                        # Einmalig: Projekt anlegen
railway add --database mysql        # MySQL-Service hinzufügen
railway variables set APP_KEY=$(php artisan key:generate --show)
railway up                          # Build + Deploy
railway run php artisan migrate --force   # Nach erstem Deploy
```

Umgebungsvariablen die Railway automatisch setzt (aus dem MySQL-Service):
- `MYSQL_URL` / `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

## Deployment-Architektur (Dockerfile)

Multi-Stage Build:
1. **Stage `frontend`** (node:22-alpine): `npm ci` + `vite build` → Assets in `public/build/`
2. **Stage `production`** (php:8.4-fpm-alpine): Composer install + nginx + supervisord

`railway.json` führt beim Start aus:
```
php artisan migrate --force && php artisan optimize && supervisord
```

## Bekannte Fallstricke

- **Breeze überschreibt `web.php`** beim Install – unsere Routes manuell wiederherstellen
- **`@vitejs/plugin-vue` v5 + Vite 7** = Peer-Dep-Konflikt → immer v6 verwenden
- **`tiptap` bare package** hat npm-Konflikte → nur `@tiptap/*` Scoped-Pakete installieren
- **Laravel 11** hat `AuthorizesRequests`-Trait aus dem Base-Controller entfernt →
  manuell in `app/Http/Controllers/Controller.php` hinzugefügt
- **MySQL Fulltext-Index** (`notes_fulltext`) braucht InnoDB Engine – ist Standard bei MySQL 8

## Datenbank-Schema

```
users           – id, name, email, password
notebooks       – id, user_id, name, color
notes           – id, user_id, notebook_id, title, content (HTML), content_json,
                  is_favorite, is_pinned, deleted_at (soft delete)
tags            – id, user_id, name, color
note_tag        – note_id, tag_id (pivot)
```
