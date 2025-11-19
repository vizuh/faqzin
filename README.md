# faqzin
Lightweight FAQ plugin with a built-in FAQ custom post type, semantic accordion shortcode, and optional custom CSS.

## Install
Upload `faqzin` to `wp-content/plugins/` and activate.

## Usage
- Add FAQs under **FAQs → Add New**. Optionally assign **FAQ Categories** and order items with the **Order** field.
- Display them with `[faq_accordion]`, e.g.:
  - `[faq_accordion]` – output all FAQs.
  - `[faq_accordion category="billing"]` – limit to a category slug.
  - `[faq_accordion class="my-faq"]` – add extra wrapper classes.
- The legacy `[faqzin]...[/faqzin]` shortcode still wraps custom markup if you prefer manual content.

## Dev
- Registers a `faqzin_faq` custom post type and `faqzin_category` taxonomy.
- Enqueues `assets/faqzin.css` and `assets/faqzin.js` only when the shortcode is rendered, and attaches custom CSS set in **Settings → FAQzin**.
- Outputs semantic `<details>/<summary>` markup plus JSON-LD FAQPage schema for better SEO.
- PHP 7.4+ / 8.x compatible.

## License
GPL-2.0-or-later
