# Tracking audit

*Read-only audit.*
- **Sources:** the restored local database (`http://medhub.local`, IDs and settings only) and the public HTML of `https://medhub.ae/`, fetched 2026-09-25.
- **Changes:** none to production tracking. None of it moves into the theme.

## What is injected, and where from

| Tag | ID | Provided by | How | Printed on live? |
|---|---|---|---|---|
| **Google Tag Manager** (head script) | `GTM-52GJSMTM` | **WPCode Lite**, snippet #5753 "Head" | Auto-insert, location *Site Wide Header* (`wp_head`) | ✅ `gtm.js` ×1 |
| **Google Tag Manager** (noscript iframe) | `GTM-52GJSMTM` | **WPCode Lite**, snippet #5754 "Body" | Auto-insert, location *Site Wide Body* (`wp_body_open`) | ✅ `ns.html` ×1 |
| **Google Analytics 4** | `G-W4CVQTNBZK` | **WPCode Lite**, snippet #5753 "Head" (same snippet as GTM) | gtag.js | ✅ ×1 |
| **Google Ads** tag | `AW-11210110631` | **WPCode Lite**, global *Header & Footer → Header* box (option `ihaf_insert_header`) | gtag.js + `config` | ✅ `gtag/js?id=AW-…` ×1 |
| **Google Ads conversions + dynamic remarketing** | `AW-11210110631`, label `iG3MCPDDtdEaEKftsuEp`, Merchant Center `5068491764` | **Pixel Manager for WooCommerce** 1.58.10 | Its own `pmwDataLayer` script, consent mode on, order deduplication on | ✅ ("START/END Pixel Manager" block) |
| Google Ads gtag (duplicate copy) | `AW-11210110631` | **Code Snippets** #5 "new", active, *global* scope | HTML pasted into a PHP-scope snippet | ❌ No output (not printed locally or on live) |
| Google Ads "Purchase (1)" event | `AW-11210110631/Ey1gCOz1zPcZEKftsuEp` | **Code Snippets** #6 "fdf", active, *global* scope | Same as above | ❌ No output |
| Google for WooCommerce conversion | `AW-11210110631`, label `l5s1CIje65EZEKftsuEp` | *Google for WooCommerce* (plugin **inactive**) | Leftover option `gla_ads_conversion_action` | ❌ |
| **Meta (Facebook) Pixel** | none | not installed anywhere | none | ❌ No `fbq(` / `facebook.com/tr` |
| Microsoft Clarity, Hotjar, TikTok pixel | none | none | none | ❌ (the "tiktok" hits are the footer social link) |

**Other references (not trackers):**
- `gtm_server_side_web_container_id = GTM-52GJSMTM` is a stored option, with no server-side container configured.
- **Seven trashed** WPCode snippets hold older Ads conversion and "WhatsApp Button Click" code. They are not active.

## Does any of it depend on the old theme or plugins being removed?

**No.** Every active tag is printed by **WPCode Lite** and **Pixel Manager**, through standard WordPress hooks:

- `wp_head`: GTM, GA4, Google Ads gtag, Pixel Manager
- `wp_body_open`: the GTM noscript iframe
- `wp_footer`: Pixel Manager scripts

**The MedHub theme calls all three hooks**, verified in the local page output. Elementor, Elessi, Nasa Core, Slider Revolution, WPBakery, YITH Compare and Super Fast WP do not provide any tracking.

**Pixel Manager's WooCommerce events** (view_item, add_to_cart, begin_checkout, purchase) come from WooCommerce hooks and the `woocommerce_thankyou` page. None of those templates are overridden by the theme, so the events keep working.

## What must remain after the cleanup

| Keep | Why |
|---|---|
| **WPCode Lite** (snippets #5753, #5754 and the global header box) | Provides GTM, GA4 and the Google Ads base tag |
| **Pixel Manager for WooCommerce** | Google Ads purchase conversions and remarketing, with consent mode |

**Not required (tidy-up candidates, decide with the client or their ads manager; not changed now):**
- **Code Snippets #5 and #6:** they print nothing. If the intent was a second Ads tag and a purchase event, Pixel Manager already covers purchases. Deactivating them later has no visible effect.
- **Google Ads loaded in two ways:** once by WPCode's header box, and once inside Pixel Manager's own loader. This is not a double tag in the HTML, but the ads manager should confirm conversions aren't counted twice. (Pixel Manager has order deduplication on.)
- **GTM `GTM-52GJSMTM` and GA4 `G-W4CVQTNBZK`:** it's worth checking whether the GTM container also fires GA4 or Ads. If it does, those would double-count.

## Local copy

The local safety guard keeps tracking off locally:
- It rewrites tag URLs to `tracking-blocked.invalid`.
- It switches Pixel Manager and Hostinger Reach off at runtime.

This prevents test traffic reaching Google. It is local only (`tooling/local/mu-plugins/medhub-local-safety.php`), and nothing is written to the database.
