<?php

final class FAQZIN
{
    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_shortcode('faqzin', [$this, 'render_shortcode']);
    }

    public function load_textdomain(): void
    {
        load_plugin_textdomain('faqzin', false, dirname(plugin_basename(FAQZIN_FILE)) . '/languages');
    }

    public function register_assets(): void
    {
        wp_register_style('faqzin', FAQZIN_URL . 'assets/faqzin.css', [], FAQZIN_VER);
        wp_register_script('faqzin', FAQZIN_URL . 'assets/faqzin.js', ['jquery'], FAQZIN_VER, true);
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
}
