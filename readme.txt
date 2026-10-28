=== FAQzin ===
Contributors: pauloreducino, hfdutra, atroci
Tags: faq, accordion, schema, elementor, divi
Requires at least: 5.8
Tested up to: 7.1
Stable tag: 2.1.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Professional FAQ Manager with Schema, HTML5 Accordion, Divi & Elementor Ready.

== Description ==

FAQzin is the ultimate FAQ solution for WordPress. It combines a professional design with technical excellence, offering native HTML5 accordions, automatic Schema.org markup for SEO, and seamless compatibility with modern page builders like Elementor and Divi.

**Key Features:**

*   **HTML5 Native Accordion:** Uses `<details>` and `<summary>` for maximum performance and accessibility. Works even without JavaScript!
*   **SEO Optimized:** Automatically generates `FAQPage` JSON-LD schema for your FAQ lists and `QAPage` schema for individual FAQ pages.
*   **Premium Design:** Beautiful, responsive styling with Q/A badges, smooth animations, and clean typography.
*   **Page Builder Ready:** Fully compatible with Gutenberg, Elementor, Divi, and others.
*   **Developer Friendly:** Clean code, hooks, and filters.
*   **Headless Ready:** With WPGraphQL, FAQs (`faqs`, with `answerText` and `featured`) and categories (`faqCategories`, with `order`) are in the GraphQL schema. Headless mode turns off FAQ pages, archives and schema on WordPress.
*   **WP-CLI Import:** `wp faqzin import faqs.json` creates categories and FAQs from a JSON file.

== Installation ==

1.  Upload the plugin files to the `/wp-content/plugins/faqzin` directory, or install the plugin through the WordPress plugins screen directly.
2.  Activate the plugin through the 'Plugins' screen in WordPress.
3.  Use the `[faqzin]` shortcode to display your FAQs.

== Frequently Asked Questions ==

= How do I add an FAQ? =

Go to **FAQs > Add New** in your WordPress dashboard. Enter the question as the title and the answer in the content area.

= How do I display FAQs? =

Simply add the `[faqzin]` shortcode to any page or post. You can also filter by category: `[faqzin category="my-category"]`.

= Does it work with Elementor? =

Yes! You can use the Shortcode widget in Elementor to display your FAQs.

= Can another site (headless) show the FAQs? =

Yes. Install WPGraphQL and tick **FAQs > Settings > Headless mode**. The other site reads `faqs` and `faqCategories` over GraphQL; WordPress no longer publishes FAQ pages of its own.

== Screenshots ==

1.  FAQ List with Premium Design
2.  Single FAQ Page
3.  Settings Page

== Changelog ==

= 2.1.1 =
*   Fix: all admin and shortcode output escaped; Re-Order inputs sanitised (WordPress.org Plugin Check).
*   Fix: translations load through WordPress (removed the manual text-domain loader); hidden files left out of the release zip.

= 2.1.0 =
*   New: WPGraphQL support: `faqs` and `faqCategories`, with `answerText` (plain-text answer), `featured` and category `order`.
*   New: Headless mode (Settings): no FAQ pages, category archives or single-FAQ schema on WordPress.
*   New: Featured box on each FAQ and an Order field on each FAQ category.
*   New: `wp faqzin import <file.json> [--overwrite]`.

= 2.0.0 =
*   Major release: Rebranding to FAQzin.
*   New: Professional design with Q/A badges.
*   New: HTML5 native accordion support.
*   New: Automatic Schema.org generation.
*   New: Settings page with Custom CSS.
