# SEO: pages and posts (local copy, 2026-09-27)

Applied with `tooling/local/wp.sh eval-file tooling/local/apply-seo.php apply` from
`tooling/seo/meta.json`. `status` shows the current state; `rollback` restores the previous values
exactly (saved in `_medhub_seo_prev` post meta and the `medhub_seo_prev_templates` /
`medhub_seo_prev_alts` options). Everything stays in Rank Math / WordPress; no SEO in theme code.

## Done

| Change | Scope |
| --- | --- |
| Title pattern `%title% | MedHub` (was `%title% - Medical Equipment Suppliers in Dubai | Buy Medical Supplies UAE`, 70–160 chars) | all pages, posts, products, product categories, brands; blog categories get `%term% Guides | MedHub` |
| Custom Rank Math title + description + focus keyword, written from each page's own content | 21 pages/posts that had none |
| `noindex, follow` (drops them from the Rank Math sitemap) | checkout, my-account, shopping-cart, compare, yith-compare, order-tracking, payment-confirmation |
| Focus keyword changed so posts stop competing with landing pages | 6570 BiPAP modes, 6600 oxygen concentrator how-it-works; new posts use informational keywords (e.g. "BiPAP vs CPAP") |
| WordPress title "Elementor #4307" → "Device on Rent" (H1; URL unchanged) | page 4307 |
| Featured-image alt text = post title (was empty) | all 12 guides |

Pages that already had custom Rank Math meta (home, landing pages, about, 8 guides) were not changed.

The local copy prints `noindex` on every URL because of `medhub-local-safety.php`; that is local-only.

## Needs a decision (not done)

1. **301 redirects** recommended in `docs/url-inventory.csv` (URL changes, so not applied):
   `/return-policy/` → `/refund-policy/`, `/terms-and-conditions/` → `/terms-conditions/`,
   `/multiple-shipments-policy-if-applicable/` → merge into `/delivery-policy/`,
   `/device-on-rent/` → `/product-category/medical-equipment-rental/`,
   `/inogen-portable-oxygen-concentrator/` → `/brand/inogen/`, `/devilbiss-blue-cpap/` → `/product-category/cpap/`,
   `/compact-525-oxygen-concentrator/` and `/vacuaide-portable-suction-unit/` → matching products.
   Until then they have proper meta. Four legacy pages have images without alt text.
2. **Keyword overlap between landing pages and older guides**: `/cpap-in-dubai/` vs the CPAP guide,
   `/bipap-in-dubai/` vs two BiPAP guides, `/portable-oxygen-machine-in-dubai/` vs its buyer's guide,
   `/oxygen-concentrator-in-dubai/` vs the how-it-works guide. Focus keywords now differ; the guides
   should also link to their landing page (content edit).
3. **Claims in existing titles** written before this work: "Best …" (landing pages) and
   "Authorized Devilbiss Service Centre". Keep only if the client can stand behind them.
4. **Brands**: 34 brand pages have no description (Rank Math uses the brand description).
5. **Products** (203): titles now fit; product descriptions were not reviewed (D6: export + impact list first).

## Going live

Nothing here is on medhub.ae yet. The same script can be run on production only after approval
and a fresh backup (it refuses to run outside the local copy as written).

# Pass 2: every page, post, product, category and brand (2026-09-27)

Pipeline (all local, reversible):

```
tooling/local/wp.sh eval-file tooling/seo/export.php tmp/seo-export.json    # read-only export
node tooling/seo/plan.mjs tmp/seo-export.json                              # → tooling/seo/plan.json
node tooling/seo/check.mjs tmp/seo-export.json [tooling/seo/plan.json]     # checklist before/after
tooling/local/wp.sh eval-file tooling/local/apply-seo-plan.php apply|status|rollback
```

`check.mjs` models Rank Math's published tests (rankmath.com/kb/score-100-in-tests). Rank Math's own
score is only calculated in the editor, so the stored `rank_math_seo_score` values are stale until a
post is opened and updated there.

| Type | Tests passed before | After |
| --- | --- | --- |
| Pages (25 indexable) | 49% | 64% |
| Posts (12) | 61% | 84% |
| Products (202) | 27% | 79% |
| Product categories (33 indexable) | 72% | 80% |
| Brands (34 indexable) | 20% | 73% |

What changed:
- **Products**: title (`<name> in Dubai | MedHub`, ≤ 60 chars), description (110–160 chars, whole sentences),
  unique focus keyword from the product name (name itself unchanged, D6), an H2 with the product name at
  the top of the description, one closing paragraph with links to its category/brand and the contact
  page, featured-image alt text = product name where empty (178 images).
- **Brands**: title, description, keyword; a short intro (brand description, shown on the brand page)
  listing its real categories and products; the 2 brands with no published products (BMC Healthcare, Longfian Scientific) set to noindex.
- **Categories**: meta for the 4 without it; Uncategorized noindex; `%sitename%` titles fixed.
- **Guides**: one opening sentence with the focus keyword, a closing section (H2 + internal links, and
  NHLBI / FDA links where relevant), clearer keywords/descriptions for 6063, 6067, 6069, 6230, 6233,
  6570, 6600.
- **Landing pages** (tooling/content, re-applied with apply-content.php): focus keyword in the intro and
  in the FAQ heading ("CPAP in Dubai: questions"); home intro + "Why MedHub" heading mention "medical
  equipment supplier in Dubai"; hidden U+2060 character removed from the sleep-apnea keyword.
- Policies: keyword = the phrase the page is about (content untouched).

Not done on purpose (would need new facts or claims): "power"/sentiment words in titles ("Best",
"Top"), external links on products, product descriptions under 200 words (123 products need real
specifications from the manufacturer), long URLs (URL changes need approval).

Rollback order if needed: apply-seo-plan.php rollback, then apply-seo.php rollback.
