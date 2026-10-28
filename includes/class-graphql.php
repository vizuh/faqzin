<?php
/**
 * WPGraphQL fields for headless sites. The post type and taxonomy are exposed as faqs and
 * faqCategories (class-cpt.php); this adds Faq.answerText, Faq.featured and FaqCategory.order.
 * Runs only when WPGraphQL is active.
 */

if (!defined('ABSPATH')) {
    exit;
}

class FAQzin_GraphQL {

    public function __construct() {
        add_action('graphql_register_types', array($this, 'register_fields'));
    }

    public function register_fields() {
        register_graphql_field('Faq', 'answerText', array(
            'type'        => 'String',
            'description' => __('The answer as plain text: a blank line between paragraphs, a single line break within one.', 'faqzin'),
            'resolve'     => function ($post) {
                return self::answer_text((string) get_post_field('post_content', $post->databaseId));
            },
        ));
        register_graphql_field('Faq', 'featured', array(
            'type'        => 'Boolean',
            'description' => __('Marked Featured in the FAQ editor.', 'faqzin'),
            'resolve'     => function ($post) {
                return FAQzin_Fields::is_featured($post->databaseId);
            },
        ));
        register_graphql_field('FaqCategory', 'order', array(
            'type'        => 'Int',
            'description' => __('Position of the category, lowest first; null when unset.', 'faqzin'),
            'resolve'     => function ($term) {
                return FAQzin_Fields::category_order($term->databaseId);
            },
        ));
    }

    /**
     * The answer's post content (blocks or classic HTML) as plain text.
     */
    public static function answer_text($content) {
        $html = do_blocks($content);
        if (false === stripos($html, '<p')) {
            $html = wpautop($html);
        }
        // Whitespace in HTML is not a line break; only the tags below make one.
        $html = preg_replace('/\s+/u', ' ', $html);
        $html = preg_replace('#<br\s*/?>#i', "\n", $html);
        $html = preg_replace('#</(p|li|h[1-6]|blockquote|pre|div|ul|ol|figure)>#i', "\n\n", $html);
        $text = html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]*\n[ \t]*/", "\n", $text);
        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }
}

new FAQzin_GraphQL();
