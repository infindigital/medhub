# Local Staging Setup (LocalWP)

*This covers the local copy only. Nothing here touches medhub.ae.*

The steps are **in order**. The safety guard must be in place **before the imported site is first loaded in a browser**, because that first page load is when WordPress would otherwise run scheduled jobs and send emails.

## Prerequisites
- LocalWP is installed. It is already on this machine, with PHP 8.2, MySQL 8 and Mailpit.
- You provide the Hostinger backup: the database (`.sql`) plus `wp-content` (or an All-in-One WP Migration / Local-compatible `.zip`).
- Store the backup in `Medhub/backups/`, which git ignores. **It contains real customer data. Don't share or commit it.**

## 1. Create the site (don't open it yet)
1. In LocalWP choose *Import site* with the backup `.zip`. Alternatively, create an empty site and import the SQL through *Database › Adminer*.
   - Site name: `medhub`. Domain: `medhub.local`.
   - PHP: pick 8.3 if LocalWP offers it (live runs 8.3). Otherwise use 8.2.
2. For a manual SQL import, open *Site shell* and run:
   `wp search-replace 'https://medhub.ae' 'http://medhub.local' --all-tables --precise`
   Run it again for `//medhub.ae` if needed.
3. **Don't visit the site yet.**

## 2. Install the safety guard
1. Paste the contents of `tooling/local/wp-config-local.sample.php` into the site's `wp-config.php`, above *"That's all, stop editing!"*.
2. Copy `tooling/local/mu-plugins/medhub-local-safety.php` into `…/app/public/wp-content/mu-plugins/`. Create the folder if it doesn't exist.

**What the guard does.** Everything happens at runtime, and nothing is written to the database.

| Area | Protection |
|---|---|
| Payments | Only offline gateways (COD/BACS) are offered until Telr is in TEST mode and `MEDHUB_LOCAL_TELR_TEST_CONFIRMED` is `true` |
| Outbound HTTP from PHP | Blocked completely (`WP_HTTP_BLOCK_EXTERNAL`). This covers the Telr API, Hostinger, Jetpack, WooCommerce.com and Google |
| Email | Forced through PHP `mail()`, which goes to **Mailpit**, even if an SMTP plugin holds real credentials |
| Marketing and tracking plugins | Hostinger Reach, Pixel Manager (Google Ads) and Jetpack are switched off at runtime |
| Tracking tags in the page | GTM, GA, Google Ads, Meta and TikTok URLs are neutralised in the front-end HTML |
| Scheduled jobs | `DISABLE_WP_CRON` is set, and any due events are skipped |
| Search engines | `noindex, nofollow`. The site is never public anyway |
| Kill switch | Does nothing unless the environment is `local` and the host isn't medhub.ae |

**Caveat.** If someone activates or deactivates a plugin in wp-admin while the guard is on, WordPress saves the filtered plugin list. The blocked plugins then stay deactivated in the *local* database. That's harmless locally. Just be aware of it.

## 3. First load and checks
Open `http://medhub.local/wp-admin` and confirm each of these:
- [ ] The admin bar shows **"LOCAL COPY · safety on · offline gateways only"**.
- [ ] *Settings › Reading*: the search-engine visibility checkbox reads as discouraged (forced by the guard).
- [ ] *Tools › Site Health* shows the environment as **local**.
- [ ] Place a test order with COD. The order email appears in **Mailpit** (LocalWP › *Tools › Mailpit*) and **nowhere else**.
- [ ] View source on the home page: no `googletagmanager.com` and no Google Ads conversion scripts.
- [ ] `wp-content/debug.log` shows blocked HTTP requests instead of real calls.

## 4. Telr in test mode (optional, needed for step 2G payment tests)
1. *WooCommerce › Settings › Payments › Telr*: switch to **Test mode** and enter Telr **test** credentials. This is a local DB change, approved under D2. Never use the live keys.
2. Add `secure.telr.com` to `WP_ACCESSIBLE_HOSTS`.
3. Set `MEDHUB_LOCAL_TELR_TEST_CONFIRMED` to `true`.
4. Pay with Telr's test cards only.
5. Afterwards, set both back.

## 5. Capture the baseline (before activating the MedHub theme)
While the local copy still runs **Elessi**, capture:
- A crawl of all 315 URLs from `docs/url-inventory.csv` (status, title, meta, canonical, H1, robots, schema types).
- Screenshots of the home page, a category, a product, the cart and checkout.
- Plugin list and versions (*Plugins* screen), and the WooCommerce *System Status* report.

This baseline is what every later step gets compared against.

## 6. Link the theme from this repo
In **Command Prompt**. A junction doesn't need admin rights:
```
mklink /J "%USERPROFILE%\Local Sites\medhub\app\public\wp-content\themes\medhub" "%USERPROFILE%\Desktop\Infin Digital\Websites\Medhub\medhub-theme"
```
Then run `npm run watch` in this repo and activate **MedHub** in *Appearance › Themes* on the **local** site only.

## 7. Plugin conflict checks (D2)
- **Header Footer Elementor:** if the old Elessi header or footer appears around the MedHub theme, set `MEDHUB_LOCAL_DISABLE_HFE` to `true`.
- **nasa-core:** if you see PHP errors mentioning `nasa`/`elessi`, or stray wishlist or quick-view markup in product loops, set `MEDHUB_LOCAL_DISABLE_NASA_CORE` to `true`. Before that, search the content for `[nasa_` shortcodes.
- Neither plugin is deleted, and nothing changes on production.

## Rollback (local)
Activate Elessi again. The MedHub theme changes no data, so the site returns to exactly how it was.
