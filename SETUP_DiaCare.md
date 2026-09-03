# DiaCare setup

This archive is the existing DiaCare Laravel project with the Word-document-inspired UI applied. It is not a new application.

## Windows / VS Code setup

Open this folder in VS Code and run the following commands in the integrated terminal:

```powershell
composer install
npm install
php artisan migrate
php artisan optimize:clear
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`, register or log in, and then open `/dashboard`.

For live CSS and JavaScript updates during development, use two terminals:

```powershell
php artisan serve
npm run dev
```

Keep the existing `.env` file from your local project. The archive intentionally does not contain `.env`, database credentials, API keys, `vendor/`, `node_modules/`, `.git/`, or the local SQLite database.

## Required directories

The archive includes placeholders for `storage/framework/views`, `storage/framework/cache`, `storage/framework/sessions`, and `storage/logs`. If your extraction tool omits empty directories, recreate them with:

```powershell
New-Item -ItemType Directory -Force storage\framework\views
New-Item -ItemType Directory -Force storage\framework\cache
New-Item -ItemType Directory -Force storage\framework\sessions
New-Item -ItemType Directory -Force storage\logs
```

## What to expect

The shared app layout and navigation match the supplied design, and the authenticated dashboard includes the sidebar, summary cards, quick actions, medication panel, glucose trend chart, care reminders, and floating assistant control. The secondary health pages are styled frames backed by Laravel routes and sample controller data. The profile page persists the additional health-profile fields through the included migration.

Run `APP_ENV=testing php artisan test` in Bash, or set `$env:APP_ENV='testing'` before `php artisan test` in PowerShell, to run the test suite.
