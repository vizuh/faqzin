<?php
/**
 * WP-CLI: wp faqzin import <file> [--overwrite]
 */

if (!defined('ABSPATH')) {
    exit;
}

class FAQzin_CLI {

    /**
     * Imports FAQ categories and FAQs from a JSON file.
     *
     * Categories and FAQs are matched by slug. Existing ones are skipped, so edits made in
     * WordPress survive a re-run; --overwrite replaces existing FAQs with the file's version.
     *
     * The file:
     *
     *     {
     *       "categories": [ { "slug": "general", "name": "General", "order": 1 } ],
     *       "faqs": [ {
     *         "slug": "opening-hours", "question": "When are you open?",
     *         "answer": "First paragraph.\n\nSecond paragraph.",
     *         "category": "general", "order": 1, "featured": true, "status": "publish"
     *       } ]
     *     }
     *
     * "answer" is plain text: a blank line starts a new paragraph. "status" is "publish"
     * (default) or "draft". "order", "featured" and "status" are optional.
     *
     * ## OPTIONS
     *
     * <file>
     * : Path to the JSON file.
     *
     * [--overwrite]
     * : Replace FAQs that already exist (question, answer, category, order, featured, status).
     *
     * ## EXAMPLES
     *
     *     wp faqzin import faqs.json
     *
     * @when after_wp_load
     */
    public function import($args, $assoc_args) {
        $file = $args[0];
        if (!is_readable($file)) {
            WP_CLI::error("Cannot read {$file}.");
        }
        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data)) {
            WP_CLI::error("{$file} is not valid JSON.");
        }
        $overwrite = WP_CLI\Utils\get_flag_value($assoc_args, 'overwrite', false);

        $category_ids = array();
        foreach ($data['categories'] ?? array() as $category) {
            $term = get_term_by('slug', $category['slug'], 'faq_category');
            if (!$term) {
                $created = wp_insert_term($category['name'], 'faq_category', array('slug' => $category['slug']));
                if (is_wp_error($created)) {
                    WP_CLI::warning("Category {$category['slug']}: " . $created->get_error_message());
                    continue;
                }
                if (isset($category['order'])) {
                    update_term_meta($created['term_id'], FAQzin_Fields::ORDER_META, (int) $category['order']);
                }
                $term = get_term($created['term_id'], 'faq_category');
                WP_CLI::log("Category created: {$category['slug']}");
            }
            $category_ids[$category['slug']] = (int) $term->term_id;
        }

        $created = $updated = $skipped = 0;
        foreach ($data['faqs'] ?? array() as $faq) {
            $category = $faq['category'] ?? '';
            if ('' !== $category && !isset($category_ids[$category])) {
                $term = get_term_by('slug', $category, 'faq_category');
                if (!$term) {
                    WP_CLI::warning("FAQ {$faq['slug']}: unknown category {$category}, skipped.");
                    $skipped++;
                    continue;
                }
                $category_ids[$category] = (int) $term->term_id;
            }
            $existing_id = $this->find_faq($faq['slug']);
            if ($existing_id && !$overwrite) {
                $skipped++;
                continue;
            }
            $post = array(
                'post_type'    => 'faq',
                'post_title'   => $faq['question'],
                'post_name'    => $faq['slug'],
                'post_content' => self::answer_blocks((string) $faq['answer']),
                'post_status'  => 'draft' === ($faq['status'] ?? '') ? 'draft' : 'publish',
                'menu_order'   => (int) ($faq['order'] ?? 0),
            );
            // wp_update_post keeps the fields not listed (author, date, password, excerpt).
            if ($existing_id) {
                $post['ID'] = $existing_id;
                $id = wp_update_post(wp_slash($post), true);
            } else {
                $id = wp_insert_post(wp_slash($post), true);
            }
            if (is_wp_error($id)) {
                WP_CLI::warning("FAQ {$faq['slug']}: " . $id->get_error_message());
                $skipped++;
                continue;
            }
            update_post_meta($id, FAQzin_Fields::FEATURED_META, empty($faq['featured']) ? 0 : 1);
            wp_set_object_terms($id, '' === $category ? array() : array($category_ids[$category]), 'faq_category');
            $existing_id ? $updated++ : $created++;
        }

        WP_CLI::success("FAQs created: {$created}, updated: {$updated}, skipped: {$skipped}.");
    }

    /**
     * The FAQ with this slug in any status but trash, or 0.
     * Direct lookup: get_page_by_path() and get_posts() miss drafts here.
     */
    private function find_faq($slug) {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'faq' AND post_name = %s AND post_status NOT IN ('trash', 'auto-draft') LIMIT 1",
            $slug
        ));
    }

    /**
     * Plain text (blank line = new paragraph, line break kept) as paragraph blocks.
     */
    public static function answer_blocks($text) {
        $blocks = array();
        foreach (preg_split('/\r?\n\s*\r?\n/', trim($text)) as $paragraph) {
            $paragraph = trim($paragraph);
            if ('' === $paragraph) {
                continue;
            }
            $html     = nl2br(esc_html($paragraph), false);
            $blocks[] = "<!-- wp:paragraph -->\n<p>{$html}</p>\n<!-- /wp:paragraph -->";
        }
        return implode("\n\n", $blocks);
    }
}

WP_CLI::add_command('faqzin', 'FAQzin_CLI');
