<?php

final class FAQZIN
{
    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('init', [$this, 'register_content']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'register_settings_page']);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_shortcode('faqzin', [$this, 'render_shortcode']);
        add_shortcode('faq_accordion', [$this, 'render_faq_accordion']);
    }

    public function load_textdomain(): void
    {
        load_plugin_textdomain('faqzin', false, dirname(plugin_basename(FAQZIN_FILE)) . '/languages');
    }

    public function register_assets(): void
    {
        wp_register_style('faqzin', FAQZIN_URL . 'assets/faqzin.css', [], FAQZIN_VER);
        wp_register_script('faqzin', FAQZIN_URL . 'assets/faqzin.js', ['jquery'], FAQZIN_VER, true);

        $custom_css = get_option('faqzin_custom_css', '');
        if ($custom_css) {
            wp_add_inline_style('faqzin', $this->sanitize_custom_css($custom_css));
        }
    }

    public function render_shortcode(array $atts = [], $content = null): string
    {
        wp_enqueue_style('faqzin');
        wp_enqueue_script('faqzin');

        $atts = shortcode_atts([
            'class' => '',
        ], $atts, 'faqzin');

        $extra_classes = array_filter(array_map('sanitize_html_class', preg_split('/\s+/', (string) $atts['class'])));
        $wrapper_classes = implode(' ', array_filter(array_merge(['faqzin'], $extra_classes)));

        $content = $content ?: '';

        ob_start();
        ?>
        <div class="<?php echo esc_attr($wrapper_classes); ?>" role="list">
            <?php echo wp_kses_post(do_shortcode($content)); ?>
        </div>
        <?php

        return trim((string) ob_get_clean());
    }

    public function register_content(): void
    {
        $labels = [
            'name' => __('FAQs', 'faqzin'),
            'singular_name' => __('FAQ', 'faqzin'),
            'add_new' => __('Add New', 'faqzin'),
            'add_new_item' => __('Add New FAQ', 'faqzin'),
            'edit_item' => __('Edit FAQ', 'faqzin'),
            'new_item' => __('New FAQ', 'faqzin'),
            'view_item' => __('View FAQ', 'faqzin'),
            'search_items' => __('Search FAQs', 'faqzin'),
            'not_found' => __('No FAQs found', 'faqzin'),
            'not_found_in_trash' => __('No FAQs found in Trash', 'faqzin'),
            'all_items' => __('All FAQs', 'faqzin'),
            'menu_name' => __('FAQs', 'faqzin'),
        ];

        register_post_type('faqzin_faq', [
            'labels' => $labels,
            'public' => true,
            'supports' => ['title', 'editor', 'revisions', 'page-attributes'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-editor-help',
            'rewrite' => ['slug' => 'faq'],
        ]);

        register_taxonomy('faqzin_category', ['faqzin_faq'], [
            'labels' => [
                'name' => __('FAQ Categories', 'faqzin'),
                'singular_name' => __('FAQ Category', 'faqzin'),
            ],
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'faq-category'],
        ]);
    }

    public function render_faq_accordion(array $atts = []): string
    {
        wp_enqueue_style('faqzin');
        wp_enqueue_script('faqzin');

        $atts = shortcode_atts([
            'class' => '',
            'category' => '',
            'limit' => -1,
        ], $atts, 'faq_accordion');

        $extra_classes = array_filter(array_map('sanitize_html_class', preg_split('/\s+/', (string) $atts['class'])));
        $wrapper_classes = implode(' ', array_filter(array_merge(['faqzin'], $extra_classes)));

        $query_args = [
            'post_type' => 'faqzin_faq',
            'posts_per_page' => (int) $atts['limit'],
            'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
        ];

        $category = sanitize_text_field((string) $atts['category']);
        if ($category !== '') {
            $query_args['tax_query'] = [[
                'taxonomy' => 'faqzin_category',
                'field' => 'slug',
                'terms' => array_map('sanitize_title', array_filter(array_map('trim', explode(',', $category)))),
            ]];
        }

        $faq_query = new \WP_Query($query_args);

        $faqs = [];
        if ($faq_query->have_posts()) {
            while ($faq_query->have_posts()) {
                $faq_query->the_post();
                $faqs[] = [
                    'question' => get_the_title(),
                    'answer' => get_the_content(),
                ];
            }
            wp_reset_postdata();
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static function (array $faq) {
                return [
                    '@type' => 'Question',
                    'name' => wp_strip_all_tags($faq['question']),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => wpautop(wp_kses_post($faq['answer'])),
                    ],
                ];
            }, $faqs),
        ];

        ob_start();
        ?>
        <div class="<?php echo esc_attr($wrapper_classes); ?>" role="list">
            <?php foreach ($faqs as $faq) : ?>
                <details class="faqzin-item" role="listitem">
                    <summary class="faqzin-question" aria-expanded="false">
                        <?php echo esc_html($faq['question']); ?>
                    </summary>
                    <div class="faqzin-answer" aria-hidden="true">
                        <?php echo wp_kses_post(wpautop($faq['answer'])); ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($schema['mainEntity'])) : ?>
            <script type="application/ld+json">
                <?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
            </script>
        <?php endif; ?>
        <?php

        return trim((string) ob_get_clean());
    }

    public function register_settings(): void
    {
        register_setting('faqzin_settings', 'faqzin_custom_css', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_custom_css'],
            'default' => '',
        ]);

        add_settings_section('faqzin_styles_section', __('Custom Styles', 'faqzin'), null, 'faqzin');

        add_settings_field(
            'faqzin_custom_css',
            __('Custom CSS', 'faqzin'),
            function () {
                $custom_css = get_option('faqzin_custom_css', '');
                ?>
                <textarea
                    name="faqzin_custom_css"
                    id="faqzin_custom_css"
                    rows="8"
                    class="large-text code"
                    aria-describedby="faqzin_custom_css_description"
                ><?php echo esc_textarea($custom_css); ?></textarea>
                <p id="faqzin_custom_css_description" class="description">
                    <?php esc_html_e('Add custom CSS for the FAQ accordion. HTML and script tags are stripped for safety.', 'faqzin'); ?>
                </p>
                <?php
            },
            'faqzin',
            'faqzin_styles_section'
        );
    }

    public function register_settings_page(): void
    {
        add_options_page(
            __('FAQzin Settings', 'faqzin'),
            __('FAQzin', 'faqzin'),
            'manage_options',
            'faqzin',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('FAQzin Settings', 'faqzin'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('faqzin_settings');
                do_settings_sections('faqzin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function sanitize_custom_css($css): string
    {
        $css = wp_unslash((string) $css);
        $css = trim(wp_strip_all_tags($css, true));

        return $css;
    }
}
