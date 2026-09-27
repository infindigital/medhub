# Local WordPress Audit: restored medhub.ae backup

*Local copy only (`http://medhub.local`, LocalWP site `medhub`). Nothing here was done on medhub.ae.*

- **Source:** the WPvivid full backup `medhub.ae_wpvivid-d8d0657c6b109_2026-09-25-10-59`, 5 parts.
  - The originals stay untouched in `medhub-backup/`, which is git-ignored and never uploaded.
- **Restored:** 2026-09-25, through WPvivid on a fresh LocalWP site.
- **Audit method:** a read-only WP-CLI script, which reports counts and settings only. No customer, order or payment-key data was printed or copied.

This document records what the site holds as restored (the "baseline"). **Nothing has been deactivated, deleted or reconfigured.** The only local changes were:
- the safety guard, listed in section 11
- linking and activating the MedHub theme, recorded in section 12

## 1. Platform

| | Local copy | Live (per backup metadata) |
|---|---|---|
| WordPress | **6.9.9** | 6.9.9 |
| WooCommerce | **10.7.0** (DB 10.7.0) | 10.7.0 |
| PHP | 8.2.29 | 8.3.33 |
| Database | MySQL 8.0.35 | MariaDB 11.8.9 |
| URLs | `http://medhub.local` (rewritten by WPvivid) | `https://medhub.ae` |
| Permalinks | `/%postname%/` | same |

The restore completed without database errors.
- **Mid-restore errors:** two fatal errors were logged while WordPress core was being swapped from 7.1.2 back to 6.9.9 (`_wp_register_default_icon_collections`). They were transient and haven't recurred.
- **Ongoing notices:** these are PHP 8.2 deprecations from Telr (`wc-telr`: dynamic properties).
- **Hosting data:** the site's own title is "Medical Equipment Suppliers in Dubai | Buy Medical Supplies UAE".

## 2. Themes

- **Active:** Elessi Theme Child 1.0 (parent: **Elessi Theme 6.3.7**, NasaTheme).
  - It has 509 theme mods (Elessi options).
- **Also installed:** Hostinger AI theme 2.0.16, Twenty Twenty-Five, Twenty Twenty-Four and Twenty Twenty-Three.

## 3. Plugins

"Active" means active in the restored database.

| Plugin | Version | Active | Notes |
|---|---|---|---|
| WooCommerce | 10.7.0 | ✅ | HPOS on (`custom_orders_table_enabled = yes`) |
| WooCommerce Analytics | 0.9.12 | ✅ | |
| Telr Secure Payments (`wc-telr`) | 8.4 | ✅ | Gateway enabled in **LIVE mode** (`testmode = no`); see §7 |
| Checkout Field Editor for WooCommerce | 2.1.8 | ✅ | Billing fields customised (see §7) |
| Rank Math SEO | 1.0.277.2 | ✅ | |
| Rank Math SEO PRO | 3.0.102 | ✅ | |
| Elementor | 4.1.4 | ✅ | No Elementor Pro |
| Ultimate Addons for Elementor (Header Footer Elementor) | 2.9.2 | ✅ | 28 templates (2 headers, footers, custom blocks) |
| Nasa Core (Elessi companion) | 6.3.7 | ✅ | `nasa_*` shortcodes used on /compare/ |
| Slider Revolution | 6.7.38 | ✅ | |
| YITH WooCommerce Compare | 3.10.0 | ✅ | /yith-compare/ page |
| WPForms Lite | 2.0.0.2 | ✅ | **0 forms** stored |
| WP Mail Logging | 1.16.0 | ✅ | Logs mails in the DB |
| Code Snippets | 3.9.6 | ✅ | 2 active snippets: Google Ads gtag + a "Purchase" conversion event (§9) |
| WPCode Lite | 2.3.6 | ✅ | 2 snippets (site-wide head + body): Google Tag Manager `GTM-52GJSMTM` |
| Pixel Manager for WooCommerce | 1.58.10 | ✅ | **Blocked locally** by the safety guard |
| Hostinger Reach | 1.4.12 | ✅ | **Blocked locally** by the safety guard |
| MedHub Floating Contact Buttons | 1.4 | ✅ | Custom plugin |
| MedHub Mobile Contact Banner | 1.3 | ✅ | Custom plugin |
| Super Fast WP (optimizer) | 1.0.0 | ✅ | |
| Disable XML-RPC | 1.0.0 | ✅ | |
| Limit Login Attempts (LLAR-Similar) | 1.0.0 | ✅ | |
| WPvivid Backup | 0.9.135 | ✅ | Used for this restore |
| WP Mail SMTP | 4.6.0 | ❌ | Inactive; the guard would block it anyway |
| LiteSpeed Cache | 7.9.1 | ❌ | |
| Contact Form 7 | 6.1.3 | ❌ | The Contact page still contains a `[contact-form-7]` shortcode |
| Google for WooCommerce | 3.4.3 | ❌ | |
| Hostinger Tools / Easy Onboarding | 3.0.66 / 2.1.21 | ❌ | |
| Smash Balloon Instagram Feed | 6.12.0 | ❌ | |
| WPBakery Page Builder | 7.7.2 | ❌ | `vc_*` shortcodes remain on /compare/, /shopping-cart/, /order-tracking/ |

**Must-use plugins:**
- `hostinger-auto-updates.php`, which disables auto-updates.
- `hostinger-preview-domain.php`, Hostinger's temporary-domain helper.
- `medhub-local-safety.php`, which is local only (§11).

## 4. Catalogue

| | Count |
|---|---|
| Products, published | **202** (all *simple*: 0 variable, 0 variations) |
| Products, pending review / draft | 25 / 8 |
| In stock / out of stock / on backorder | 148 / 48 / 6 |
| With a sale price | 77 |
| With a product gallery | 141 |
| Without a featured image | 0. A sample of 40 images: all files present on disk |
| Without a price | 4: `portable-suction-machine-with-battery-vacuaide-qsu-drive-devilbiss-rental`, `syringe-pump-hp-30-rental`, `3-function-electrical-bed`, `flaem-aspira-go-20-lpm-portable-aspirator` |
| With an SKU | 2 |
| With product attributes | 0. Global attributes `pa_color` (7 terms) and `pa_size` (5) exist but are unused |
| Built with Elementor | 0 |
| Product tags | 66 |
| Shipping classes | 1 (`van`) |
| Approved product reviews | 0 |
| Media library | 827 attachments |

**Categories (`product_cat`, base `/product-category/`).** There are 34 terms, all top level:
- Oxygen Therapy `oxygen-concentrator` (18)
- Oxygen Concentrator `buy-oxygen-concentrator` (5)
- Portable Oxygen Concentrator (6)
- Oxygen Concentrator Accessories (5)
- CPAP `cpap` (2)
- Auto CPAP (3)
- Travel CPAP (1)
- BiPAP in Dubai `bipap` (3)
- Auto BiPAP (2)
- CPAP/BiPAP Masks `buy-cpap-bipap-masks` (13)
- CPAP/BiPAP Accessories (11)
- Sleep Support & Comfort Solutions (6)
- Ventilation `portable-ventilator` (**0**)
- Ventilator Accessories (11)
- Suction Machine (5)
- Tracheostomy Care (23)
- Hospital Furniture (15)
- Patient Bed (7)
- Feeding Pump (3)
- Feeding Pump Accessories (5)
- Feeding Milk (4)
- Infusion Therapy (1)
- Nasogastric Tubes (1)
- Therapy & Massage (1)
- Health Monitoring (18)
- Patient Monitor (2)
- Vital Sign Monitor (2)
- Pulse Oximeter (3)
- Electrocardiogram (3)
- Disposables `medical-disposables` (4)
- Hygiene & Infection Control (25)
- Wound Care (11)
- Rental `medical-equipment-rental` (13)
- Uncategorized (4)

All 33 slugs used by the theme's department map (`medhub-theme/config/departments.php`) exist.

**Brands (`product_brand`, WooCommerce core brands, base `/brand/`).** There are 36 terms. **7 have no products:**
- aerogen
- best-in-rest
- bmc-healthcare
- longfian-scientific
- masimo
- romsons
- zephair

Largest brands: ResMed (8), Choice One Medical (8), Contour (6), Mölnlycke (6), Abbott (5).

*Note:* the homepage copy drafted in the sandbox lists "Masimo" as a brand. Masimo has **0** products in the real catalogue, so the pattern now says "Abbott" instead.

**Rental** is a product category, not a product type. No rental-specific meta or plugin exists; the "per month" wording lives in product content.

## 5. Pages and posts

- **32 published pages.**
- **The front page is #3806** "medhub" (slug `elm-medical-v1-2024-01-30-13-28-52`), an Elementor page. There is no posts page.
- **Elementor-built:**
  - about `/medical-equipment/`
  - `/oxygen-concentrator-in-dubai/`, `/bipap-in-dubai/`, `/cpap-in-dubai/`, `/oxygen-machine-in-dubai/`, `/portable-oxygen-machine-in-dubai/`
  - `/contact-us-medhub/`
  - `/⁠sleep-apnea-machine-in-dubai/`. The slug contains an invisible U+2060 character (`%e2%81%a0`).
  - `/devilbiss-*`, `/device-on-rent/` (titled "Elementor #4307"), `/inogen-portable-oxygen-concentrator/`, `/compact-525-oxygen-concentrator/`, `/vacuaide-portable-suction-unit/`
- **Block editor:** the policies:
  - privacy, cookie, delivery, cancellation, refund, return
  - both terms pages (`/terms-conditions/` and `/terms-and-conditions/` are duplicates)
  - multiple-shipments, payment-confirmation
  - /yith-compare/, /my-account/
- **WooCommerce pages:**
  - shop #8 `/shop/`
  - cart #262 `/shopping-cart/`: classic `[woocommerce_cart]` inside WPBakery rows
  - checkout #10 `/checkout/`: classic `[woocommerce_checkout]`, Elessi template `page-checkout.php`
  - my account #11
  - *no Terms page is assigned in WooCommerce*
- **12 published posts.** All 12 slugs match the URL inventory. There are 7 Elementor-built posts. Blog categories in use: `cpap-and-bipap` (5), `medical-equipment` (4), `oxygen-machine` (3). There are 7 empty demo categories.
- **Menus:** Main Menu (19 items, location `primary`), Category (9), Footer menu (7).

## 6. WooCommerce settings (read, not changed)

| Setting | Value |
|---|---|
| Currency | AED, left with space, 2 decimals |
| Selling / shipping countries | UAE only |
| Taxes | **On**. Prices entered *excluding* tax and displayed excl. VAT 5% applies **only when the state is `DXB`**. There is also a 0% rate for the `zero-rate` class |
| Shipping | 1 zone "Every Where" (no locations = matches everything), **Flat rate AED 27** |
| Coupons | Enabled. No coupons exist |
| Guest checkout | Yes |
| Stock management | Yes. Out-of-stock items are shown |
| AJAX add to cart | Yes. No redirect to the cart |
| Reviews | Enabled (none approved) |
| Catalogue | 4 columns, default sort `menu_order` |
| Product editor | Classic (new product block editor off) |
| Orders | HPOS. 486 orders (324 processing, 61 completed, 93 cancelled, 8 failed) |
| Customers | 106 customer accounts, 2 administrators |

⚠️ **Delivery fee mismatch:** WooCommerce charges **AED 27**, but the Delivery Policy page and the theme's business config (`site-sourced`) say **AED 25**. The client must confirm the right figure before any copy mentions a fee.

⚠️ **VAT only for `DXB`:** orders from other emirates may be calculated without VAT. Flag for the client or their accountant. This is not a theme issue and wasn't changed.

## 7. Checkout and payments

- **Checkout** is the classic shortcode (not the Checkout block), so it's styleable with the theme and all WooCommerce hooks keep firing.
- **Checkout Field Editor** controls these billing fields:
  - required: first/last name, country, address 1, city, phone, email
  - optional: company, address 2, state, postcode
  - shipping and additional fields are default
- **Gateways:**

  | Gateway | Status |
  |---|---|
  | `cod` "Submit Details to Medhub.ae" | ✅ enabled |
  | `wctelr` "Pay using a credit or debit card via Online Payments" | ✅ enabled, **live mode**, store ID set |
  | Telr Apple Pay | off |
  | BACS / cheque | off |

- **Locally,** the guard offers `cod` only. Telr stays hidden until it is switched to TEST mode on this copy and `MEDHUB_LOCAL_TELR_TEST_CONFIRMED` is set (milestone 2G). The live Telr keys are in the local DB, so **never enable it locally without test mode.**

## 8. SEO (Rank Math, read only)

- **Modules:**
  - sitemap, rich snippets, WooCommerce
  - local SEO, analytics, link counter
  - content AI, instant indexing, role manager, AI visibility, among others
- **Sitemaps:** posts, pages and products **on**. Product categories and brands **off** (the category and brand archives are not in the sitemap). 200 per page, images included.
- **Breadcrumbs:** on. No base stripping for category, product or product category.
- **Stored values:**
  - 106 focus keywords
  - 27 custom SEO titles
  - 24 custom meta descriptions
- **Redirections:** the redirections table is not present, so there are no redirections.
- **Baseline:** `docs/seo-baseline-elessi.json` holds the title, description, canonical, robots, H1s and schema types of all 314 inventory URLs, rendered locally with Elessi before the theme switch. It is used for comparison in later steps.

## 9. Tracking and marketing (neutralised locally)

- **WPCode:** Google Tag Manager `GTM-52GJSMTM` (head + body snippets).
- **Code Snippets #5:** Google Ads `gtag` `AW-11210110631`.
- **Code Snippets #6:** a "Purchase (1)" conversion event. It isn't printed on normal pages (checked on / and /shop/).
- **Other tags:** a GA4 tag `G-W4CVQTNBZK` is printed in the page, and Pixel Manager and Hostinger Reach are also installed.
- **Locally:** all tag URLs are rewritten to `tracking-blocked.invalid`, and Pixel Manager and Reach are switched off at runtime.

## 10. Other observations

- **Elessi and page-builder leftovers:**
  - Two "Compare" pages (`/compare/` Elessi and `/yith-compare/`).
  - Duplicate terms pages.
  - WPBakery shortcodes on three pages while WPBakery is inactive.
- **Empty WPForms:** WPForms Lite is active but holds no forms. The Contact page is Elementor, with a Contact Form 7 shortcode while CF7 is inactive, so the live contact form may not be working. Check on live before relying on it.
- **Scheduled actions:** 20 are pending (WooCommerce cleanup, WPForms, Hostinger Reach cart cleanup and others). None run locally.

## 11. Local safety measures (local copy only)

- **`wp-config.php`**, block "MedHub LOCAL COPY safety". The original is kept as `Local Sites/medhub/wp-config.original.php`. It sets:
  - `MEDHUB_LOCAL_COPY`
  - `DISABLE_WP_CRON`
  - `WP_HTTP_BLOCK_EXTERNAL`
  - no auto-updates
  - a debug log
  - `MEDHUB_LOCAL_TELR_TEST_CONFIRMED = false`
- **`wp-content/mu-plugins/medhub-local-safety.php`** (source: `tooling/local/mu-plugins/`) handles:
  - offline gateways only
  - all mail forced through PHP `mail()`, which goes to Mailpit (SMTP 127.0.0.1:10013)
  - WP Mail SMTP, Pixel Manager, Hostinger Reach and Jetpack off at runtime
  - no WP-Cron and no Action Scheduler async runner
  - `noindex, nofollow`
  - tracking URLs neutralised
  - an admin-bar badge
- The guard is keyed to the local wp-config marker and the request host. It is **not** keyed to the DB `home` option, which says medhub.ae straight after a restore.

## 12. MedHub theme on the local copy

**Linking and activation:**
- The theme folder is linked through a junction: `wp-content/themes/medhub` → `medhub-theme/`.
- It was activated with `tooling/local/wp.sh theme activate medhub`.
- Elessi is untouched; re-activating it restores the old look.

**Only DB writes, all local:**
- the active theme
- `theme_mods_medhub.custom_logo = 3810` (the existing `logo.png`). This is what *Customize › Site Identity* would set, and it must be repeated on production at switch time.
- the test cart session

**Verified in Chrome at 1907×867, 1440×900 and 390×844:**

| Check | Result |
|---|---|
| All page types (home, shop, category, brand, product, landing, about, contact, policy, blog post, cart, account, search, 404) | 200, no new PHP warnings or notices in `debug.log` |
| Horizontal overflow | 0 px on every page and width tested |
| Homepage | New design. Hero rotates 4 **real** products (Inogen Rove 6 #5411, Philips DreamStation Auto BiPAP #5287, Healthward bed #4575, Yuwell 8F-5A #6552). 47 real product cards with live prices and stock. The front page (#3806) has no MedHub blocks yet, so the "Homepage (MedHub)" pattern renders as a preview, with a notice |
| Shop | 202 products, real brand and price filters, product cards with image, category, name, AED price, stock, Add to cart |
| Product (Inogen Rove 6) | Real title, gallery (5 images), AED 9,399.00, "In stock", category and brand eyebrow, description, 4 related products. Rank Math title and Product and BreadcrumbList schema are present |
| Add to cart | The WooCommerce form submits. Notice: "has been added to your cart", header count 0 → 1 |
| Cart | 1 line item. Subtotal AED 9,399.00, flat rate AED 27.00, total AED 9,426.00 |

**Theme fixes needed for the real site** (in the repo):
- `inc/woocommerce/compat.php`:
  - "AED" currency label. It was in the Elessi child theme's `functions.php`.
  - Detaches Nasa Core output (brand logo block etc.) from product and loop display hooks only. Its cart, checkout and price hooks are untouched.
- `medhub_reveal_words()` joins words with `&#32;`. Super Fast WP's `str_replace("> <", "><")` had glued the hero H1 into one word.

**Found during testing (live-site issues, not changed):**
- 🔴 **Super Fast WP page cache** (active on live with default settings; no `sfwp_options` row exists):
  - It caches every guest GET page by URL, including `/shopping-cart/`, `/checkout/` and `/my-account/`, ignoring WooCommerce's no-cache signals.
  - Locally, a fresh visitor was served another guest's cart for up to 1 hour.
  - On live this can show one guest's cart, and possibly pre-filled checkout details, to others.
  - Locally the page cache is switched off at runtime by the safety guard.
- Super Fast WP's JS `defer` causes `wp is not defined` and `elementorFrontendConfig is not defined` errors on live pages. Its whitespace stripping can join adjacent inline words site-wide.
- Local page generation takes about 5–8 s per page with or without the theme (plugin load). There is no caching locally.

## 13. Follow-ups for later milestones

- The delivery fee (27 vs 25) and VAT-by-state questions go to the client.
- Homepage and landing copy must only name brands that have products (the Masimo mention is already fixed).
- Decide whether the Elementor landing pages get rebuilt as MedHub-block content (in WordPress, per content-architecture.md), or whether their Elementor content renders inside the theme layout.
- The Contact form: the CF7 shortcode is on the page but the plugin is inactive.
- The two Compare pages, the duplicate terms pages and the invisible character in the sleep-apnea slug are cleanup candidates. They are listed only; nothing has changed.
