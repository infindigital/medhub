# Content Architecture: Design in the Theme, Content in WordPress

*This follows the Stage 2 approval, and it **replaces** the `content/` PHP-array approach in `stage-2-plan.md` §1.1–1.3. That folder has been removed from the theme.*

## Principle
| Layer | Owns |
|---|---|
| **Theme** (`medhub-theme/`) | Layout, typography, spacing, components, animation, responsive behaviour, and reusable content *blocks* (markup + styles), but no copy |
| **WordPress** (page / term / post data) | Page title (= H1), intro, SEO copy, FAQs, supporting sections, images |
| **Rank Math** | SEO title, meta description, canonical, robots, sitemaps, schema |
| **WooCommerce** | Products, prices, stock, images, categories, brands, cart, checkout |
| `config/business.php` | Business details only (phone, address, delivery facts…) with confirmation status |

## Landing pages (e.g. `/cpap-in-dubai/`, `/oxygen-concentrator-in-dubai/`)
One reusable page template, **"Landing"** (`templates/landing.php`, declared with a `Template Name:` header). Editors build the page body in the **block editor** from core blocks plus a small set of theme blocks:

| Section | How it's authored |
|---|---|
| H1 | The page title (one H1, printed by the template) |
| Intro + CTAs | `medhub/landing-hero` block: intro paragraph, 2 buttons, optional image |
| Relevant products | `medhub/product-rail` block: pick a category, brand or products. Rendered **live** from WooCommerce |
| Benefits / how to choose | Core heading + paragraph + list blocks, styled by the theme |
| Buy vs rent | `medhub/compare` block (two columns of editable text) |
| Related categories | `medhub/category-links` block: choose categories, with names/links/images resolved live |
| FAQ | Core **Details** blocks inside a `medhub/faq` wrapper. FAQPage schema is added to Rank Math's graph only from what is visibly rendered |
| CTA | `medhub/cta-band` block (text editable, contact details from business config) |

A **block pattern** called "Landing page" inserts this whole structure in one click, so each keyword page shares one design with different content.

**Theme blocks** are server-rendered (`block.json` + a PHP `render` file). Their small editor scripts are vanilla JS using the global `wp.*` packages that WordPress already ships, with no React build and no JSX. esbuild only minifies them.

## Categories and brands
- **Intro above the grid:** the existing WooCommerce **term description**. This is unchanged, and already edited in wp-admin.
- **Content below the grid** (how to choose, FAQ): a theme-registered term field, *"Below-products content"*, edited with a rich-text editor on the category/brand edit screen and stored as term meta. It writes to the DB only when an editor saves it.
- **H1:** the term name, or an optional *"Display heading"* term field if the name is too short. For example, the category "Disposables" could show "Medical Disposables and Supplies".

## Migration of existing Elementor landing pages (a later step, staging only, with approval)
1. Copy the current Elementor text into blocks using the "Landing page" pattern, keeping the headings and passages that rank.
2. Switch the page to the block editor and assign the "Landing" template. Elementor's data stays in post meta, so rollback remains possible.
3. Rank Math fields are unchanged.

Nothing of this is done until the staging copy exists and you approve the content step (2H).

## New pages (`/blog/`, `/faq/`, `/sleep-test-dubai/`), per D5
These are **not created**. The templates are prepared: `home.php` for the blog index and `templates/faq.php`. Whether they become pages will be decided later.
