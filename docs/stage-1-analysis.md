# MedHub.ae Frontend Revamp: Stage 1 Analysis

*Crawled 25 Sep 2026 (read-only) from the live sitemaps, the WooCommerce Store API, the WP REST API and page HTML. Nothing on the live site was changed.*
*Companion file: [`url-inventory.csv`](url-inventory.csv) lists 315 URLs (every page, post, category, brand and product) with status, indexability, title, H1 and a keep/redirect recommendation.*

---

## 0. Findings that change the plan

| # | Finding | Why it matters |
|---|---|---|
| 1 | **Product categories and brand pages are missing from the XML sitemap** (Rank Math's `product_cat`/`product_brand` sitemaps are off), yet they are indexable and most client keywords map to them. | The quickest SEO gain on the site, and it needs no redesign. |
| 2 | **None of the 9 Elementor landing pages has an H1.** That includes `/cpap-in-dubai/`, `/bipap-in-dubai/`, `/oxygen-concentrator-in-dubai/` and the others. | The new templates fix this. |
| 3 | **Keyword cannibalisation.** "CPAP in Dubai" is targeted by a landing page, a category and a blog post. The same happens for BiPAP, oxygen concentrator, portable oxygen machine, CPAP masks, pulse oximeter, suction machine, feeding pump and "medical equipment suppliers". | Each query needs one owner URL. The keyword map (§6–10) assigns one. |
| 4 | **Conflicting business facts.** There are four phone numbers: +971 52 814 2931 (header, WhatsApp, delivery policy), +971 4 252 3424 (contact page), +971 4 385 2231 (about page) and +971 50 255 2219 (schema). Legacy copy refers to "LIFEPLUS", "Life Plus healthcare" and "Medicalmart". | NAP consistency matters for local SEO. **MedHub needs to confirm the facts** before we write copy. |
| 5 | **The contact form is broken.** `/contact-us-medhub/` shows `[contact-form-7 id="2151"]` as plain text. | Enquiries are being lost now. |
| 6 | **The sleep apnea URL contains an invisible character:** `/%e2%81%a0sleep-apnea-machine-in-dubai/` (U+2060 word-joiner). That page is actually about *sleep tests*. | Needs a clean URL and a 301. |
| 7 | **Cross-origin cart calls are blocked.** The Store API returns no `Access-Control-Allow-Origin` for foreign origins, and checkout runs on **Telr** (`wc-telr`). | A decoupled frontend on another domain cannot run the cart. This drives the architecture choice in §13. |
| 8 | **39 product names contain SEO text.** Example: "Buy Aerogen Solo in Dubai \| UAE \| Online", which shows as the H1 and on cards. 200 of 202 products have no SKU. 72 have no short description. | Product data clean-up, done in WooCommerce admin with the client's OK. |
| 9 | **The homepage testimonial ("Unaiz") matches the WP author "Mohammed Unaizh".** The home schema is typed `Article`, and the org description says "one stop shop … fantastically low prices". | Don't carry any of these over without verification. |
| 10 | **The homepage is heavy:** about 366 KB of HTML, 40 scripts, 39 stylesheets, Revolution Slider and the Elessi theme. Only 9 of 501 product images are WebP. | There is plenty of room to improve the Core Web Vitals. |

---

## 1. Existing website structure

**Stack:** WordPress, WooCommerce and the Elessi theme (child theme) with Elementor, Header-Footer-Elementor, Revolution Slider, YITH Compare, Rank Math SEO, Telr payment, the Pixel Manager (Google Ads) plugin, GTM (`GTM-52GJSMTM`) and Jetpack. Hosting is Hostinger behind Cloudflare.

**Permalinks:** posts sit at the root (`/%postname%/`), products use `/product/{slug}/`, categories `/product-category/{slug}/` and brands `/brand/{slug}/`. The cart is `/shopping-cart/`. Checkout (`/checkout/`) redirects to the cart when the cart is empty. The account area is `/my-account/`.

**Current main navigation**
```
HOME  → /
ABOUT → /medical-equipment/
SERVICES ▾
  Device on Rent            → /product-category/medical-equipment-rental/
  Devilbiss Service Centre  → /devilbiss-service-centre-in-dubai/
  Sleep Disorder Apnea Test → /%e2%81%a0sleep-apnea-machine-in-dubai/
PRODUCTS ▾
  RESPIRATORY ▾  CPAP · BIPAP · CPAP/BiPAP Masks · OXYGEN CONCENTRATOR · PORTABLE OXYGEN
                 · OXYGEN CYLINDER (→ links to oxygen-concentrator-accessories, which is wrong)
  HOSPITAL & HOMECARE FURNITURE ▾  BED · HOSPITAL FURNITURE
RENTALS    → /product-category/medical-equipment-rental/
CONTACT US → /contact-us-medhub/
```
Plus a category sidebar that lists every category, and a header search with "Popular searches" (Inogen G5, Philips SimplyGo, Philips DreamStation BiPAP).

**Footer:** About, Terms, Privacy, "PD Policy" (delivery), Refund, Cancellation and Contact. The credit line reads "© 2024 – WebTech".

**Business facts found on the site (to be confirmed by MedHub)**
- Legal entity: **LIFE CHOICE MEDICAL EQUIPMENT TRADING L.L.C**, trading as MedHub (older copy also says "Life Choice").
- Address: Shop No. 1 B, Makateb Building, Port Saeed, opposite the Nissan Showroom, Airport Road, Deira, Dubai.
- Phone and WhatsApp: **+971 52 814 2931**. Three other numbers also appear (see §0).
- Email: lifechoicemed@gmail.com.
- Hours (from schema only): Mon–Thu 09:00–20:00; Fri 09:00–12:00 and 14:00–20:00; Sat 09:00–14:30 and 15:30–20:00. Sunday is not listed.
- Delivery policy: orders processed within 24 h of payment; **AED 25 flat fee UAE-wide**; **2–3 business days**; no free delivery; no international shipping.
- Socials: facebook.com/medhub.uae, instagram.com/medhub_uae, tiktok.com/@medhub.ae.
- Payment: Telr (cards).

## 2. Existing major page types

| Type | Count | Notes |
|---|---|---|
| Homepage | 1 | Revolution Slider hero, product carousels, "Why MedHub", blog teasers, testimonial |
| SEO landing pages (Elementor) | 6 | `/cpap-in-dubai/`, `/bipap-in-dubai/`, `/oxygen-machine-in-dubai/`, `/oxygen-concentrator-in-dubai/`, `/portable-oxygen-machine-in-dubai/`, sleep apnea/test. Published Dec 2025, about 700 words each, no H1 |
| Service pages | 2 | DeVilbiss Service Centre; Sleep test (on the sleep-apnea URL) |
| About | 1 | Lives at `/medical-equipment/` |
| Legacy 2024 product-style pages | 5 | `/compact-525-oxygen-concentrator/`, `/inogen-portable-oxygen-concentrator/`, `/devilbiss-blue-cpap/`, `/vacuaide-portable-suction-unit/`, `/device-on-rent/` (an 18-word stub titled "Elementor #4307") |
| Product category archives | 33 active + `portable-ventilator` (0 products, noindex) | Flat, with no parent/child hierarchy |
| Brand archives | 34 | `/brand/{slug}/`, thin "Archives" titles |
| Products | 202 | All simple products, no variations or attributes, 0 reviews, AED pricing, 77 on sale, 48 out of stock |
| Blog posts | 12 | Root URLs, 3 categories, author "Mohammed Unaizh". **No blog index page** (`/blog/` returns 404) |
| Policies | 10 | Includes duplicates: `/return-policy/` vs `/refund-policy/`, and `/terms-conditions/` vs `/terms-and-conditions/` |
| WooCommerce utility pages | 5 | shop, cart, checkout, my-account, order-tracking. Compare/YITH pages are indexable (they shouldn't be) |

## 3. Existing product and category structure

WooCommerce categories are **flat**: all 33 have `parent = 0`. They fall naturally into these departments, which the new navigation uses as groupings (no WooCommerce changes needed):

| Department (new nav grouping) | Existing categories (products) |
|---|---|
| **Oxygen Therapy** | Oxygen Therapy `/oxygen-concentrator/` (18) · Oxygen Concentrator `/buy-oxygen-concentrator/` (5) · Portable Oxygen Concentrator (6) · Oxygen Concentrator Accessories (5) · *product* `/product/oxygen-cylinder-in-dubai/` |
| **Sleep Care** | CPAP (2) · Auto CPAP (3) · Travel CPAP (1) · BiPAP (3) · Auto BiPAP (2) · CPAP/BiPAP Masks (13) · CPAP/BiPAP Accessories (11) · Sleep Support & Comfort (6) |
| **Respiratory & Airway** | Ventilator Accessories (11) · Portable Ventilator (0) · Suction Machine (5) · Tracheostomy Care (23) |
| **Hospital Furniture** | Hospital Furniture (15) · Patient Bed (7) |
| **Patient Care & Feeding** | Feeding Pump (3) · Feeding Pump Accessories (5) · Feeding Milk (4) · Infusion Therapy (1) · Nasogastric Tubes (1) · Therapy & Massage (1) |
| **Monitoring** | Health Monitoring (18) · Pulse Oximeter (3) · Patient Monitor (2) · Vital Sign Monitor (2) · Electrocardiogram (3) |
| **Medical Supplies** | Disposables (4) · Hygiene & Infection Control (25) · Wound Care (11) |
| **Rental** | Rental (13). Per-month simple products; 3 are priced AED 0 (enquiry only) |

**Brands with products:** Choice One Medical (8), ResMed (8), Contour (6), Mölnlycke (6), Abbott (5), Attends (4), Purdoux (4), Activheal, Beurer, Friends, Medical Econet, Meditera, Yuwell (3 each), Ace Sabaah, Best in Rest, Cardinal Health, Clinell, Convatec, Covidien, **Inogen**, Masimo, **Philips Respironics**, Romsons (2 each), and Aerogen, B Braun, Caire, GCE, Hartmann, Medline, Nateen, O2 Concepts, Portex, Sinmed, ZephAir (1 each). About 60 products have no brand assigned. For example, several Philips masks aren't linked to the Philips brand.

**Data quality issues** (fixed in WooCommerce admin, only with approval):
- Category names with typos or odd wording: "Feeding Milk (Nutiricional Suppliment )", "Health Monitoring Pulse Oximeter", and "BiPAP in Dubai" used as a category name.
- A product name typo: "Alunimum Oxygen Cylinder".
- 39 product names with "Buy … in Dubai | UAE | Online" in them.
- Missing SKUs and brands, which weaken the Product schema and Merchant Center.

## 4. Important existing URLs (must be preserved)

The full list is in `url-inventory.csv`. The most important ones:

- **Money pages (keep exactly):** `/`, `/shop/`, all 33 `/product-category/*` URLs, all 202 `/product/*` URLs, `/brand/inogen/`, `/brand/resmed/`, `/brand/philips/`
- **SEO landing pages (keep, rebuild the design):** `/cpap-in-dubai/`, `/bipap-in-dubai/`, `/oxygen-machine-in-dubai/`, `/oxygen-concentrator-in-dubai/`, `/portable-oxygen-machine-in-dubai/`, `/devilbiss-service-centre-in-dubai/`, `/medical-equipment/`
- **Blog (keep all 12 URLs):** for example `/cpap-masks-dubai/`, `/pulse-oximeter-dubai/`, `/suction-machine-dubai/`, `/oxygen-concentrator-in-dubai-how-it-works/`, `/bipap-in-dubai-modes-explained/`, `/enteral-feeding-pumps-in-dubai-medhub-uae-supplier/`…
- **WooCommerce functional (never touch):** `/shopping-cart/`, `/checkout/` (plus `/checkout/order-received/*`), `/my-account/*`, `/order-tracking/`, `/payment-confirmation/`, `?wc-ajax=*`, `/wp-json/*`, and the Telr return and callback URLs
- **Policies (keep):** `/privacy-policy/`, `/terms-conditions/`, `/refund-policy/`, `/cancellation-policy/`, `/delivery-policy/`, `/cookie-policy/`

**Proposed redirects (301)**, all to be confirmed against Search Console traffic and backlinks first:

| From | To | Reason |
|---|---|---|
| `/%e2%81%a0sleep-apnea-machine-in-dubai/` | `/sleep-apnea-machine-in-dubai/` | Invisible character in slug |
| `/device-on-rent/` | `/product-category/medical-equipment-rental/` | 18-word stub |
| `/inogen-portable-oxygen-concentrator/` | `/brand/inogen/` | Legacy duplicate |
| `/compact-525-oxygen-concentrator/` | matching DeVilbiss product or `/product-category/buy-oxygen-concentrator/` | Legacy duplicate |
| `/devilbiss-blue-cpap/` | `/product-category/cpap/` | Legacy duplicate |
| `/vacuaide-portable-suction-unit/` | VacuAide rental product | Legacy duplicate |
| `/return-policy/` | `/refund-policy/` | Duplicate policy |
| `/terms-and-conditions/` | `/terms-conditions/` | Duplicate policy |
| `/multiple-shipments-policy-if-applicable/` | `/delivery-policy/` | 22-word page |

## 5. Recommended new sitemap

New URLs are marked **NEW**. Everything else keeps its current URL.

```
/                                         Home (Medical Equipment Dubai)
├── /shop/                                All products (filters, search)
├── /medical-equipment/                   About MedHub + Medical Equipment Suppliers in Dubai + department directory
│
├── OXYGEN THERAPY
│   ├── /product-category/oxygen-concentrator/            Oxygen Therapy hub
│   ├── /oxygen-machine-in-dubai/                          Chooser landing (buy vs rent, types)
│   ├── /oxygen-concentrator-in-dubai/                     Landing
│   │   └── /product-category/buy-oxygen-concentrator/     Home concentrators (listing)
│   ├── /portable-oxygen-machine-in-dubai/                 Landing
│   │   └── /product-category/portable-oxygen-concentrator/ POC listing
│   ├── /product/oxygen-cylinder-in-dubai/                 Cylinder (rental product)
│   └── /product-category/oxygen-concentrator-accessories/
│
├── SLEEP CARE
│   ├── /sleep-apnea-machine-in-dubai/  NEW (clean slug)   Sleep Care hub
│   ├── /sleep-test-dubai/              NEW (only if service confirmed)
│   ├── /cpap-in-dubai/                                    Landing
│   │   ├── /product-category/cpap/
│   │   ├── /product-category/auto-cpap-machine/
│   │   └── /product-category/travel-cpap-machine/         (also covers "portable CPAP")
│   ├── /bipap-in-dubai/                                   Landing
│   │   ├── /product-category/bipap/
│   │   └── /product-category/auto-bipap-machine/
│   ├── /product-category/buy-cpap-bipap-masks/
│   ├── /product-category/cpap-bipap-accessories/
│   └── /product-category/sleep-support-comfort-solutions/
│
├── RESPIRATORY & AIRWAY
│   ├── /product-category/portable-ventilator/   (noindex until stocked)
│   ├── /product-category/ventilator-accessories-in-dubai/
│   ├── /product-category/suction-machine/
│   └── /product-category/tracheostomy-care-products-in-dubai/
│
├── HOSPITAL & PATIENT CARE
│   ├── /product-category/hospital-furniture/    (hospital bed suppliers)
│   ├── /product-category/patient-bed/
│   ├── /product-category/enteral-feeding-pump/
│   ├── /product-category/feeding-pump-accessories/
│   ├── /product-category/feeding-milk-nutritional-supplement/
│   ├── /product-category/infusion-pump/
│   ├── /product-category/nasogastric-tubes/
│   └── /product-category/therapy-and-massage-devices-in-dubai/
│
├── MONITORING
│   ├── /product-category/health-monitoring-devices/     Monitoring hub
│   ├── /product-category/pulse-oximeter/
│   ├── /product-category/patient-monitor/
│   ├── /product-category/vital-sign-monitor-in-dubai/
│   └── /product-category/electrocardiogram-machine/
│
├── MEDICAL SUPPLIES
│   ├── /product-category/medical-disposables/           Supplies hub
│   ├── /product-category/hygiene-and-infection-control-products/
│   └── /product-category/wound-care/
│
├── BRANDS
│   ├── /brand/inogen/  /brand/resmed/  /brand/philips/  (full brand landings)
│   └── /brand/{other}/                                  (standard brand archive)
│
├── RENTAL & SERVICES
│   ├── /product-category/medical-equipment-rental/
│   └── /devilbiss-service-centre-in-dubai/
│
├── /product/{slug}/                    202 products (unchanged)
├── /blog/                  NEW         Blog index
│   ├── /category/{cpap-and-bipap|oxygen-machine|medical-equipment}/
│   └── /{post-slug}/                   12 posts (unchanged)
├── /faq/                   NEW
├── /contact-us-medhub/
├── Policies: /privacy-policy/ /terms-conditions/ /refund-policy/ /cancellation-policy/ /delivery-policy/ /cookie-policy/
└── WooCommerce: /shopping-cart/ /checkout/ /my-account/ /order-tracking/   (noindex, functional)
```

**Why so few new URLs:** "Respiratory Care", "Sleep Care" and "Patient Care" work as **mega-menu groupings**, not as new thin hub pages. Sleep Care's top link goes to the new `/sleep-apnea-machine-in-dubai/`. Oxygen, Monitoring and Supplies reuse existing parent-like categories as their hubs. Only 4 new URLs are proposed (`/sleep-apnea-machine-in-dubai/`, `/blog/`, `/faq/`, plus `/sleep-test-dubai/` if the service is confirmed).

**Keyword merges (same intent, one strong page instead of two thin ones):**
- *Portable CPAP* and *Travel CPAP* → `/product-category/travel-cpap-machine/`
- *Feeding Pump* and *Enteral Feeding Pump* → `/product-category/enteral-feeding-pump/`
- *Hospital bed suppliers*, *Patient bed* → two pages with separate roles: clinical furniture/beds vs homecare beds
- *Medical Equipment Suppliers in Dubai* → `/medical-equipment/`, the About page. A company-profile page matches "suppliers" intent better than a second homepage would.

---

## 6–10. Keyword map: primary and secondary keywords, SEO title, meta description, H1

Titles are 45–60 characters and metas 140–160 characters (verified by script); none are duplicated. Metas marked `[ONLY IF …]` are conditional. Where a meta lists product types, those must be checked against the live products before launch. **Existing blog posts that currently compete** for these keywords are covered after the table.

| # | URL | Primary keyword | Secondary keywords | SEO title (chars) | Meta description (chars) | H1 | Notes |
|---|---|---|---|---|---|---|---|
| | **Core** | | | | | | |
| 1 | `/` | **Medical Equipment Dubai** | medical equipment suppliers in Dubai · medical equipment supplier UAE · medical equipment company Dubai · healthcare equipment Dubai | Medical Equipment Dubai | Buy & Rent Online | MedHub (52) | Buy or rent medical equipment in Dubai, from oxygen concentrators and CPAP machines to patient beds and monitors. Trusted brands, delivered across the UAE. (155) | Medical Equipment in Dubai for Home and Clinical Care |  |
| 2 | `/medical-equipment/` | **Medical Equipment Suppliers in Dubai** | medical equipment suppliers UAE · medical equipment company Dubai · medical equipment Dubai | About MedHub | Medical Equipment Suppliers in Dubai (51) | MedHub is a medical equipment supplier in Deira, Dubai, offering oxygen, sleep, respiratory and patient care equipment to buy or rent. See what we supply. (154) | About MedHub, Medical Equipment Suppliers in Dubai | Existing About URL. Becomes company page + department directory. |
| 3 | `/shop/` | **buy medical equipment online UAE** | medical supplies Dubai · healthcare products UAE | Shop Medical Equipment & Supplies Online | MedHub UAE (53) | Browse MedHub’s full range of medical equipment and supplies with live prices and stock. Filter by category or brand and order online for delivery in the UAE. (158) | Shop Medical Equipment and Supplies |  |
| | **Oxygen** | | | | | | |
| 4 | `/oxygen-machine-in-dubai/` | **oxygen machine in Dubai** | oxygen concentrator Dubai · portable oxygen machine Dubai · oxygen equipment Dubai | Oxygen Machine in Dubai | Home, Portable & Rental | MedHub (58) | Compare home, portable and rental oxygen machines in Dubai. Understand flow rates and battery life, then buy or rent with advice from the MedHub team. (150) | Oxygen Machines in Dubai: Find the Right Fit | Plain-language chooser: stationary vs portable vs cylinder, buy vs rent. |
| 5 | `/oxygen-concentrator-in-dubai/` | **oxygen concentrator in Dubai** | Oxygen Concentrator in UAE · Philips oxygen concentrator UAE · oxygen concentrator accessories Dubai | Oxygen Concentrator in Dubai | 5L & 10L Models | MedHub (55) | Home oxygen concentrators in Dubai from Philips, Yuwell, DeVilbiss and more. Compare 5L and 10L models, check live prices, or rent one by the month. (148) | Oxygen Concentrators in Dubai |  |
| 6 | `/portable-oxygen-machine-in-dubai/` | **Portable oxygen machine in Dubai** | portable oxygen machine UAE · portable oxygen concentrator UAE · Inogen | Portable Oxygen Machine in Dubai | Buy or Rent | MedHub (55) | Lightweight portable oxygen machines in Dubai from Inogen, Caire, GCE and O2 Concepts. Compare battery life and flow settings, then buy or rent one monthly. (156) | Portable Oxygen Machines in Dubai | "portable oxygen concentrator Dubai" moves to the category page below so two pages don’t chase one query. |
| 7 | `/product-category/portable-oxygen-concentrator/` | **portable oxygen concentrator Dubai** | portable oxygen concentrator UAE · POC Dubai | Portable Oxygen Concentrators in Dubai | Shop | MedHub (54) | Shop portable oxygen concentrators in Dubai with live prices and stock. Inogen, Caire, GCE and O2 Concepts models for home use and travel, with UAE delivery. (157) | Portable Oxygen Concentrators | Product listing (transactional). |
| 8 | `/product-category/oxygen-concentrator/` | **oxygen equipment Dubai** | oxygen therapy equipment · oxygen concentrator UAE | Oxygen Therapy Equipment in Dubai | Concentrators & More (56) | All oxygen therapy equipment in one place: home and portable oxygen concentrators, cylinders and accessories. Live prices, UAE delivery and monthly rental. (155) | Oxygen Therapy Equipment | Existing category named "Oxygen Therapy" becomes the Oxygen department hub. |
| 9 | `/product-category/buy-oxygen-concentrator/` | **buy oxygen concentrator UAE** | home oxygen concentrator · 5L / 10L oxygen concentrator | Buy a Home Oxygen Concentrator in the UAE | MedHub (50) | Shop stationary home oxygen concentrators with 5L and 10L flow options from Philips, Yuwell and DeVilbiss. Live prices and stock, with delivery in the UAE. (155) | Home Oxygen Concentrators |  |
| 10 | `/product-category/oxygen-concentrator-accessories/` | **Oxygen Concentrator Accessories in Dubai** | oxygen accessories Dubai · oxygen concentrator accessories UAE | Oxygen Concentrator Accessories in Dubai | MedHub (49) | Nasal cannulas, humidifier bottles, connectors and sterile water for oxygen concentrators in Dubai. Check compatibility, see live prices and order online. (154) | Oxygen Concentrator Accessories |  |
| 11 | `/product/oxygen-cylinder-in-dubai/` | **oxygen cylinder in Dubai** | oxygen cylinder Dubai · oxygen equipment Dubai | Oxygen Cylinder in Dubai | 10L Cylinder & Trolley Rental (56) | Rent a 10-litre aluminium oxygen cylinder with trolley in Dubai, charged monthly. Check availability and the rental price online, or message MedHub on WhatsApp. (160) | Aluminium Oxygen Cylinder 10L with Trolley (Monthly Rental) | Existing product URL owns this keyword. No separate thin cylinder page. Fix "Alunimum" typo. |
| | **Sleep** | | | | | | |
| 12 | `/sleep-apnea-machine-in-dubai/` | **sleep apnea machine in Dubai** | CPAP in Dubai · BiPAP in Dubai · sleep apnea machine | Sleep Apnea Machine in Dubai | CPAP, BiPAP & Masks (50) | Explore sleep apnea machines in Dubai: CPAP, auto CPAP, BiPAP and travel devices from Philips, ResMed and BMC, plus masks and accessories. Buy or rent. (151) | Sleep Apnea Machines in Dubai | NEW clean slug; 301 from /%e2%81%a0sleep-apnea-machine-in-dubai/. Acts as the Sleep Care hub. |
| 13 | `/sleep-test-dubai/` | **sleep test Dubai** | sleep apnea test Dubai · sleep apnea diagnosis Dubai · sleep test services Dubai | Sleep Test in Dubai | Home Sleep Apnea Testing | MedHub (55) | [ONLY IF CONFIRMED] Book a home sleep apnea test in Dubai through MedHub. Learn how the test works, what it measures and what happens after your results. (153) | Sleep Apnea Testing in Dubai | NEW URL – build only once MedHub confirms it provides or arranges sleep tests. |
| 14 | `/cpap-in-dubai/` | **CPAP in Dubai** | CPAP machine Dubai · sleep apnea machine in Dubai · ResMed CPAP · Travel CPAP Machine in Dubai | CPAP in Dubai | Auto & Travel CPAP Machines | MedHub (52) | Buy or rent a CPAP machine in Dubai. Compare auto and travel CPAP models from Philips, BMC and Heyer, choose the right mask, and get set-up help from MedHub. (157) | CPAP Machines in Dubai |  |
| 15 | `/product-category/cpap/` | **CPAP machine Dubai** | CPAP UAE · RESmart CPAP | CPAP Machines | Shop CPAP Online in Dubai | MedHub (50) | Shop CPAP machines online with live prices and stock, delivered in the UAE. See matching masks, tubing and filters, or rent a CPAP by the month in Dubai. (153) | CPAP Machines |  |
| 16 | `/product-category/auto-cpap-machine/` | **auto CPAP machine Dubai** | APAP Dubai · Philips DreamStation Auto CPAP | Auto CPAP Machines in Dubai | Shop Online | MedHub (50) | Auto CPAP machines adjust pressure through the night. Compare Philips DreamStation, RESmart and Heyer models in Dubai with live prices and UAE delivery. (152) | Auto CPAP Machines |  |
| 17 | `/product-category/travel-cpap-machine/` | **Travel CPAP Machine in Dubai** | portable CPAP in Dubai · portable sleep apnea machine · CPAP machine Dubai | Travel CPAP Machine in Dubai | Portable CPAP | MedHub (53) | Compact travel CPAP machines in Dubai for flights, business trips and holidays. Compare size, weight and power options, then order online for UAE delivery. (155) | Travel and Portable CPAP Machines | Portable CPAP + Travel CPAP merged here (same intent). Only 1 product today. |
| 18 | `/bipap-in-dubai/` | **BiPAP in Dubai** | BiPAP machine Dubai · Auto BiPAP Machine in Dubai · sleep apnea machine Dubai | BiPAP in Dubai | Auto BiPAP & AVAPS Machines | MedHub (53) | Buy or rent a BiPAP machine in Dubai. Understand the difference between BiPAP, auto BiPAP and AVAPS, compare Philips and ResMed models, and ask our team. (153) | BiPAP Machines in Dubai |  |
| 19 | `/product-category/bipap/` | **BiPAP machine Dubai** | BiPAP ST · AVAPS | BiPAP Machines | Shop BiPAP Online in Dubai | MedHub (52) | Shop BiPAP machines including ST and AVAPS models with live prices and stock. Order online for delivery in the UAE or talk to MedHub about rental options. (154) | BiPAP Machines | Rename category display name "BiPAP in Dubai" → "BiPAP Machines". |
| 20 | `/product-category/auto-bipap-machine/` | **Auto BiPAP Machine in Dubai** | auto BiPAP · Philips DreamStation BiPAP | Auto BiPAP Machine in Dubai | Shop Online | MedHub (50) | Auto BiPAP machines in Dubai that adjust inhale and exhale pressure automatically. Compare Philips DreamStation and other models with live prices and stock. (156) | Auto BiPAP Machines |  |
| 21 | `/product-category/buy-cpap-bipap-masks/` | **CPAP/BiPAP Masks in Dubai** | CPAP masks Dubai · BiPAP masks Dubai · sleep apnea masks Dubai | CPAP & BiPAP Masks in Dubai | Nasal, Pillow & Full Face (55) | Shop CPAP and BiPAP masks in Dubai from ResMed and Philips: nasal, nasal pillow and full face styles. Compare fit and cushion types with live prices and stock. (159) | CPAP and BiPAP Masks |  |
| 22 | `/product-category/cpap-bipap-accessories/` | **CPAP/BiPAP Accessories in Dubai** | CPAP accessories Dubai · BiPAP accessories Dubai · sleep apnea accessories Dubai | CPAP & BiPAP Accessories in Dubai | Filters, Tubes & More (57) | Replacement filters, tubing, humidifiers and other CPAP and BiPAP accessories in Dubai for ResMed and Philips machines. Check compatibility and order online. (157) | CPAP and BiPAP Accessories |  |
| 23 | `/product-category/sleep-support-comfort-solutions/` | **sleep support products Dubai** | — | Sleep Support & Comfort Products in Dubai | MedHub (50) | Sleep support and comfort products to make CPAP therapy and everyday rest easier, available from MedHub in Dubai. See live prices and stock and order online. (157) | Sleep Support and Comfort | Low priority – no client keyword. |
| | **Respiratory** | | | | | | |
| 24 | `/product-category/portable-ventilator/` | **Portable Ventilator in Dubai** | portable ventilator UAE · ventilator Dubai | Portable Ventilator in Dubai | Home Ventilation | MedHub (56) | [ONLY WHEN STOCKED] Portable and home ventilators in Dubai with ventilator accessories and support. Speak to MedHub about models, availability and pricing. (155) | Portable Ventilators | 0 products and noindex today – keep noindex until products or a confirmed enquiry service exist. |
| 25 | `/product-category/ventilator-accessories-in-dubai/` | **Ventilator Accessories Dubai** | ventilator Dubai · ventilator circuits · humidification | Ventilator Accessories in Dubai | Circuits & Filters (52) | Ventilator circuits, filters, humidification and other ventilator accessories in Dubai from trusted brands. Live prices and stock with delivery in the UAE. (155) | Ventilator Accessories |  |
| 26 | `/product-category/suction-machine/` | **Suction Machine in Dubai** | suction machine Dubai · medical suction equipment | Suction Machine in Dubai | Portable & Home Units | MedHub (57) | Portable and home suction machines in Dubai, including DeVilbiss VacuAide and Flaem models. Compare suction power and battery options, or rent one monthly. (155) | Suction Machines |  |
| | **Brands** | | | | | | |
| 27 | `/brand/inogen/` | **Inogen machine in Dubai** | Inogen oxygen concentrator Dubai · portable oxygen concentrator Dubai · portable oxygen machine Dubai | Inogen Machine in Dubai | Inogen One G5 & Rove 6 | MedHub (57) | Inogen portable oxygen concentrators in Dubai, including the Inogen One G5 and Rove 6. Compare battery and flow settings, buy online, or rent one monthly. (154) | Inogen Portable Oxygen Concentrators in Dubai | Brand archive upgraded to a brand landing. Legacy /inogen-portable-oxygen-concentrator/ 301s here. |
| 28 | `/brand/resmed/` | **ResMed machine in Dubai** | ResMed CPAP · CPAP machine Dubai · sleep apnea machine Dubai | ResMed Machine in Dubai | Lumis, AirSense & AirFit Masks (56) | ResMed in Dubai: the Lumis 150 VPAP ST, AirFit nasal, pillow and full face masks, and HumidAir humidifiers, plus the AirSense 10 AutoSet for monthly rental. (156) | ResMed Machines and Masks in Dubai | No ResMed CPAP is sold outright today (AirSense 10 is rental only, and its product is not tagged ResMed). Confirm the range. |
| 29 | `/brand/philips/` | **Phillips machine in UAE (written as "Philips")** | Philips oxygen concentrator UAE · Philips Respironics · DreamStation | Philips Machines in the UAE | Respironics CPAP & Oxygen (55) | Philips Respironics equipment in the UAE: Everflo and Oxygenate oxygen concentrators, DreamStation BiPAP machines and masks. Live prices and UAE delivery. (154) | Philips Respironics Machines in the UAE | Correct spelling in all copy; Google already maps "Phillips" to "Philips". |
| | **Rental & Services** | | | | | | |
| 30 | `/product-category/medical-equipment-rental/` | **medical equipment rental in Dubai** | medical equipment rental Dubai · hospital equipment rental Dubai · medical equipment hire Dubai | Medical Equipment Rental in Dubai | Monthly Plans | MedHub (58) | Rent medical equipment in Dubai by the month: oxygen concentrators, CPAP, hospital beds, suction and feeding pumps, and monitors. Check rates and availability. (159) | Medical Equipment Rental in Dubai | /device-on-rent/ 301s here. |
| 31 | `/devilbiss-service-centre-in-dubai/` | **Devilbiss Service Centre in Dubai** | Devilbiss service Dubai · medical equipment service Dubai | DeVilbiss Service Centre in Dubai | CPAP & Oxygen Repair (56) | Repair and maintenance for DeVilbiss CPAP, BiPAP, oxygen concentrators and suction units in Dubai. Book a service or request a quote from the MedHub team. (154) | DeVilbiss Service Centre in Dubai | Drop "Authorized" unless MedHub can show proof of authorisation. |
| | **Hospital & Patient Care** | | | | | | |
| 32 | `/product-category/hospital-furniture/` | **hospital bed suppliers in Dubai** | hospital bed Dubai · medical beds Dubai · hospital furniture Dubai | Hospital Bed Suppliers in Dubai | Beds & Furniture | MedHub (59) | Hospital beds and medical furniture in Dubai: electric beds, overbed tables, commode chairs, wheelchairs, patient lifters and mattresses. Buy or rent a bed. (156) | Hospital Beds and Medical Furniture |  |
| 33 | `/product-category/patient-bed/` | **Patient Bed in Dubai** | patient bed suppliers Dubai · hospital bed Dubai · medical beds Dubai | Patient Bed in Dubai | Electric Homecare & ICU Beds (51) | Electric patient beds in Dubai for home and clinical care. Compare 3-function, 5-function and ICU beds with mattresses, or rent a patient bed by the month. (155) | Patient Beds for Home and Clinical Care |  |
| 34 | `/product-category/enteral-feeding-pump/` | **Enteral Feeding Pump in Dubai** | Feeding Pump · feeding pump Dubai · feeding pump UAE · enteral feeding equipment Dubai | Enteral Feeding Pump in Dubai | Buy or Rent | MedHub (52) | Enteral feeding pumps in Dubai for home and clinical use, including Kangaroo and Abbott models, with matching feeding sets. Buy online or rent one monthly. (155) | Enteral Feeding Pumps | "Feeding Pump" + "Enteral Feeding Pump" merged (same intent). |
| 35 | `/product-category/feeding-pump-accessories/` | **Feeding Pump Accessories in Dubai** | feeding pump accessories UAE · enteral feeding accessories · ENFit syringes | Feeding Pump Accessories in Dubai | Sets & ENFit Syringes (57) | Feeding sets, ENFit syringes and other enteral feeding pump accessories in Dubai. Check compatibility with your pump, see live prices and order online. (151) | Feeding Pump Accessories |  |
| 36 | `/product-category/feeding-milk-nutritional-supplement/` | **Feeding milk nutritional supplement in Dubai** | medical nutrition Dubai · enteral nutrition Dubai | Feeding Milk & Nutritional Supplements in Dubai | MedHub (56) | Enteral feeding milk and nutritional supplements in Dubai for tube and oral feeding, from brands such as Abbott. See live prices and stock, and order online. (157) | Feeding Milk and Nutritional Supplements | Fix category name typo "Nutiricional Suppliment". |
| 37 | `/product-category/infusion-pump/` | **Infusion Pump in Dubai** | infusion pump UAE · medical infusion equipment Dubai · syringe pump | Infusion Pump in Dubai | Infusion & Syringe Pumps | MedHub (58) | Infusion and syringe pumps in Dubai for clinical and home care use. Compare models, buy online, or rent an infusion pump or a syringe pump by the month. (152) | Infusion and Syringe Pumps | Only 1 product today – surface the rental infusion/syringe pumps here too. |
| 38 | `/product-category/tracheostomy-care-products-in-dubai/` | **Tracheostomy Care Products in Dubai** | tracheostomy supplies Dubai · tracheostomy care equipment | Tracheostomy Care Products in Dubai | Tubes & Supplies (54) | Tracheostomy tubes, HMEs, cleaning and care supplies in Dubai from Portex, Covidien and other brands. See live prices and stock, with delivery in the UAE. (154) | Tracheostomy Care Products |  |
| 39 | `/product-category/therapy-and-massage-devices-in-dubai/` | **Therapy and Massage Devices in Dubai** | therapy devices Dubai · medical therapy equipment Dubai | Therapy & Massage Devices in Dubai | Compression Systems (56) | Therapy devices in Dubai, including the Kendall SCD 700 sequential compression system for clinical and home care. See details, live prices and order online. (156) | Therapy and Massage Devices | Only 1 product – consider noindex until the range grows. |
| | **Monitoring** | | | | | | |
| 40 | `/product-category/health-monitoring-devices/` | **Health Monitoring Devices in Dubai** | health monitoring equipment Dubai · medical monitoring devices Dubai | Health Monitoring Devices in Dubai | BP Monitors & AEDs (55) | Health monitoring devices in Dubai: blood pressure monitors, thermometers, glucose meters, patient monitors and AEDs. See live prices, with UAE delivery. (153) | Health Monitoring Devices | Monitoring department hub. |
| 41 | `/product-category/pulse-oximeter/` | **Pulse Oximeter in Dubai** | pulse oximeter Dubai · oxygen saturation monitor Dubai | Pulse Oximeter in Dubai | Handheld & Tabletop | MedHub (54) | Handheld and tabletop pulse oximeters in Dubai for SpO2 and pulse readings, from Masimo and Medical Econet. See live prices and order online for UAE delivery. (158) | Pulse Oximeters |  |
| 42 | `/product-category/patient-monitor/` | **Patient Monitor in Dubai** | patient monitor Dubai · medical patient monitoring equipment | Patient Monitor in Dubai | Bedside Monitors | MedHub (52) | Multi-parameter bedside patient monitors in Dubai for clinics and home care. Compare the parameters, buy online, or rent a patient monitor by the month. (152) | Patient Monitors |  |
| 43 | `/product-category/vital-sign-monitor-in-dubai/` | **Vital Sign Monitor in Dubai** | vital signs monitor Dubai · patient monitoring equipment Dubai | Vital Sign Monitor in Dubai | Buy or Rent | MedHub (50) | Vital sign monitors in Dubai for blood pressure, SpO2, pulse and temperature checks in clinics and at home. Buy online or rent a vital sign monitor monthly. (156) | Vital Sign Monitors |  |
| 44 | `/product-category/electrocardiogram-machine/` | **Electrocardiogram machine in Dubai** | ECG machine Dubai · ECG equipment UAE | Electrocardiogram Machine in Dubai | 12-Channel ECG (51) | ECG machines in Dubai for clinics and healthcare providers, including 12-channel models such as the Bionet CardioTouch 3000. Live prices and UAE delivery. (154) | Electrocardiogram (ECG) Machines |  |
| | **Supplies** | | | | | | |
| 45 | `/product-category/medical-disposables/` | **Medical disposables in Dubai** | medical disposable products Dubai · medical supplies Dubai | Medical Disposables in Dubai | Clinical & Home Supplies (55) | Medical disposables and supplies in Dubai: wound care, hygiene and infection control, tracheostomy and feeding consumables. See live prices and order online. (157) | Medical Disposables and Supplies | Supplies department hub (only 4 own products – surfaces sibling categories). |
| 46 | `/product-category/hygiene-and-infection-control-products/` | **Hygiene and Infection Control Products in Dubai** | infection control products Dubai · medical hygiene products Dubai | Hygiene & Infection Control Products in Dubai | MedHub (54) | Hygiene and infection control products in Dubai: adult diapers, underpads, disinfectant wipes and bed bath cloths from Clinell, Attends and more. Order online. (159) | Hygiene and Infection Control |  |
| 47 | `/product-category/wound-care/` | **wound care products Dubai** | advanced wound dressings · silver dressings | Wound Care Products in Dubai | Advanced Dressings | MedHub (58) | Advanced wound care dressings in Dubai from Convatec, 3M, Mölnlycke and other brands, including silver and foam dressings. See live prices and order online. (156) | Wound Care Products | No client keyword – supporting page. |
| | **Info** | | | | | | |
| 48 | `/contact-us-medhub/` | **contact MedHub** | medical equipment store Deira | Contact MedHub | Medical Equipment Store in Deira, Dubai (56) | Visit MedHub in Port Saeed, Deira, call or WhatsApp the team, or send an enquiry about equipment, rentals and quotes. Opening hours and directions included. (156) | Contact MedHub | Fix broken contact form (shortcode renders as text). |
| 49 | `/blog/` | **medical equipment guides** | — | Medical Equipment Guides & Insights | MedHub Blog (49) | Practical guides on oxygen therapy, CPAP and BiPAP, patient care and home monitoring from MedHub in Dubai. Clear, plain explanations before you buy or rent. (156) | Guides and Insights | NEW blog index (no posts page exists today). |
| 50 | `/faq/` | **medical equipment FAQ Dubai** | delivery · rental · returns | Frequently Asked Questions | MedHub Medical Equipment (53) | Answers about ordering, delivery across the UAE, monthly rentals, returns, payment and servicing at MedHub. Can’t find your answer? Call or WhatsApp us. (152) | Frequently Asked Questions | NEW. Answers must come from confirmed policies. |

### Blog posts that compete with commercial pages

All URLs stay the same. We change only the **title and H1 angle**, so each post targets an informational query and links to the commercial owner page. Do this after checking Search Console: if a post currently ranks better than the commercial page for the keyword, leave it alone and review again after launch.

| Post URL (unchanged) | Commercial page it competes with | New informational angle |
|---|---|---|
| `/medical-equipment-suppliers-in-dubai-complete-guide-…/` | `/medical-equipment/` | How to choose a medical equipment supplier (checklist) |
| `/portable-oxygen-machine-in-dubai-your-complete-buyers-guide/` | `/portable-oxygen-machine-in-dubai/` | Portable oxygen concentrator buyer's guide: pulse vs continuous flow |
| `/cpap-in-dubai-your-complete-guide-…/` | `/cpap-in-dubai/` | CPAP therapy explained: how it works and getting started |
| `/bipap-in-dubai-your-complete-guide-…/` + `/bipap-in-dubai-modes-explained/` | `/bipap-in-dubai/` | BiPAP vs CPAP / BiPAP modes (two posts, two distinct angles) |
| `/cpap-masks-dubai/` | CPAP/BiPAP Masks category | How to choose a CPAP mask: nasal, pillow or full face |
| `/pulse-oximeter-dubai/` | Pulse Oximeter category | How to read a pulse oximeter at home |
| `/suction-machine-dubai/` | Suction Machine category | Using and cleaning a home suction machine |
| `/infusion-pump-in-dubai-…/` | Infusion Pump category | Infusion vs syringe pumps explained |
| `/travel-cpap-machine-in-dubai-…/` | Travel CPAP category | Flying with a CPAP: what to know |
| `/enteral-feeding-pumps-in-dubai-medhub-uae-supplier/` | Feeding Pump category | Enteral feeding at home: a caregiver's guide |
| `/oxygen-concentrator-in-dubai-how-it-works/` | `/oxygen-concentrator-in-dubai/` | Already informational (how PSA works). Keep as is. |

---

## 11. Recommended page content structure

**Commercial landing page** (e.g. `/cpap-in-dubai/`), about 400–700 words:
1. Breadcrumb
2. **H1** + a 2-line intro + primary CTA (*Shop CPAP machines*) + secondary CTA (*Rent monthly* / *WhatsApp a specialist*) + product visual
3. Quick-pick chips for the sub-types (Auto CPAP · Travel CPAP · Masks · Accessories)
4. **Live product rail** (4–8 real products from WooCommerce)
5. H2 "How to choose": 3–4 short, scannable decision points (e.g. auto vs travel CPAP, humidifier, mask type)
6. H2 "Buy or rent": a comparison block that links to the relevant rental products
7. Brand strip showing only brands actually stocked in this category, linked to the brand pages
8. H2 "Delivery, set-up and support": facts from the delivery policy only (AED 25 fee, 2–3 business days, UAE-wide)
9. FAQ: 4–6 questions, visible on the page, with FAQPage schema
10. Related guides (blog) + related categories
11. Final CTA band (call · WhatsApp · enquiry)

**Category page** (`/product-category/*`), 150–350 words of copy plus the product grid:
H1 + a 1–2 sentence intro above the grid → filters (brand, price, in stock, buy/rent) + sort → product grid → an expandable "About {category}" block (150–300 words, fed from the WooCommerce category description) → FAQ (optional) → related categories and guides.

**Product page** (URL unchanged): breadcrumb → gallery (zoom, thumbnails) → sticky buy box (title, brand, short summary, AED price or sale price, stock status, qty, Add to cart; for zero-price rental items a *Request a quote* + WhatsApp CTA instead) → trust row (UAE delivery AED 25 · 2–3 business days · Telr secure payment) → tabs/accordion (Description · What's in the box · Delivery & returns) → related products → "Also in {category}" + relevant guide.

**Brand landing** (Inogen, ResMed, Philips): H1 → a short brand intro (what MedHub stocks from the brand, with factual model names) → product grid → model comparison table (only from specs on the product pages) → FAQ → related categories.

**Homepage:** Hero → trust bar → department cards → featured products (live) → Why MedHub (4 factual pillars: Deira showroom · buy or rent · brands stocked · UAE delivery) → brands → rental spotlight → Sleep Care interactive switcher (CPAP / Auto CPAP / BiPAP / Travel / Masks, loading real products) → delivery & support → blog → FAQ → final CTA.

---

## 12. Recommended internal linking structure

```
Home ─┬─ Department cards ──► hub pages (Oxygen Therapy · Sleep apnea machine · Health Monitoring · Medical Disposables · Rental)
      ├─ Featured products ─► /product/*
      └─ Blog teasers ──────► posts

Hub ──► its landings + categories ──► products
 e.g. /sleep-apnea-machine-in-dubai/ ─► /cpap-in-dubai/ ─► /product-category/cpap/ · /auto-cpap-machine/ · /travel-cpap-machine/
                                     ─► /bipap-in-dubai/ ─► /product-category/bipap/ · /auto-bipap-machine/
                                     ─► masks · accessories · /brand/resmed/ · /brand/philips/ · /sleep-test-dubai/

 /product-category/oxygen-concentrator/ (Oxygen hub)
     ─► /oxygen-machine-in-dubai/ ─► /oxygen-concentrator-in-dubai/ ─► /buy-oxygen-concentrator/
                                  ─► /portable-oxygen-machine-in-dubai/ ─► /portable-oxygen-concentrator/ ─► /brand/inogen/
                                  ─► /product/oxygen-cylinder-in-dubai/ · /oxygen-concentrator-accessories/

 /product-category/hospital-furniture/ ─► /patient-bed/ ─► rental beds
 /product-category/health-monitoring-devices/ ─► patient-monitor · vital-sign-monitor · pulse-oximeter · electrocardiogram

Product ─► its category (breadcrumb) + brand page + "pairs well with" (masks↔CPAP, accessories↔concentrators, feeding sets↔pumps)
          + rental version if one exists (and the reverse)
Blog post ─► 1 commercial owner page (contextual link in the first 150 words) + 2–4 products + 1 related post
Rental category ◄──► purchase category for the same equipment type
```
**Rules:**
- Anchors are descriptive and varied ("auto CPAP machines", "see ResMed masks"), never the exact-match keyword every time.
- Every indexable page gets at least 3 contextual inbound links.
- No orphans: every category is reachable from the mega menu or a hub.
- The breadcrumbs follow the department grouping: Home › Sleep Care › CPAP Machines › *Product*.

---

## 13. Recommended frontend architecture

I considered three options against the constraints: keep every URL, cart, checkout, Telr and Rank Math working, and don't touch WooCommerce data.

| | **A. Custom WooCommerce theme** (recommended) | B. Headless frontend (e.g. Astro) + Cloudflare edge routing | C. Headless on a separate domain/subdomain |
|---|---|---|---|
| URLs preserved | ✅ Automatically, 100% | ⚠️ Every route must be re-implemented and kept in sync | ❌ Changes the domain or WP URL |
| Cart / checkout / Telr / coupons / shipping | ✅ Native, and it gets the new design | ⚠️ Works only via same-origin proxy; **cart and checkout keep the old Elessi look** | ❌ Blocked by CORS; payment callbacks at risk |
| Live price and stock | ✅ Real-time | ⚠️ Needs rebuild webhooks or SSR | ⚠️ Same as B |
| Rank Math titles, canonicals, schema, sitemaps, redirects | ✅ Keeps working; we only improve the values | ❌ Must be rebuilt or bridged | ❌ Same as B |
| GTM / Google Ads conversion tracking | ✅ Unchanged | ⚠️ Rebuild | ⚠️ Rebuild |
| Performance ceiling | High (lean PHP + vanilla JS, full-page cache via Hostinger/LiteSpeed and Cloudflare) | Very high | Very high |
| Build effort and ongoing maintenance | Lowest; one system | High; two systems | Highest |
| Change on WordPress | **Activating a theme.** This is the presentation layer only: no plugin, product, order or setting data changes, and you can switch back in one click. | None to WP itself, but a DNS/Worker change | WP URL change |

**Recommendation: Option A.** It's a lightweight custom WooCommerce theme ("medhub"), developed in this folder in plain PHP templates + semantic HTML, modern CSS (custom properties, `@layer`, container queries) and small vanilla-JS modules, with no framework and no jQuery on non-shop pages.
- It is built and tested on a **Hostinger staging clone**. The live site stays untouched until you approve go-live.
- Elementor and Revolution Slider are no longer needed on the front end. They are left installed and can be deactivated later.

It meets the "don't modify WooCommerce" rule in spirit: WooCommerce, its data, Telr and the checkout logic keep running unchanged. Only the visual layer is replaced. Activating a theme is still a change to the WordPress install, so it needs your explicit sign-off.

**Folder layout (this repo):**
```
medhub/
├── docs/                       analysis, keyword map, URL inventory, redirect map, QA checklists
├── theme/medhub/               the WordPress theme (deployable folder)
│   ├── style.css               theme header only
│   ├── functions.php           bootstraps /inc
│   ├── inc/                    setup · assets (conditional loading) · woocommerce hooks · nav/mega-menu data
│   │                           · schema gaps (FAQ, ItemList) · landing-page content registry · helpers
│   ├── templates & parts       header, footer, front-page, page, single, home (blog), archive, search, 404
│   ├── page-templates/         landing.php (data-driven), hub.php, service.php, contact.php, faq.php
│   ├── woocommerce/            archive-product, single-product, content-product, minimal cart/checkout skin
│   ├── parts/components/       one PHP partial per component (see §14)
│   ├── content/landings/       landing-page copy as structured PHP arrays (version-controlled, reviewable)
│   └── assets/
│       ├── css/  tokens.css · base.css · components/*.css · pages/*.css
│       ├── js/   core.js + modules loaded per page: nav, mega-menu, reveal, counters, filters, quick-view, gallery, faq
│       ├── fonts/ self-hosted variable font (WOFF2, subset)
│       └── icons/ SVG sprite
└── tooling/                    optional: a single lightningcss/esbuild minify step (the only dev dependency)
```

**Local development:** a local WordPress copy (LocalWP or Docker) seeded from a staging database export, so templates always render **real** products. No fake data.

---

## 14. Recommended component structure

**Design tokens** (`tokens.css`): colour (a calm off-white/ink base with one confident accent and one support tint, instead of hospital blue gradients), type scale (fluid `clamp()`), spacing (4-pt scale), radius (small, 6–14 px), shadows (2 levels only), motion (durations and easing, all zeroed under `prefers-reduced-motion`), z-index, container widths, breakpoints (480 / 768 / 1024 / 1280 / 1536).

| Layer | Components |
|---|---|
| **Primitives** | Button (primary · secondary · ghost · WhatsApp · icon), Link-arrow, Badge (sale · out of stock · rental · new), Chip/filter pill, Input/Select/Checkbox/Quantity stepper, Price (regular/sale/per-month), Stock indicator, Icon |
| **Navigation** | Top utility bar (phone · WhatsApp · delivery note), Header (sticky, condenses on scroll), Mega menu (department columns + featured product/visual), Mobile drawer (accordion, focus-trapped), Search overlay (live suggestions via the Store API, same-origin), Mini-cart drawer (WooCommerce fragments), Breadcrumb |
| **Commerce** | Product card (image swap on hover, category, name, price, stock, CTA, quick-view trigger), Product grid, Product rail (horizontal scroll-snap), Quick-view modal, Filter panel (sheet on mobile), Sort bar, Gallery (zoom + thumbs), Sticky buy box, Rental CTA block, Related products |
| **Content** | Section header (eyebrow · H2 · lede · link), Category card (large visual), Department tile, Brand strip, Feature/pillar list, Comparison table, Buy-vs-Rent block, Trust bar, Stat counter (only for verified numbers), FAQ accordion (`<details>`-based), Rich text, Article card, Article header (author, date, reading time), Table of contents, CTA band |
| **Global** | Footer (SEO-structured columns), Floating WhatsApp button, Toast/notice, Skip link, Cookie notice (existing plugin, reskinned) |

---

## 15. Potential SEO risks during the redesign

| Risk | Mitigation |
|---|---|
| URL changes or lost pages | The theme approach keeps every URL by default. Only 9 deliberate 301s (§4), entered in Rank Math's redirect manager. A pre/post crawl diff of all 315 URLs. |
| Rank Math output lost or duplicated | The theme outputs no `<title>`/meta/canonical of its own. Rank Math stays the single source. Theme schema only fills gaps (FAQPage, ItemList) and is de-duplicated against Rank Math's graph. |
| Losing the content that ranks today | Landing pages keep their topic and the parts of the copy that rank. We tighten it and add H1s rather than replace wholesale. Snapshot the current HTML of all 315 URLs before launch. |
| Cannibalisation getting worse | One owner URL per keyword (§6–10). Blog retitles are data-led after checking Search Console. |
| Categories and brands missing from the sitemap | Enable the `product_cat` and `product_brand` sitemaps in Rank Math. Remove `elementor-hf` templates, compare, YITH and payment-confirmation pages from the index. |
| Thin pages indexed | Keep `portable-ventilator` noindex. Consider noindex for 1-product categories (therapy-massage, nasogastric, travel CPAP) until they're cross-listed. |
| Unverified claims in the copy ("Authorized", "24/7", "certified", "doctor recommended", testimonial) | Remove them, or ask MedHub for proof first. |
| Faceted filters creating crawl traps | Filter URLs `noindex, follow` + canonical to the clean category URL. Don't link filter states in the HTML. |
| JS-rendered content not indexed | Products, copy and links are server-rendered in HTML. JS only enhances. |
| CLS/LCP regressions from the new hero and animations | Explicit image dimensions, a preloaded hero image in AVIF/WebP, font `size-adjust` fallbacks, and animations using transform/opacity only. |
| Inconsistent NAP (4 phone numbers) | Settle on one canonical set and use it in the header, footer, contact page, schema and Google Business Profile. |
| Tracking loss | Keep GTM and the Pixel Manager hooks. Verify purchase and add-to-cart events on staging. |

## 16. Potential WooCommerce integration risks

| Risk | Mitigation |
|---|---|
| Template overrides going stale after WooCommerce updates | Override as few templates as possible. Prefer hooks and CSS. Check WC → Status → Templates after each update. |
| Telr checkout and callback breaking | Don't override checkout payment templates, only style them. Run end-to-end test orders on staging (Telr test mode), including failed and cancelled payments. |
| Cart fragments and the mini-cart | Use the standard `woocommerce_add_to_cart_fragments`, so the Ajax add-to-cart keeps working with LiteSpeed/Cloudflare caching (cart and checkout excluded from cache). |
| Rental products are plain simple products priced per month; 3 are priced AED 0 | The card and buy box detect the Rental category and zero price, then show "per month" and a *Request a quote/WhatsApp* CTA instead of Add to cart. No data change needed. |
| Out-of-stock (48) and sale (77) states | The components render WooCommerce's own flags. No invented availability. |
| Plugins that inject markup (YITH Compare, wishlist in Elessi's nasa-core, Jetpack) | Decide which to keep. Nasa-core and wishlist are tied to Elessi, so wishlist/compare disappear unless we rebuild them. Recommendation: drop them (low value). |
| Elementor-built pages losing their layout | Landing, hub and service pages get new PHP templates with their content in the theme. Policy pages render as clean prose. Elementor stays installed as a fallback. |
| Staging vs live drift (orders placed while we build) | The theme is code only. Go-live means uploading and activating the theme on live; no database merge. |
| Search | Use the WooCommerce product search (same origin). Optional live suggestions via the Store API `products?search=`. |
| Security note (outside this project) | `/wp-json/wp/v2/users` exposes admin usernames. Worth hardening separately. |

## 17. Recommended development phases

| # | Phase | Output | Gate |
|---|---|---|---|
| 1 | ✅ **Analysis** (this document) | Findings, keyword map, URL inventory | **Your approval + answers below** |
| 2 | Sitemap and architecture sign-off | Final keyword map, redirect map, nav/mega-menu spec | Approval |
| 3 | Design system | `tokens.css`, type and colour, component library page (static HTML preview) | Visual approval |
| 4 | Homepage | Built on real products (staging data) | Review |
| 5 | Global components | Header, mega menu, mobile drawer, footer, search, mini-cart, product card | Review |
| 6 | Category and shop templates | Filters, sort, grid, category intro/FAQ, brand landings | Review |
| 7 | Product template | Gallery, sticky buy box, rental/quote logic, related | Test orders on staging |
| 8 | Landing, service and rental pages | Data-driven landing template + copy for every page in the map | Copy approval (medical-claim check) |
| 9 | Blog | Index, article template, category archives, related products | Review |
| 10 | SEO layer | Rank Math values applied, sitemap settings, redirects, schema gaps, crawl diff | SEO sign-off |
| 11 | WooCommerce hardening | Cart/checkout skin, Telr E2E tests, emails check, tracking check | Test orders pass |
| 12 | Responsive pass | 360 → 1920 px, touch targets, sticky elements | Device QA |
| 13 | Performance | Image pipeline (AVIF/WebP), critical CSS, JS budget (< 40 KB per page), caching rules | Lighthouse mobile ≥ 90, CWV green in the lab |
| 14 | Final QA and go-live | Accessibility audit (WCAG 2.2 AA), link check, 315-URL diff, rollback plan, activate on live | **Your go-live approval** |

---

## Decisions and information needed from you or MedHub

1. **Architecture.** Approve Option A (custom WooCommerce theme, built here, tested on staging, activated on live only with your sign-off), or tell me you need Option B (fully headless, zero WordPress change, but the cart and checkout keep the old look).
2. **Access.** A Hostinger staging clone (or a DB + `wp-content/uploads` export) for local development. Read access to **Google Search Console** and GA4, so the cannibalisation and redirect decisions are based on real data.
3. **Business facts to confirm:**
   - The canonical phone and WhatsApp number, and the public email (it's a Gmail address today).
   - Opening hours, including whether Sunday is closed.
   - Whether MedHub **provides or arranges sleep tests**.
   - Whether it is an **authorised** DeVilbiss service centre.
   - Whether it supplies **portable ventilators**.
   - Whether the "Unaiz" testimonial is a genuine customer review.
   - Whether "24/7 support" is real.
   - Any verifiable numbers (years trading, customers served) you'd like to use.
4. **WooCommerce admin clean-up (optional, separate from the code).** Fix the category name typos, remove "Buy … in Dubai | UAE | Online" from 39 product names, assign missing brands, cross-list products (e.g. BMC M1 Mini → Travel CPAP; rental infusion pumps → Infusion Pump), and switch on the category and brand sitemaps in Rank Math. These are content and settings edits in WP admin, so I'll only list them for your team or do them with explicit permission.
