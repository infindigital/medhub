# WooCommerce Template Overrides Register

Check after every WooCommerce update under *WooCommerce › Status › Templates*. If WooCommerce reports an override as outdated, diff the new core template against the version below and port any hook or markup changes.

| Theme file | Core template | Core @version copied | Why | Hooks kept |
|---|---|---|---|---|
| `woocommerce/archive-product.php` | `archive-product.php` | 8.6.0 | Theme layout: header, sub-navigation, filters, toolbar, below-grid content | before/after_main_content, archive_description, before/after_shop_loop, shop_loop, no_products_found, sidebar |
| `woocommerce/content-product.php` | `content-product.php` | 9.4.0 | Theme product card | before/after_shop_loop_item (default link/button callbacks unhooked in `inc/woocommerce/archive.php`) |
| `woocommerce/single-product.php` | `single-product.php` | 1.6.4 | Page shell only (theme `<main>`, no sidebar markup) | before/after_main_content, sidebar |
| `woocommerce/content-single-product.php` | `content-single-product.php` | 3.6.0 | Two-column gallery + sticky summary, related products, explore links | before_single_product, before_single_product_summary, single_product_summary, after_single_product_summary, after_single_product |
| `woocommerce/single-product/tabs/tabs.php` | `single-product/tabs/tabs.php` | 9.8.0 | Tabs rendered as an accessible `<details>` accordion | `woocommerce_product_tabs` filter, tab callbacks, product_after_tabs |

**Not overridden (by design):**
- The cart, checkout, payment, thank-you and email templates. Telr and the order flow stay 100% core; they are styled only in step 2G.
- The add-to-cart form (`single-product/add-to-cart/*`). It is called as-is from the buy box.
- The loop ordering and pagination templates, which are restyled with CSS.

**Default callbacks unhooked (markup replaced by the theme; hooks still fire):**
- Archive: `inc/woocommerce/archive.php`
- Single product: `inc/woocommerce/single.php`
