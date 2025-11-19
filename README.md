# faqzin
Lightweight FAQ plugin (shortcode: `[faqzin]`).

## Install
Upload `faqzin` to `wp-content/plugins/` and activate.

## Usage
Wrap your FAQ markup with `[faqzin] ... [/faqzin]` and use `.faqzin-item`, `.faqzin-question`, and `.faqzin-answer` classes inside. Add extra wrapper classes with the `class` attribute (e.g., `[faqzin class="my-faq"]`).

## Dev
- Enqueues `assets/faqzin.css` and `assets/faqzin.js` only when the shortcode is rendered.
- PHP 7.4+ / 8.x compatible.

## License
GPL-2.0-or-later
