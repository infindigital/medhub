# Milestone 2: homepage, landing pages, test product, cart and checkout (LOCAL copy)

*Everything below happened on the LocalWP copy (`http://medhub.local`) only. medhub.ae was not touched, and nothing was pushed or deployed.*

## 1. Pages now editable in WordPress (MedHub blocks)

The pages were converted **in place** by `tooling/local/apply-content.php`:
- The **same page ID, URL, title, status and Rank Math meta** are kept.
- Elementor's data (`_elementor_data`) is kept untouched.
- The previous content, excerpt, page template and Elementor mode are stored in `_medhub_legacy_*` meta.
- `apply-content.php rollback` restores every page exactly, and Elementor renders it again.

| Page | ID | Source |
|---|---|---|
| Front page (`/`) | 3806 | `medhub-theme/patterns/home.html` |
| About `/medical-equipment/` | 3171 | Stage 2H draft |
| `/cpap-in-dubai/` | 5994 | draft |
| `/bipap-in-dubai/` | 6004 | draft |
| `/oxygen-concentrator-in-dubai/` | 6024 | draft |
| `/portable-oxygen-machine-in-dubai/` | 5958 | draft |
| `/oxygen-machine-in-dubai/` | 5952 | draft (had the Elessi `page-blank.php` template) |
| `/⁠sleep-apnea-machine-in-dubai/` (U+2060 slug kept) | 4325 | draft |
| `/devilbiss-service-centre-in-dubai/` | 4317 | draft |
| Contact `/contact-us-medhub/` | 3170 | draft. The excerpt is set to its previous auto description (see §5) |

**Category landing sections** (below the product grid, "Category content" entries): 11 categories. Their Stage 1 display headings are set in term meta `medhub_heading`, and the old value is kept in `_medhub_legacy_heading`.

**Editing, for the client:** open the page in *Pages → Edit*.
- Each section is a block, with its settings (texts, links, categories, limits, "Show products") in the right sidebar and a live preview.
- The FAQ and "why MedHub" cards are normal paragraph, heading and details blocks.
- No code changes are needed. Verified in the block editor: 0 invalid blocks on the homepage and on /cpap-in-dubai/.

**Not converted (still Elementor, rendered inside the MedHub layout):** these are the legacy pages that Stage 1 marked for 301 redirects. They need a decision: redirects are a Rank Math change.
- `/devilbiss-blue-cpap/`
- `/device-on-rent/` (titled "Elementor #4307")
- `/inogen-portable-oxygen-concentrator/`
- `/compact-525-oxygen-concentrator/`
- `/vacuaide-portable-suction-unit/`

The 7 Elementor-built blog posts also still render through Elementor, inside the article layout.

## 2. Nothing depends on specific products

- **Hero:**
  - With **Show products** on (homepage) or **Categories** set (landing pages), it shows the products starred **Featured** in WooCommerce: in stock, with an image. It fills up with the newest products after that.
  - Hand-picked slugs are optional.
  - Missing, unpublished or out-of-stock products are skipped.
- **Every other product section** already used live queries:
  - Featured / On sale / New tabs
  - category or brand lists
  - the rental category rail
  - the care-switcher categories
  - category and brand counts
- **The drafts no longer contain product slugs.** They now carry category slugs.

## 3. Test product: passed and deleted

`TEST - MedHub Product` was created through WooCommerce's product API (the same code path as *Products → Add New*):
- SKU `TEST-MEDHUB-001`
- AED 1,250, on sale at 999
- stock 5
- Featured
- category Portable Oxygen Concentrator, brand Inogen
- a generated test image and a visible attribute

| Check | Result |
|---|---|
| Homepage | First hero slide, and first in the Featured tab, automatically |
| Category page | Card with image, SALE badge, AED 999.00 / ~~1,250.00~~, In stock, Add to cart |
| Shop (Sort by latest), brand page, product search | Listed |
| Shop default order | Not on page 1. The store's default sort is `menu_order` then name, which is expected |
| Product page | H1, image, sale price, "5 in stock", SKU, short and long description, attribute tab, 4 related products, Rank Math Product schema |
| Add to cart | Notice shown, header count 1 |
| Cart | AED 999.00 + flat rate AED 27.00 = AED 1,026.00 |

Deleted afterwards (local only): the product, its image, and test order #6728. The product URL now returns 404.

## 4. Cart and checkout

- **Classic WooCommerce shortcodes, no templates overridden.** All hooks still fire, Checkout Field Editor still works, and the COD/Telr markup is untouched.
- **Stylesheet:** the new `assets/src/css/checkout.css` loads only on the cart, checkout and account pages, after WooCommerce's own CSS. It is deliberately unlayered, so it wins over WooCommerce's unlayered CSS.
- **What it styles:**
  - MedHub buttons (primary: Proceed to checkout, Place order)
  - fields and the focus ring
  - the cart table with thumbnails
  - totals and order-review cards
  - a two-column checkout with a sticky order summary at ≥ 960 px
  - notices, account forms and navigation
  - the loading overlay
- **Test order #6728** (COD "Submit Details to Medhub.ae"):
  - The order-received page loaded.
  - Both emails (customer + admin "New order") were captured in **Mailpit**. Nothing was sent out.
  - The order was then deleted.
- **Telr:** still hidden locally (guard: offline gateways only). The live keys and `testmode = no` are in the local database, so Telr must not be enabled until it is switched to test mode.
- **Nasa Core:** its cart and checkout hooks were left untouched (they include price and order-line logic). The cart and checkout render correctly with them.

## 5. SEO: before/after over all 314 inventory URLs

The baseline is `docs/seo-baseline-elessi.json` (Elessi, before any change).

| | Result |
|---|---|
| HTTP status, titles, canonicals, robots | **Unchanged on all 314 URLs** |
| Meta descriptions | Unchanged. The Contact page had no custom description, and Rank Math's `%excerpt%` fallback now comes from its page excerpt, set to the previous text |
| Pages without an H1 | 63 → **0** |
| Pages with more than one H1 | 12 → **0**. H1s inside old descriptions and articles are shown as H2 on output; stored content is unchanged |
| Schema | Page types kept (AboutPage, ContactPage, CollectionPage, ItemPage…). FAQ is added to Rank Math's page entity as `["<type>","FAQPage"]` on 21 pages with visible FAQs |

**Fix found during testing:** Rank Math merges a separate FAQPage node into the page entity and *replaces* the page type when the page has no other schema. The theme now adds FAQ directly to Rank Math's page entity, after Rank Math has built its graph.

## 6. Other theme changes in this milestone

- **Hero block:** new attributes `showProducts`, `categories` and `limit`. The editor preview shows the first slide.
- **Hero product card:** the price never overflows; only the product name is truncated.
- **One H1 per page:** `inc/seo/headings.php`.

## 7. Observed on the local copy

**Order #6693** was placed through the local checkout at 15:26 UTC (not by these scripts). It reduced the local stock of product #18 by 1, and its emails went to Mailpit. It was left untouched.
