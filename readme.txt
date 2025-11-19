=== faqzin ===
Contributors: yourwporguser
Tags: faq, accordion
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight FAQ accordion with a custom post type, semantic markup, and optional custom CSS.

== Description ==
Add FAQs under **FAQs → Add New** and optionally group them with **FAQ Categories**. Display them anywhere with the `[faq_accordion]` shortcode, e.g. `[faq_accordion]`, `[faq_accordion category="billing"]`, or `[faq_accordion class="my-faq"]`. Custom CSS can be added under **Settings → FAQzin**. The legacy `[faqzin]...[/faqzin]` wrapper shortcode remains available for manual markup.

== Changelog ==
= 0.2.0 =
* Add FAQ custom post type and category taxonomy.
* New `[faq_accordion]` shortcode with semantic `<details>/<summary>` markup and JSON-LD FAQPage schema.
* Custom CSS settings page with sanitization applied to inline styles.

= 0.1.1 =
* Enqueue assets only when the shortcode renders and add optional wrapper classes.

= 0.1.0 =
* First release.
