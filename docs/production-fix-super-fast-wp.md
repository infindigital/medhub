# Production fix: stop "Super Fast WP" from serving shared pages to shoppers

*Status: **documented only, not applied.** Apply on medhub.ae only after the client approves. Take a fresh backup first.*

## What's wrong

The plugin **Super Fast WP – Frontend & Backend Optimizer** 1.0.0 (`wp-content/plugins/super-fast-wp-plugin/`) is active on medhub.ae. Its header reads *"Author: ChatGPT (generated)"*.

Its settings have never been saved: there is no `sfwp_options` row, so the defaults apply. The defaults are:

| Setting | Default | Effect on medhub.ae |
|---|---|---|
| Page cache (guests) | **on**, TTL 3600 s | Every guest GET page that returns 200 is stored in `wp-content/superfast-cache/cache_<md5(URL)>.html` and served to **every other guest** for 1 hour |
| Minify HTML | **on** | Removes spaces between tags (`"> <"` → `"><"`) and collapses whitespace inside inline `<script>` blocks |
| Defer JS | **on** | Adds `defer` to every enqueued script except jQuery |
| Combine assets | off | no effect |
| Lazy-load images | on | harmless |
| Reduce Heartbeat | on (60 s) | harmless, admin only |

### 1. Page cache: privacy and correctness (🔴 high)

- **What the cache ignores:** the cache key is the URL only. There are no exclusions for:
  - `/shopping-cart/`, `/checkout/` and `/my-account/`
  - WooCommerce's cart and session cookies (`woocommerce_items_in_cart`, `wp_woocommerce_session_*`)
  - WooCommerce's `DONOTCACHEPAGE` and `nocache_headers()` signals
- **Reproduced on the local copy:**
  1. A guest added a product to the cart and opened `/shopping-cart/`.
  2. A second visitor with **no cookies at all** then requested `/shopping-cart/`.
  3. They received the first guest's cart (`X-SFWP-Cache: HIT`).
- **Consequences on live:**
  - One guest's cart, and the header cart count, can be shown to other guests.
  - A guest's **pre-filled checkout details** (name, phone, address and email, which WooCommerce refills from the session when a guest returns to `/checkout/`) can be stored in a cache file and shown to others.
  - Stale nonces and cart fragments cause failed add-to-cart and checkout requests for other visitors.
  - Old copies (up to 1 h) of product pages show stale prices and stock.
- **Local check only:** this was checked on the local copy only. We did not test it on live, because that would write cache files there.

### 2. Minify HTML and Defer JS: broken output (🟠 medium)

- **Glued words:** removing spaces between tags joins words that sit in separate inline elements. The new theme is already hardened against this for its hero heading, but other plugins' output isn't.
- **Broken inline scripts:** collapsing whitespace in inline scripts turns a `// comment` line into a comment that swallows the rest of the script. The result is `SyntaxError: Unexpected end of input`.
- **Defer errors:** `defer` on scripts that inline code depends on causes `ReferenceError: wp is not defined` and `elementorFrontendConfig is not defined`.

## Exact production-safe change (wp-admin, about 2 minutes)

No plugin is deactivated or deleted. Only this plugin's own settings change.

1. Take a fresh backup (WPvivid → Backup Now).
2. **wp-admin → Settings → Super Fast WP.**
3. Change the settings:
   - **Page cache (guests):** ☐ **untick** "Enable full-page caching for non-logged-in users".
     - This is the only safe value. The plugin has no URL or cookie exclusion setting, so it can't be made WooCommerce-safe while enabled.
   - **Minify HTML:** ☐ **untick**.
   - **Defer JS:** ☐ **untick**.
   - **Combine assets (advanced):** leave ☐ unticked.
   - Lazy-load images ☑ and Reduce Heartbeat ☑: leave as they are.
4. Click **Save Changes**.
5. Under *Cache management*, click **Clear full-page cache**.
   - This deletes all `cache_*.html` and `.meta` files in `wp-content/superfast-cache/`. Those files may contain other customers' cart or checkout HTML.
6. Purge any upstream caches:
   - Cloudflare → Caching → **Purge Everything**
   - Hostinger hPanel → Cache Manager → **Purge all**, if enabled

These are settings only. Nothing in WooCommerce, products, orders, customers or Rank Math changes.

## Verify afterwards (read-only)

Run these from any computer:

```bash
# 1. No plugin page cache header any more (repeat twice; both must lack X-SFWP-Cache)
curl -s -D - -o /dev/null -H "Accept: text/html" https://medhub.ae/shopping-cart/ | grep -i "x-sfwp\|cache-control\|cf-cache-status"
curl -s -D - -o /dev/null -H "Accept: text/html" https://medhub.ae/checkout/      | grep -i "x-sfwp\|cache-control\|cf-cache-status"
curl -s -D - -o /dev/null -H "Accept: text/html" https://medhub.ae/my-account/    | grep -i "x-sfwp\|cache-control\|cf-cache-status"
# Expected: no X-SFWP-Cache line, Cache-Control "no-cache, must-revalidate, max-age=0, private", cf-cache-status DYNAMIC
```

- **In two different browsers** (one private window), add different products. Each cart must show only its own items.
- **In hPanel File Manager,** `public_html/wp-content/superfast-cache/` must contain no `cache_*.html` files. It stays empty while the page cache is off.

## Rules any page cache on medhub.ae must follow

This applies whichever plugin is used: Super Fast WP, LiteSpeed Cache, Hostinger or Cloudflare.

**Never cache:**
- `/shopping-cart/`, `/checkout/` (including `/checkout/order-received/*` and `/checkout/order-pay/*`), `/my-account/*`, `/order-tracking/`
- the compare pages
- any URL with `add-to-cart`, `wc-ajax`, `remove_item`, `undo_item`, `wc-api` or `key=`

**Bypass the cache when these cookies are present:**
- `woocommerce_items_in_cart`
- `woocommerce_cart_hash`
- `wp_woocommerce_session_*`
- `wordpress_logged_in_*`
- `comment_author_*`

**Respect WooCommerce's signals:** `DONOTCACHEPAGE` and `Cache-Control: private/no-cache`.

**Header cart count:** update it through WooCommerce cart fragments (the MedHub theme already does this), never through cached HTML.

## If page caching is wanted later

**LiteSpeed Cache** 7.9.1 is already installed on the site but **inactive**. Hostinger runs LiteSpeed servers.

LiteSpeed Cache's WooCommerce integration follows the rules above by default. It excludes cart, checkout and account pages, and varies the cache on the cart cookies.

Activating it is a **separate decision**:
- Test it on the local or staging copy first.
- Never run two page caches at once, so the Super Fast WP page cache must stay off.

## Rollback

Re-tick the three boxes in *Settings → Super Fast WP*. This isn't recommended.
