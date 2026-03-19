# GitHub + Hostinger Safe Deploy

## Goal
Use GitHub for code deployment without overwriting live secrets or production data.

## What will NOT be lost if you follow this flow
- MySQL database data
- Production `.env`
- `storage/stores.json` and runtime JSON files

Reason:
- Database lives in MySQL, not in the git repo.
- `.env` is ignored by git.
- Runtime JSON files are ignored by git.

## Before first deploy
1. Export a backup of the production database from phpMyAdmin.
2. Download a backup copy of these files from the server:
   - `.env`
   - `storage/stores.json`
3. Keep the current production path unchanged.

## Safe repository rules
Do not commit:
- `.env`
- `storage/*.json`
- `public/storage/*.json`

Commit only code:
- `src/`
- `public/`
- `database/schema.sql`
- docs and config templates

## Recommended production structure on Hostinger
- Code directory: `public_html/app`
- Production `.env`: stays on server only
- Production storage JSON: stays on server only
- Database: stays in MySQL

## Deploy strategy
### Option A: Pull repo into the existing production folder
Good when production files already exist and you want to keep paths unchanged.

Steps:
1. Initialize git locally.
2. Push code to GitHub.
3. On Hostinger connect/pull the repo into the same app folder.
4. Make sure these files remain untouched on server:
   - `.env`
   - `storage/stores.json`

### Option B: Deploy to a staging folder first
Safest option.

Steps:
1. Keep live app in current folder.
2. Pull GitHub repo into a new folder like `public_html/app-release`.
3. Copy production `.env` into the new folder.
4. Copy `storage/stores.json` into the new folder.
5. Test.
6. Swap traffic or replace current code after validation.

## Update flow after first setup
1. Make code changes locally.
2. Commit and push to GitHub.
3. On Hostinger deploy/pull latest code.
4. Do not replace `.env` or runtime JSON.
5. If database schema changes, apply SQL manually first or immediately after deploy.

## Important note
If you ever delete the production folder entirely and recreate it from git without copying back `.env` and `storage/stores.json`, you may lose app runtime configuration and connected-store cache. The MySQL database itself will still remain unless you explicitly delete the database.
