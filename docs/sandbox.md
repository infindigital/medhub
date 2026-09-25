# Dev Sandbox (development data only)

A throwaway local WordPress used to build and review the theme until the Hostinger backup arrives. **It is not a copy of medhub.ae. It has no connection to production and runs no production code or data.**

| | |
|---|---|
| Stack | WordPress 6.9.9 + WooCommerce 10.7.0 (same versions as live), SQLite, PHP 8.2 (LocalWP's binary), PHP built-in server |
| URL | http://localhost:8080 (admin: `dev` / `dev`, local only) |
| Location | `sandbox/` (git-ignored). The theme is a **junction** to `medhub-theme/`, so edits show immediately |
| Data | `tooling/sandbox/dev-seed.json` is a read-only snapshot of the **public** medhub.ae catalogue (Store API / REST API, 25 Sep 2026): 33 categories, 34 brands, 202 products (one image each), 12 blog posts (excerpt only), placeholder pages at the live URLs |
| Marking | Every seeded record has `_medhub_dev_seed = 1`. A "DEV SANDBOX · development data" label shows on every page |
| Mirror | `tooling/sandbox/mu-plugins/medhub-sandbox-mirror.php` shows prices as "AED …" like the live store. It is sandbox-only and not part of the theme |

## Commands (from the repo root, in Git Bash)
```bash
sandbox/serve.sh                                                # start http://localhost:8080
npm run watch                                                   # rebuild CSS/JS on save
sandbox/wp.sh eval-file "$(pwd -W)/tooling/sandbox/seed.php" catalog   # (re)seed catalogue – idempotent
sandbox/wp.sh eval-file "$(pwd -W)/tooling/sandbox/seed.php" home      # reset homepage content from the pattern
```

## What is NOT real here
- Stock, prices and featured flags are the public values as of the snapshot date. They will drift from live.
- Pages other than the homepage are placeholders. Blog posts contain excerpts only.
- No payment gateway, shipping, tax or Rank Math is installed. **Checkout, Telr and SEO output must be tested on the LocalWP copy of the real backup** (see `local-setup.md`).
- Business details follow `medhub-theme/config/business.php`: "site-sourced" values are visible here because this is not production.

## Deleting the sandbox
Stop the server and delete `sandbox/`. Nothing else depends on it.
