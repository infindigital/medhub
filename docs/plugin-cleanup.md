# Plugin and theme cleanup (Option B): LOCAL copy

*Done on `http://medhub.local` only. Nothing was deleted, and medhub.ae is unchanged.*

- **Plugins:** deactivated with `tooling/local/plugins.php` (`status` / `deactivate` / `restore`). The original plugin list is stored in the option `_medhub_legacy_active_plugins`.
- **Content:** migrated with `tooling/local/apply-content.php` (`apply` / `posts` / `rollback` / `posts-rollback`).
- **Before/after checks:** `docs/cleanup-before.json` and `docs/cleanup-after.json`, from `tooling/local/page-check.mjs`.

## 1. What each removed plugin was used for (checked before deactivation)

| Plugin | Used for on the live site | Depends on it after the migration |
|---|---|---|
| **Elementor** 4.1.4 | 10 designed pages (converted in Milestone 2); 5 legacy pages; 4 blog posts (one HTML widget each); 2 posts with a stale Elementor flag over block content | Nothing. The posts are now built-in blocks. The 5 legacy pages show Elementor's saved HTML fallback (same text and images, without Elementor's styling) until they are redirected (§4) |
| **Ultimate Addons for Elementor (HFE)** | 28 Elementor header/footer/custom templates of the old theme | Nothing (the theme has its own header and footer) |
| **Nasa Core** 6.3.7 | Elessi's companion: `[nasa_title]` and `[nasa_products]` on `/compare/`; brand logos, compare and quick-view hooks on products | Nothing. The theme's Nasa Core workaround was removed as well |
| **Slider Revolution** 6.7.38 | 24 sliders stored; **none used** in any page, post or widget | Nothing |
| **YITH WooCommerce Compare** | `[yith_woocompare_table]` on `/yith-compare/` and "Compare" links on product pages | Nothing (comparison is not part of the design) |
| **Super Fast WP** | Page cache, HTML minify, JS defer (see `docs/production-fix-super-fast-wp.md`) | Nothing |
| **MedHub Floating Contact Buttons** / **MedHub Mobile Contact Banner** | WhatsApp and call buttons with a hardcoded, unconfirmed number (+971 52 814 2931) | Nothing. Replaced by the theme's floating buttons (§5) |
| **WPForms Lite** | **0 forms** | Nothing. No contact form is built yet; the contact page shows contact details |
| WPBakery (already inactive), Elessi + Elessi Child (inactive themes) | `[vc_row]`, `[vc_column]` and similar wrappers on `/shopping-cart/`, `/order-tracking/` and `/compare/` | Nothing. The theme unwraps these leftovers (`inc/content/legacy.php`) |

## 2. Pages checked before and after deactivation

| Page | Before | After |
|---|---|---|
| `/shopping-cart/` | Works | ✅ Works (the WooCommerce cart shortcode inside the old WPBakery wrappers is rendered) |
| `/order-tracking/` | Works | ✅ Works (tracking form present) |
| `/checkout/` | Works (redirects to the cart when the cart is empty) | ✅ Works. A test order was placed and then deleted |
| `/my-account/` | Login + register | ✅ Same |
| 6 blog posts (4 Elementor) | Render | ✅ Same text, links and images. Each article's own styles and toggle scripts still work |
| 5 legacy Elementor pages | Styled by Elementor | ⚠️ Same text and images, but **unstyled** (Elementor's HTML fallback). Decision needed: see §4 |
| `/compare/` | Raw `[vc_empty_space]` text + Nasa product widget | ⚠️ **Empty page** (its only content was the compare widget) |
| `/yith-compare/` | Raw `[yith_woocompare_table]` text (broken before) | ⚠️ **Empty page** |

## 3. Plugins still active (14)

| Plugin | Why it stays |
|---|---|
| WooCommerce, WooCommerce Analytics | Store |
| Telr Secure Payments | Card payments (credentials stay in WooCommerce settings, never in the theme) |
| Checkout Field Editor for WooCommerce | Configures the checkout fields; prints no design of its own |
| Rank Math SEO + Rank Math SEO PRO | Titles, descriptions, canonicals, schema, sitemaps, robots, breadcrumbs |
| WPCode Lite, Pixel Manager for WooCommerce | Tracking (see `docs/tracking-audit.md`). Pixel Manager is kept off at runtime by the local guard only |
| Code Snippets | Holds 2 active snippets that print nothing (tracking audit). Left for the ads manager to decide |
| Hostinger Reach | Email marketing (kept off at runtime by the local guard). Not part of the design; business decision |
| WP Mail Logging | Email log (admin) |
| WPvivid Backup | Backups |
| Disable XML-RPC, Limit Login Attempts (LLAR-Similar) | Security |

**Inactive and not needed** (can be deleted at go-live, after a backup): Elementor, Ultimate Addons for Elementor, Nasa Core, Slider Revolution, YITH Compare, Super Fast WP, the two MedHub contact plugins, WPForms Lite (unless a form is wanted), WPBakery, Contact Form 7, Smash Balloon, Google for WooCommerce, LiteSpeed Cache (unless chosen as the page cache), Hostinger Tools / Easy Onboarding. The Elessi, Elessi Child and Hostinger AI themes are also not needed.

## 4. Legacy Elementor pages: recommendation (Stage 1 URL map)

No redirects have been created.

| URL | Now | Recommendation | Target |
|---|---|---|---|
| `/device-on-rent/` ("Elementor #4307") | 29-word stub of 4 images | **301** | `/product-category/medical-equipment-rental/` |
| `/inogen-portable-oxygen-concentrator/` | Old product page duplicate | **301** | `/brand/inogen/` |
| `/devilbiss-blue-cpap/` | Old product page | **301** | `/product-category/cpap/` |
| `/compact-525-oxygen-concentrator/` | Old product page | **301** | `/product/devilbiss-compact-525-5-ltr-oxygen-concentrator/` (the product exists) |
| `/vacuaide-portable-suction-unit/` | Old product page | **301** | `/product/portable-suction-machine-with-battery-vacuaide-qsu-drive-devilbiss-rental/` (exists), or `/product-category/suction-machine/` |
| `/compare/`, `/yith-compare/` | Empty now | **301** to `/shop/` (Stage 1: noindex) | `/shop/` |
| `/⁠sleep-apnea-machine-in-dubai/` (U+2060 in slug) | Designed landing page | Keep the page; **301** the broken slug to a clean one when approved | `/sleep-apnea-machine-in-dubai/` |

Redirects would be created in **Rank Math → Redirections** at go-live (the module isn't enabled yet, so no redirects table exists).

## 5. Floating contact buttons

`template-parts/components/floating-contact.php` takes numbers from `config/business.php` only:

| Number status | Result |
|---|---|
| Confirmed | Working WhatsApp and call buttons |
| Not confirmed, outside production | A labelled "Local preview · numbers not confirmed" version with dashed, disabled buttons |
| Not confirmed, on production | **Nothing** is printed |

`phone`, `whatsapp` and `email` were set to the live medhub.ae values and marked confirmed on 26 Sep 2026 (+971 52 814 2931, wa.me/971528142931, lifechoicemed@gmail.com; the contact page also lists +971 4 252 3424). The Rank Math schema still says +971 50 255 2219.

## 6. Production changes that will eventually be required

These are not done; each needs approval.

1. **Take a backup** first.
2. **Content** (same procedure as local, via `tooling/local/apply-content.php`, adapted for staging and production):
   - Convert the 10 designed pages to built-in-block content, keeping IDs, URLs, titles and Rank Math data.
   - Append the category copy to 11 category descriptions.
   - Convert the 4 Elementor posts to Custom HTML blocks and remove the stale flag on 2 posts.
3. **Theme:** upload the MedHub theme, set the logo (*Customize → Site Identity*, attachment `logo.png`), and activate it.
4. **Plugins:** deactivate the 9 plugins in §1. Delete them later, after a stable period.
5. **Super Fast WP:** switch off its page cache, minify and defer first (`docs/production-fix-super-fast-wp.md`), then deactivate it. Clear `wp-content/superfast-cache/`.
6. **Redirects (§4):** create them in Rank Math once approved.
7. **Business details:** confirm the phone, WhatsApp and email in `config/business.php`, plus the delivery fee (AED 27 charged vs AED 25 in the policies) and VAT by emirate.
8. **Tracking:** no change is needed (WPCode + Pixel Manager stay). Optionally clean up Code Snippets #5/#6 with the ads manager.
9. **Telr:** production keeps its live settings. Test mode is only for staging or local testing.

## 6. Minimal plugin set (26 Sep 2026, LOCAL copy)

On request, the local copy now runs **only**: WooCommerce, Telr Secure Payments, Rank Math SEO + PRO and Checkout Field Editor (plus the `medhub-local-safety` mu-plugin). Everything else in §3 and the inactive list was **moved out** of the WordPress install (not uninstalled, so no plugin data was removed) to `Local Sites/medhub/removed-2026-09-26/` (`plugins/`, `themes/`, `mu-plugins/`). The previous active-plugin list is stored in the option `_medhub_active_plugins_20260926`. Only the `medhub` theme and `twentytwentyfive` (WordPress fallback) remain.

**Consequences:** no tracking locally (WPCode, Code Snippets and Pixel Manager are gone; see `docs/tracking-audit.md`), no WPvivid backups, no email log, and no XML-RPC/login-limit plugins. Production is unchanged. Decide per plugin before go-live.

**Checked after the move:** the page-check results match the earlier snapshot exactly; Rank Math titles, descriptions, schema and sitemap are unchanged; product page, add to cart, cart, checkout and wp-admin work. Pages are about 1.2 KB lighter.

**To restore:** move the folders back into `wp-content/plugins/` and reactivate them.
