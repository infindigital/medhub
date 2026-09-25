# MedHub Theme: Stage 2 Implementation Plan

> **Updated after approval (25 Sep 2026):**
> - Landing, category and FAQ **content now lives in WordPress**, not in theme PHP files. See [`content-architecture.md`](content-architecture.md). It supersedes the `content/` folder and the routing-by-registry parts of §1.1–1.3 and §3.7.
> - The palette and typography in §3.2–3.3 are **Direction A only**. The final direction will be chosen from the mood boards in `design/moodboards/`.
> - The new WordPress pages (`/blog/`, `/faq/`, `/sleep-test-dubai/`) will **not** be created for now (D5).

*Written 25 Sep 2026. It builds on `stage-1-analysis.md` and `url-inventory.csv`. **No code has been written, and nothing on production or in any database has changed.** The only files created so far are the empty theme skeleton (`medhub-theme/`) and its `style.css` header, which contains no templates and cannot be activated.*

---

## 0. Ground rules and environment

### 0.1 Facts confirmed for this plan (read-only checks, 25 Sep)
| Item | Value | Consequence |
|---|---|---|
| WordPress | 6.9.9 | `get_template_part()` with `$args` is available, and so are `wp_robots`, the Interactivity API (not needed) and AVIF uploads |
| WooCommerce | 10.7.0 | Template overrides are pinned to 10.7 versions. Brands are core WooCommerce Brands (`product_brand`, base `/brand/`) |
| PHP (live) | 8.3 | The theme targets PHP 8.1+. It is linted locally with LocalWP's PHP 8.2 |
| Cart/checkout | The empty cart renders classic markup (`cart-empty`), with no block classes | Assume **classic shortcode** cart and checkout. **Must be confirmed on staging** (see §6, issue W1) |
| Front page | A static page (ID 3806) built with Elementor | `front-page.php` takes over rendering. The page and its Rank Math meta stay as they are |
| Landing pages | Elementor (`data-elementor-type="wp-page"`) | Rendered by our landing template instead of Elementor's output (§1.3) |
| Policy pages | Plain WordPress content | Rendered by `page.php` as clean prose |
| Form plugins | CF7 is **not active** (its shortcode prints as raw text). The **WPForms** REST namespace is present | Use WPForms for the contact form, once confirmed (§3.6) |
| Local tooling | **LocalWP** is installed (bundled PHP 8.2, MySQL 8, **Mailpit**). No Docker, PHP CLI or WP-CLI on PATH | Staging = a LocalWP site imported from a Hostinger backup. Mailpit catches every email |

### 0.2 Environment plan (needs your approval and access)
1. **The source copy comes from Hostinger:** either Hostinger's one-click staging, or a full backup (DB + `wp-content`). *You or MedHub trigger this. I have no hosting access and won't ask for production credentials.*
2. **Local staging** runs on a LocalWP site at `medhub.local`, imported from that backup. Local rewrites the site URL, so nothing links back to medhub.ae.
3. **The theme is linked into LocalWP** from this repo: `…/wp-content/themes/medhub` is a symlink to `medhub-theme/`. We edit here and see the result immediately.
4. **The local copy is made safe before any theme work.** These are DB changes on the *local copy only*, and each needs your OK:
   - Put Telr in **test/sandbox mode**, or disable it locally, so no real charge is possible.
   - Deactivate Hostinger Reach (email marketing), and disconnect the Jetpack and WooCommerce.com connections.
   - Disable the Pixel Manager / GTM output, so staging doesn't fire Google Ads conversions or pollute GA4.
   - Rely on Mailpit to catch all outgoing email, and verify that no email leaves the machine.
   - Set `WP_ENVIRONMENT_TYPE = 'local'` and `DISABLE_WP_CRON` (then run cron manually). This stops scheduled jobs such as abandoned-cart mails running against real customer data.
   - Keep the backup, which contains **real customer PII**, out of git and off shared drives. Optionally anonymise customers and orders locally.
5. **Version control:** `git init` this folder, ignoring backups, `node_modules` and `.env`. Everything stays local unless you give me a remote.
6. **Production** is untouched until Stage 2L sign-off. Go-live means uploading the theme folder and activating it (§6, W14 covers what activation itself triggers).

---

## 1. Theme structure

### 1.1 Tree (created as empty folders)
```
medhub-theme/
├── style.css                 Theme header only (no styles)
├── functions.php             Loads /inc modules; nothing else
├── index.php                 Required fallback
├── front-page.php            Homepage (sections from template-parts/sections)
├── home.php                  Blog index (needs a posts page – see D5)
├── page.php                  Standard pages + WooCommerce shortcode pages (cart/checkout/account)
├── single.php                Blog article
├── archive.php               Blog category / date / author archives
├── search.php                Unified search results (products first, then articles)
├── 404.php
├── header.php / footer.php   Thin wrappers → template-parts/header|footer
├── searchform.php            Accessible search form (used by get_search_form)
│
├── inc/
│   ├── setup/                theme-supports.php · menus.php · image-sizes.php · cleanup.php (emoji, oEmbed, etc.)
│   ├── assets.php            Conditional enqueue per template; preload; defer; dequeues (Elementor/RevSlider/YITH on our templates)
│   ├── woocommerce/          support.php · hooks-archive.php · hooks-single.php · hooks-cart.php · fragments.php · rental.php · filters.php
│   ├── seo/                  rankmath-bridge.php (breadcrumbs, FAQ into Rank Math graph, noindex for filter URLs) · headings.php
│   ├── helpers/              template-tags.php · icons.php · business.php (config accessor) · content-registry.php · image.php
│   └── routing.php           template_include: landing / hub / service registries → templates/*
│
├── config/                   ALL editable non-product configuration (no DB)
│   ├── business.php          Phone, WhatsApp, email, address, hours, socials, delivery facts – each with a status flag
│   ├── departments.php       Mega-menu + category explorer: department → WooCommerce category *slugs* (names/links/counts resolved live)
│   ├── homepage.php          Which section shows what (category slugs, product selection rules, brand slugs) – no product data
│   └── features.php          Feature flags (quick view off, ajax search on, sticky ATC on…)
│
├── content/                  Editorial copy that is NOT in the database (version-controlled, reviewable)
│   ├── landings/             one PHP array file per landing page, keyed by existing page slug
│   ├── categories/           optional extra blocks per category slug (FAQ, "how to choose"); intro comes from the WC term description
│   └── brands/               Inogen / ResMed / Philips landing blocks
│
├── templates/                Whole-page templates chosen by inc/routing.php (no DB template assignment needed)
│   ├── landing.php  hub.php  service.php  contact.php  faq.php
│
├── woocommerce/              Minimal overrides only (register in §5)
│
├── template-parts/
│   ├── header/  footer/  navigation/   Global chrome, mega menu, mobile drawer, search overlay, mini-cart
│   ├── hero/                           Home hero, page hero, landing hero
│   ├── products/                       Product rail, grid, sticky buy box, gallery, rental CTA
│   ├── categories/                     Category explorer, department tile, category chips
│   ├── sections/                       Trust bar, why, brands, rental spotlight, sleep switcher, delivery, FAQ, CTA band
│   ├── cards/                          Product card, category card, article card, brand card
│   ├── blog/                           Article header, TOC, related posts, related products
│   ├── components/                     Primitives: button, badge, price, stock, breadcrumb, section-header, icon
│   └── forms/                          Contact form wrapper (provider-agnostic), newsletter (off)
│
├── assets/
│   ├── src/css/  tokens/ base/ components/ layouts/ pages/     Source CSS (one file per component)
│   ├── src/js/   core.js + modules/                            Vanilla ES modules
│   ├── dist/                    Built, minified bundles (committed, so the server needs no build step)
│   ├── fonts/  icons/  images/  Self-hosted WOFF2, SVG sprite, theme-only graphics (no product images)
│
└── docs/                        Theme docs: overrides register, config guide, QA checklists
```

### 1.2 Differences from the suggested structure (and why)
| Suggested | Changed to | Reason |
|---|---|---|
| Root `single-product.php` **and** `woocommerce/single-product/` (same for archive) | Only `woocommerce/…` | WooCommerce looks in the theme root *or* `/woocommerce/`. Having both is redundant and confusing |
| `sidebar.php` | Removed | No widget sidebars. Shop filters are a component, not a widget area |
| `page-templates/` (templates selected in WP admin) | `templates/` + `inc/routing.php` | Assigning a page template writes `_wp_page_template` to the DB. Routing by existing slug/term needs **no DB change** and keeps the current URLs |
| – | `config/` and `content/` | Keeps business facts in one place (your requirement) and holds new landing copy without editing page content in the DB |

### 1.3 How pages get their design without DB changes
- **Existing landing pages** (`/cpap-in-dubai/`, etc.): `inc/routing.php` sees that the page slug has a file in `content/landings/` and renders `templates/landing.php` with that copy. The WordPress page (and its Rank Math title, meta and canonical) stays exactly as it is. Only the body is new.
- **Categories and brands:** the intro comes from the existing WooCommerce term description. Extra blocks (FAQ, "how to choose") come from `content/categories/{slug}.php`.
- **Products:** 100% live WooCommerce data. Nothing is stored in the theme.
- **Rollback:** switch back to Elessi, and everything, including the Elementor content, is exactly as before.

**Exceptions that need DB changes on staging, then on production at go-live (approval needed, see §9):**
- The clean `/sleep-apnea-machine-in-dubai/` slug.
- The new `/blog/` posts page.
- The new `/faq/` and `/sleep-test-dubai/` pages.
- The 301 redirects.

---

## 2. Step-by-step plan (2A → 2L)

Each step ends with a **STOP + report** (what was built · files · WooCommerce impact · SEO · risks · tests · next).

| Step | Build | Key files | WooCommerce touched? | Gate / test |
|---|---|---|---|---|
| **2A Foundation** | Theme supports (`title-tag`, `woocommerce`, `html5`, thumbnails, custom-logo), menu locations, conditional asset loader, build script, business config + accessor, routing skeleton, bare header/footer/page/index/404, `wp_head`/`wp_body_open`/`wp_footer` in place | `functions.php`, `inc/setup/*`, `inc/assets.php`, `inc/routing.php`, `config/*`, `header.php`, `footer.php`, `page.php`, `index.php` | Declares support only. Cart/checkout render through `page.php` | Theme activates on local without PHP notices. Cart and checkout still work. Rank Math `<title>`/meta/schema appear **exactly once** |
| **2B Design system** | Tokens, base, typography, layout, buttons, form controls, badges, cards (static styles), a **living style-guide page** (local, admin-only) | `assets/src/css/tokens/*`, `base/*`, `components/*`, `templates/styleguide.php` (dev only) | No | Visual approval. Contrast checked. Reduced-motion checked |
| **2C Homepage** | 12 sections (§3.7) using live products, categories, brands and posts | `front-page.php`, `template-parts/sections/*`, `hero/*`, `config/homepage.php` | Reads only (`wc_get_products`, `get_terms`) | Real data renders. LCP image preloaded. No CLS |
| **2D Components** | Header + mega menu + mobile drawer, search overlay (+ suggestions), mini-cart drawer, product card, rails, breadcrumbs, FAQ accordion, CTA band, footer | `template-parts/header|navigation|cards|components/*`, `assets/src/js/modules/*` | Mini-cart uses standard fragments | Keyboard-only walkthrough. Ajax add-to-cart updates the drawer |
| **2E Shop / category / brand** | `archive-product` layout, filters (brand, price, in stock, buy/rent), sort, pagination, category intro + SEO block + FAQ, brand landings (Inogen/ResMed/Philips) | `woocommerce/archive-product.php`, `woocommerce/content-product.php`, `inc/woocommerce/hooks-archive.php`, `filters.php` | Loop markup and hooks. **The query is untouched** except whitelisted filter params | Filters give correct counts. Filter URLs are `noindex` with a clean canonical |
| **2F Product page** | Gallery (lightweight, zoom, keyboard), sticky buy box, rental/zero-price logic, trust row, accordion "tabs", related products + related categories | `woocommerce/content-single-product.php`, `single-product/product-image.php`, `single-product/tabs/tabs.php`, `inc/woocommerce/hooks-single.php`, `rental.php` | Layout only. Add-to-cart form **unchanged** (hooks kept for Pixel Manager/YITH) | Add to cart works for in-stock, out-of-stock and zero-price rental items. Rank Math Product schema is intact |
| **2G Cart / checkout / account** | Visual skin by CSS + hooks. Cart layout, checkout two-column via CSS grid, my-account nav, order-received page | CSS + `woocommerce/myaccount/navigation.php` (maybe) | **Styling only. No checkout template overrides** | Full E2E on staging: coupon, shipping, **Telr test payment success/decline/cancel**, order emails in Mailpit, stock reduced |
| **2H Standard pages** | Landing template + copy for the landing/service pages, contact (form wrapper), about (`/medical-equipment/`), policies, FAQ | `templates/*`, `content/landings/*` | No | Copy review (claims + medical safety). One H1 per page |
| **2I Blog** | Blog index, article, archives, related products, TOC | `home.php`, `single.php`, `archive.php`, `template-parts/blog/*` | Reads products for "related" | Article schema only from Rank Math. Titles unchanged |
| **2J SEO + accessibility** | Heading audit, breadcrumb parity with schema, FAQ → Rank Math graph, internal-link blocks, alt-text logic, axe/keyboard audit, focus management | `inc/seo/*` | No | Crawl of all 315 URLs on local: status, titles, H1, canonical, robots compared with the live baseline |
| **2K Performance** | Critical CSS for home/category/product, per-template bundles, JS budget, font subsetting, image `sizes`/`srcset`, jQuery only where WooCommerce needs it, dequeue unused plugin assets | `inc/assets.php`, build | Dequeues WooCommerce assets only on non-shop templates | Lighthouse mobile ≥ 90 on home/category/product/article. CLS < 0.05 |
| **2L QA** | Breakpoints 320–1920, browsers (Safari iOS, Chrome Android, desktop), full a11y pass, regression E2E orders, rollback rehearsal | `docs/qa/*` | Verification only | Your go-live approval |

---

## 3. Design system proposal

### 3.1 Direction: "Clinical calm, with a pulse"
Warm, paper-like neutrals and deep ink typography carry most of the page. That feels premium and editorial, not hospital-blue. A single **signal-lime** accent is used sparingly for key moments: the primary CTA on dark sections, active states and the "pulse" dot. A **deep teal** handles links and interactive text. Product photography sits on soft sand tiles, so the mixed white-background product images look curated. Dubai shows up through the warm sand palette and the editorial restraint, not through clichés such as skylines or gold.

### 3.2 Colour tokens (contrast verified, WCAG 2.2 AA)
| Token | Hex | Use | Contrast |
|---|---|---|---|
| `--c-ink` | `#0D1B1E` | Headings, body, dark sections | 15.9:1 on paper |
| `--c-paper` | `#F5F3EE` | Page background | – |
| `--c-surface` | `#FFFFFF` | Cards, inputs, drawer | – |
| `--c-sand` | `#E8E1D3` | Product image tiles, alt sections | ink 13.5:1 |
| `--c-muted` | `#4A585B` | Secondary text | 6.7:1 paper · 5.7:1 sand |
| `--c-teal` | `#0A6E62` | Links, focus ring, secondary buttons | 5.5:1 paper · white on teal 6.1:1 |
| `--c-lime` | `#D4F36B` | Accent CTA (ink text on it), highlights on ink | ink on lime 14.1:1 |
| `--c-line` | `#DCD6CA` | Decorative dividers only | – |
| `--c-border-input` | `#7F8A8C` | Form control borders (≥ 3:1) | 3.2:1 paper |
| `--c-sale` / `--c-ok` / `--c-warn` | `#B3261E` / `#1F7A4D` / `#8A5A00` | Sale, in stock, low/out of stock | 6.5 / 5.3 / 5.9 on white |
| `--c-whatsapp` | `#25D366` + ink text | WhatsApp button only | 8.9:1 |

Status is never shown by colour alone: stock and sale states always include text and an icon. Dark mode is out of scope; a light-only ecommerce UI keeps product photography consistent.

### 3.3 Typography
- **Geist** (variable, OFL, self-hosted WOFF2, Latin subset about 35 KB) for headlines, UI and body. It's precise and technical, and it reads as healthcare-tech.
- **Instrument Serif Italic** (OFL, about 18 KB) is an *editorial accent*, used for a single word or phrase in large headlines (e.g. "Breathe *easier* at home"). It never appears in body text. It can be dropped if you'd prefer a single-family system.
- Only one font file is preloaded, with a `size-adjust`-matched fallback so there's no layout shift.

| Token | Size (fluid `clamp`) | Weight / tracking | Use |
|---|---|---|---|
| `--fs-display` | 44 → 104 px | 600 / −0.035em | Home hero only |
| `--fs-h1` | 36 → 68 px | 600 / −0.03em | Page H1 |
| `--fs-h2` | 28 → 46 px | 600 / −0.025em | Section titles |
| `--fs-h3` | 20 → 26 px | 600 / −0.015em | Card/sub-section titles |
| `--fs-lead` | 18 → 21 px | 400 | Intros |
| `--fs-body` | 16 → 17 px | 400, line-height 1.6 | Body |
| `--fs-small` | 14 px | 500 | Meta, labels |
| `--fs-eyebrow` | 12.5 px | 600, uppercase, +0.08em | Eyebrows/badges (minimum size) |

Measure is capped at 68ch for prose. Headline line-height is 1.02–1.1.

### 3.4 Space, layout and shape
- **Spacing (4-pt):** `--s-1` 4 · `--s-2` 8 · `--s-3` 12 · `--s-4` 16 · `--s-5` 24 · `--s-6` 32 · `--s-7` 48 · `--s-8` 64 · `--s-9` 96 · `--s-10` 128. Section rhythm is `clamp(64px, 10vw, 144px)`.
- **Containers:** `--container` 1320 px · `--container-wide` 1600 px (hero, rails) · `--container-prose` 720 px. The gutter is `clamp(16px, 4vw, 40px)`.
- **Grid:** 4 columns on mobile, 8 on tablet and 12 on desktop. Asymmetric splits (7/5, 5/7) are used for editorial sections.
- **Breakpoints:** 480 · 768 · 1024 · 1280 · 1536. Container queries handle card internals, so a card adapts to its slot, not to the viewport.
- **Radius:** `--r-xs` 4 (badges) · `--r-sm` 8 (inputs, buttons) · `--r-md` 14 (cards) · `--r-lg` 22 (large media tiles) · pill (chips only). Everything else is square-ish, to avoid the "overly rounded" look.
- **Borders over shadows:** 1 px `--c-line` borders do most of the work. There are two shadows only: `--sh-1` (hover lift, very soft) and `--sh-2` (drawer, mega menu, modal), plus a 2 px teal focus ring (`--focus`).

### 3.5 Motion
- **Tokens:** `--t-fast` 150 ms · `--t-base` 240 ms · `--t-slow` 420 ms, with easing `cubic-bezier(.2,.7,.2,1)`.
- **Animated properties:** transform and opacity only.
- **Patterns:**
  - Scroll reveal: a single IntersectionObserver, a 12 px rise plus fade, run once.
  - Product image: a 1.04 scale on hover, with a swap to the second gallery image where one exists.
  - Buttons: a 2 px lift and an arrow nudge.
  - Mega menu: fade plus a 6 px drop, with a hover-intent delay of 120 ms.
  - Header: condenses after 80 px of scroll.
  - Counters: only for verified numbers, so they are **off by default**.
- **Page transitions:** the cross-document View Transitions API, supported browsers only, with no JS.
- **Reduced motion:** `prefers-reduced-motion: reduce` zeroes every duration and turns reveals into instant display.

### 3.6 Core UI elements
- **Buttons:**
  - `primary` (ink): main action on light backgrounds.
  - `accent` (lime + ink): only one per viewport, on hero or dark CTA sections.
  - `secondary` (outline): secondary actions.
  - `ghost` (text with arrow).
  - `whatsapp`.
  - `icon` (44 × 44 minimum target).
- **Sizes:** 40 / 48 / 56 px.
- **Form controls:** 48 px height, a visible label always (no placeholder-as-label), inline error text with `aria-describedby`, and native elements styled (no custom select widgets).
- **Contact form:** a provider-agnostic wrapper. `config/business.php → contact_form = ['provider' => 'wpforms', 'id' => null]`.
  - With a provider set, it renders that plugin's form, styled.
  - With none set, it renders call / WhatsApp / email actions instead. It **never shows a fake form or a raw shortcode**.

### 3.7 Homepage sections and their data sources
| # | Section | Data source (no hardcoded product data) |
|---|---|---|
| 1 | Hero: headline, copy, "Explore Medical Equipment" + "Medical Equipment Rental", product composition | Copy in `content/`. Images = featured images of 3 products picked by slug in `config/homepage.php` (name, link and price resolved live). **Needs good cut-out imagery (Q15)** |
| 2 | Trust bar | `config/business.php`. Shows only items with status `site-sourced` or `confirmed` (e.g. "Showroom in Deira", "UAE-wide delivery", "Buy or rent", "Secure card payment") |
| 3 | Category explorer (8 tiles, horizontal scroll-snap on mobile) | `config/departments.php` → live term names, links, counts and images |
| 4 | Featured products (tabs: Featured · On sale · New) | `wc_get_products()` with `featured` / `on_sale` / date. In stock first |
| 5 | Why MedHub (4 pillars, visual) | Copy in `content/`. Factual claims only |
| 6 | Brands | `get_terms('product_brand')` with `count > 0`, ordered by count, logo if the term has an image, otherwise a wordmark |
| 7 | Rental spotlight | `product_cat = medical-equipment-rental` products: price shown "/ month", zero-price shown as "Ask for a quote" |
| 8 | Sleep and respiratory switcher (CPAP · Auto CPAP · BiPAP · Travel · Masks · Oxygen) | Tabs server-render the first tab. Other tabs load real products lazily from the same-origin Store API |
| 9 | Delivery and support | `config/business.php` delivery facts (AED 25, 2–3 business days, as on `/delivery-policy/`). 24/7 is **off** until confirmed |
| 10 | Blog | `WP_Query` latest 3 posts (1 large + 2 compact) |
| 11 | FAQ | `content/home-faq.php`. Answers only from policy pages. Emitted as FAQPage in the Rank Math graph only when rendered |
| 12 | Final CTA | Shop · Rent · Contact (links + business config) |

---

## 4. Component architecture

Every component is a `template-parts/…` partial called with `get_template_part( $slug, null, $args )`. It has one CSS file in `assets/src/css/components/` and, only if interactive, one JS module in `assets/src/js/modules/`. Each partial documents its `$args` at the top.

| Component | Partial | Key args | Data | JS module |
|---|---|---|---|---|
| Button | `components/button` | label, url, variant, size, icon, attrs | – | – |
| Badge | `components/badge` | type (sale/out/rental/new), text | – | – |
| Price | `components/price` | product | `$product->get_price_html()` + "/ month" suffix for rentals, "Ask for a quote" if zero price | – |
| Stock | `components/stock` | product | `is_in_stock()`, `get_stock_quantity()` if managed | – |
| Breadcrumb | `components/breadcrumb` | – | Rank Math breadcrumbs (same trail as schema). Fallback `WC_Breadcrumb` | – |
| Section header | `components/section-header` | eyebrow, title, level, lede, link | – | – |
| Icon | `helpers/icons.php` | name, label | SVG sprite `<use>` | – |
| **Product card** | `cards/product` | product, context (grid/rail/compact), show_cta | Live `WC_Product` | `card` (image swap only if a second image exists) |
| Category card | `cards/category` | term, size | `get_term`, `thumbnail_id` term meta, count | – |
| Article card | `cards/article` | post, variant | WP post | – |
| Brand card | `cards/brand` | term | Term + image | – |
| Product rail | `products/rail` | query args or product IDs, title | `wc_get_products()` | `rail` (scroll-snap buttons, native scroll) |
| Product gallery | `products/gallery` | product | Gallery image IDs | `gallery` (zoom, thumbs, swipe, keyboard) |
| Sticky buy box | `products/buy-box` | product | Wraps the **unchanged** `woocommerce_template_single_add_to_cart` | `sticky-atc` (mobile bottom bar) |
| Rental CTA | `products/rental-cta` | product | Category + price rules | – |
| Header | `header/site-header` | – | Business config, menu config | `header` |
| Mega menu | `navigation/mega-menu` | department | `config/departments.php` → live terms | `mega-menu` (hover-intent, Esc, arrow keys) |
| Mobile drawer | `navigation/drawer` | – | Same config | `drawer` (focus trap, `inert` background) |
| Search overlay | `navigation/search` | – | Native WP/WooCommerce search. Suggestions from the Store API `products?search=` | `search` (debounce 300 ms, min 3 chars, AbortController, 6 results, session cache) |
| Mini-cart drawer | `navigation/mini-cart` | – | `woocommerce_mini_cart()` + fragments | `mini-cart` |
| Filters | `products/filters` | term context | Brands present in the current category, price range, stock, buy/rent | `filters` (GET form, progressive, works without JS) |
| FAQ | `sections/faq` | items, schema (bool) | `content/*` | native `<details>` (no JS) |
| CTA band | `sections/cta-band` | variant, title, actions | Business config | – |
| Trust bar | `sections/trust-bar` | – | Business config (status-gated) | – |
| Contact form | `forms/contact` | – | Provider adapter | – |
| Floating WhatsApp | `components/whatsapp-fab` | – | Business config (hidden if unconfirmed) | – |

**JS budget:** `core.js` (header, drawer, search, reveal) is under 12 KB gzipped on every page. Page modules load `defer` only where used. No libraries. jQuery loads **only** where WooCommerce core needs it: product, archive, cart, checkout and account.

**CSS delivery:** Source files are bundled per template: `base.css`, `shop.css`, `product.css`, `checkout.css` and `blog.css`. Critical CSS is inlined for home, category and product. **The single build dependency is `esbuild`**, which bundles and minifies both CSS and JS. The `dist/` output is committed, so the server never builds anything.

---

## 5. Templates to override

### 5.1 WordPress templates (theme-owned, new)
`front-page.php`, `home.php`, `page.php`, `single.php`, `archive.php`, `search.php`, `404.php`, `index.php`, `header.php`, `footer.php`, `searchform.php`, plus `templates/{landing,hub,service,contact,faq}.php` via routing.

### 5.2 WooCommerce template overrides (kept deliberately small)
| Template (WC 10.7) | Override? | Why / how |
|---|---|---|
| `archive-product.php` | **Yes** | Page layout: header, filters, grid, SEO block. Keeps every `woocommerce_*` loop hook |
| `content-product.php` | **Yes** | Premium card. Keeps `woocommerce_before/after_shop_loop_item` hooks so YITH and Pixel Manager still attach |
| `content-single-product.php` | **Yes** | Two-column gallery + sticky summary layout. All `woocommerce_single_product_summary` hooks kept |
| `single-product/product-image.php` + `product-thumbnails.php` | **Yes** | Lightweight gallery, replacing flexslider/zoom/photoswipe (removes about 90 KB of JS) |
| `single-product/tabs/tabs.php` | **Yes** | Tabs → accessible accordion |
| `global/breadcrumb.php` | No | Replaced by our component (Rank Math source) |
| `loop/orderby.php`, `loop/result-count.php`, `loop/pagination.php` | No | CSS only |
| `cart/cart.php`, `cart/cart-totals.php` | **No** | CSS + hooks only. The cart logic stays pure WooCommerce |
| `cart/mini-cart.php` | Maybe | Only if the drawer needs markup that CSS can't provide |
| `checkout/*` (form-checkout, payment, review-order, thankyou) | **No** | Telr safety. CSS grid restyle only |
| `myaccount/navigation.php` | Maybe | For icons. Everything else CSS |
| `emails/*` | **No** | Out of scope. Order emails stay exactly as they are today |
| `single-product/add-to-cart/*` | **No** | The add-to-cart form stays native (quantity, nonce, hooks) |

Every override is logged in `medhub-theme/docs/overrides.md` with the WooCommerce template `@version` it was copied from. After each WooCommerce update, check *WooCommerce › Status › Templates*.

---

## 6. WooCommerce and plugin compatibility issues

| # | Issue | Risk | Plan |
|---|---|---|---|
| W1 | Classic or block cart/checkout isn't confirmed (the cart was empty during inspection) | High | Check the cart/checkout page content on staging for `[woocommerce_cart]`/`[woocommerce_checkout]`. If they are blocks, the checkout is styled via block classes and §5.2 changes |
| W2 | **Header Footer Elementor** hijacks `get_header`/`get_footer` on unsupported themes when one of its templates is active (the site has about 14 header/footer templates) | High | On staging: confirm HFE display rules. Recommend deactivating HFE locally (DB change, approval needed) |
| W3 | **nasa-core** (Elessi's companion plugin) hooks into WooCommerce loops, adds wishlist/quick-view/AJAX handlers, and may assume Elessi functions exist | High | Test activation with it on. If it misbehaves, deactivate it (Elessi-only). Scan posts and pages for `[nasa_…]` shortcodes first |
| W4 | Elementor + Revolution Slider enqueue assets on pages we now render ourselves | Medium (perf) | Dequeue in `inc/assets.php` on theme-rendered templates. The plugins stay installed for rollback |
| W5 | YITH Compare injects buttons into loops and product pages | Low | Remove its actions via theme code (recommended: compare adds little value), or style it |
| W6 | Pixel Manager (Google Ads) depends on `wp_head`/`wp_footer`, WC hooks and the jQuery `added_to_cart` event | Medium | Keep all standard hooks and WooCommerce's native ajax add-to-cart. Verify events in the plugin's debug mode on staging |
| W7 | **Telr** payment: redirect/iframe flow, return and callback URLs | High | No checkout template overrides. Test success, decline, cancel and the return page in **test mode** |
| W8 | Rental items are simple products priced per month. 3 are priced AED 0 (WooCommerce treats them as not purchasable) | Medium | `inc/woocommerce/rental.php` detects the Rental category and shows "/ month". Zero-price items get a quote/WhatsApp CTA. No data change |
| W9 | Full-page caching (Hostinger/Cloudflare) vs the cart count in the header | Medium | The header count is rendered empty and filled by fragments **only if** the `woocommerce_items_in_cart` cookie exists, so there are no extra requests for most visitors. Cart, checkout and account excluded from cache (verify the existing rules) |
| W10 | Gallery replacement removes WooCommerce's zoom/lightbox/slider theme supports | Low | Our gallery provides zoom and a lightbox dialog. Test with 1-image and 5-image products |
| W11 | Product names containing "Buy … in Dubai \| UAE \| Online" become H1s and card titles | Medium (SEO/UX) | **Decision D6:** fix the data in WP admin (preferred), or use a display-only filter in the interim |
| W12 | Filters: products have no attributes. Brand filter via the `product_brand` taxonomy, price via WooCommerce's native `min_price`/`max_price`, stock via a meta query | Low | Only whitelisted GET params in `pre_get_posts`, main query only. Filter URLs `noindex, follow` via Rank Math's robots filter |
| W13 | Home and landing pages list products outside the shop | Low | Card CTA there = "View product", so those pages don't need jQuery or WooCommerce scripts. Shop, category and product pages keep ajax add-to-cart. **Decision D7** |
| W14 | **Activating the theme triggers WooCommerce thumbnail regeneration** if our image sizes differ from the current ones (the current `woocommerce_thumbnail` crops to 350 × 220, which is bad for square product shots) | Medium | Test on staging first. On go-live this writes new image files and metadata on production, so schedule it and back up first. The alternative is to reuse existing sizes (worse quality) |
| W15 | Out-of-stock (48), on-sale (77), no-SKU (200), no-brand (~60) products | Low | Components tolerate missing brand/SKU/short description and never invent values |
| W16 | WooCommerce and plugin updates after launch | Ongoing | Overrides register + a quarterly template-version check |

---

## 7. SEO risks and how the theme handles them

| Risk | Handling in the theme |
|---|---|
| Duplicate `<title>`, meta, canonical, Open Graph or schema | The theme declares `title-tag` and prints **none** of these. Rank Math owns them. A 2A check fails if any appear twice |
| Duplicate/competing schema | No standalone JSON-LD. FAQPage (only when a FAQ is rendered) is added **into Rank Math's graph** via `rank_math/json_ld`. Product, Article, Breadcrumb and Organization come from Rank Math only |
| Visible breadcrumb ≠ BreadcrumbList schema | The visible breadcrumb uses Rank Math's own trail. The department crumb (e.g. "Sleep Care") is added through Rank Math's breadcrumb filter, so both stay identical |
| Missing or multiple H1s | Each template owns exactly one H1. Checked in the 2J crawl |
| Losing ranking copy on landing pages | New copy in `content/landings/` keeps each page's topic, headings and the parts of the current copy that already rank. The old Elementor content stays in the DB untouched (rollback) |
| Filter/sort URLs indexed | `noindex, follow`, canonical to the clean category URL, and filter links not exposed as crawlable `<a>` lists |
| JS-only content | All products, copy and links are server-rendered. JS only enhances (switcher tabs 2+ are progressive) |
| Images: missing alt text or keyword-stuffed alt text | Alt order: attachment alt → product name. Decorative images get `alt=""`. No keyword alt text |
| Core Web Vitals regression | Budgets in 2K. Explicit width/height, preloaded LCP image, no layout-shifting fonts |
| Cannibalisation | Landing/category/blog roles follow the Stage 1 map. Blog retitles are **not** part of the theme (they are Rank Math/DB edits, done later with GSC data) |
| URL changes | None made by the theme. The redirects in `url-inventory.csv` stay **documented only** until go-live approval |
| Homepage schema typed `Article` (from Rank Math) | Documented. Fix in the Rank Math settings at go-live (approval), not via theme code |
| Staging getting indexed | LocalWP isn't public. If a Hostinger staging URL is used, it must be password-protected + `noindex` |

---

## 8. Information still requiring confirmation (from MedHub)

| # | Item | Where it's used | Default until confirmed |
|---|---|---|---|
| Q1 | Canonical phone number | Header, footer, contact, schema | Hidden. Admin-only "TBC" marker on staging |
| Q2 | WhatsApp number | Header CTA, floating button, product enquiry | Hidden |
| Q3 | Public email (Gmail vs a domain email) | Footer, contact | Hidden |
| Q4 | Opening hours (Sunday?) | Contact, footer | Hidden |
| Q5 | Address wording + Google Maps / Business Profile link | Contact, footer | Site-sourced address shown on staging only |
| Q6 | Delivery facts still current (24 h processing, AED 25, 2–3 business days, UAE only) | Trust bar, delivery section, FAQ, product trust row | Shown as "site-sourced" on staging |
| Q7 | Rental terms: minimum period, deposit, delivery/collection, availability | Rental section, rental FAQ | Generic "per month, ask for terms" |
| Q8 | Does MedHub provide or arrange **sleep tests**? | Sleep test page, nav | Page not built |
| Q9 | Is it an **authorised** DeVilbiss service centre? | Service page | The word "authorised" is not used |
| Q10 | Portable ventilators supplied? | Nav, ventilator page | Kept out of the nav |
| Q11 | 24/7 support? | Trust bar, support section | Not claimed |
| Q12 | Is the "Unaiz" testimonial genuine? Any real reviews (e.g. Google) we may quote? | Social proof | No testimonials |
| Q13 | Are "LIFEPLUS", "Life Plus healthcare" and "Medicalmart" references outdated? | Landing and service copy | Kept out of new copy (the old DB content stays untouched) |
| Q14 | Brand display: "MedHub" vs "Life Choice", and whether to show the legal name | Footer, about | "MedHub" + legal name in the footer |
| Q15 | High-resolution / cut-out product photography and the logo in SVG | Hero, category tiles, header | Best existing product images; current PNG logo |
| Q16 | Payment methods beyond cards via Telr (COD? Tabby/Tamara?) | Trust row, FAQ | "Secure card payment" |
| Q17 | Contact form solution: WPForms (installed) or other? Recipient address? | Contact page | Call/WhatsApp/email actions instead of a form |
| Q18 | Are customer accounts actively used (registration, order history)? | My Account priority | Styled, not redesigned in depth |
| Q19 | Arabic version needed now or later? | Architecture (RTL) | English only. CSS written with logical properties so RTL is cheap later |

---

## 9. Decisions needed before 2A starts

| # | Decision | Recommendation |
|---|---|---|
| D1 | **Staging source:** Hostinger staging clone or a full backup, provided by you. I then import it into LocalWP | Backup → LocalWP (`medhub.local`) |
| D2 | Approve the **local-only safety changes** in §0.2.4 (Telr test mode, marketing/tracking off, cron off, HFE/nasa-core deactivated if they interfere) | Approve |
| D3 | Approve **esbuild** as the single dev dependency, plus `git init` for this folder (local only) | Approve |
| D4 | Design direction: palette (ink/paper/sand + teal + lime accent) and type (Geist + Instrument Serif accent) | Approve, or ask for 2 alternative mood boards in 2B |
| D5 | Staging-only DB additions: a Blog page set as the posts page (`/blog/`), a FAQ page, the clean sleep-apnea slug. Production only at go-live | Approve for staging |
| D6 | Product names with "Buy … in Dubai \| UAE \| Online": fix the data later in WP admin, or use a display-only cleanup filter now | Fix the data (preferred). Filter as an interim fallback |
| D7 | Card CTA on home/landing pages: "View product" (lighter, no jQuery) vs direct add-to-cart | "View product" |
| D8 | Quick view | **Off.** Product pages are the stronger SEO and conversion asset |
| D9 | Product image sizes: new square sizes (regeneration on activation, W14) vs existing sizes | New sizes, tested on staging, scheduled at go-live |
