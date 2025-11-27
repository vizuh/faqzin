<?php
/**
 * Universal Shortcode Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class FAQzin_Shortcode {
    
    public function __construct() {
        add_shortcode('faqzin', array($this, 'render_shortcode'));
    }
    
    /**
     * Render FAQ Shortcode
     * 
     * Usage:
     * [faqzin]
     * [faqzin category="treatment"]
     * [faqzin category="treatment,pricing" limit="5"]
     * [faqzin class="my-custom-class"]
     */
    public function render_shortcode($atts) {
        // Enqueue assets
        wp_enqueue_style('faqzin-styles');
        wp_enqueue_script('faqzin-accordion');
        
        // Parse attributes
        $atts = shortcode_atts(array(
            'category'     => '',
            'limit'        => -1,
            'class'        => '',
            'schema'       => 'yes',
        ), $atts, 'faqzin');
        
        // Build query args
        $query_args = array(
            'post_type'      => 'faq',
            'posts_per_page' => intval($atts['limit']),
            'orderby'        => array('menu_order' => 'ASC', 'date' => 'DESC'),
            'post_status'    => 'publish',
        );
        
        // Filter by category if specified
        if (!empty($atts['category'])) {
            $categories = array_map('trim', explode(',', $atts['category']));
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'faq_category',
                    'field'    => 'slug',
                    'terms'    => $categories,
                ),
            );
        }
        
        // Query FAQs
        $faq_query = new WP_Query($query_args);
        
        if (!$faq_query->have_posts()) {
            return '';
        }
        
        // Build FAQs array for schema
        $faqs = array();
        
        // Start output buffering
        ob_start();
        
        // Wrapper classes
        $wrapper_classes = array('faqzin-container');
        if (!empty($atts['class'])) {
            $extra_classes = array_map('sanitize_html_class', explode(' ', $atts['class']));
            $wrapper_classes = array_merge($wrapper_classes, $extra_classes);
        }
        
        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>">
            <div class="faqzin-list">
                <?php
                while ($faq_query->have_posts()) {
                    $faq_query->the_post();
                    
                    // Get clean data directly from post fields
                    $post_id = get_the_ID();
                    $question = get_the_title();
                    $answer_raw = get_post_field('post_content', $post_id);
                    $answer = apply_filters('the_content', $answer_raw);
                    
                    // Add to schema array
                    $faqs[] = array(
                        'question' => $question,
                        'answer'   => wp_strip_all_tags($answer_raw),
                    );
                    
                    $this->render_faq_item($question, $answer);
                }
                wp_reset_postdata();
                ?>
            </div>
        </div>
        
        <?php
        // Output schema if enabled
        if ($atts['schema'] === 'yes' && !empty($faqs)) {
            $this->output_schema($faqs);
        }
        
        return ob_get_clean();
    }
    
    /**
     * Render single FAQ item with HTML5 details
     */
    private function render_faq_item($question, $answer) {
        ?>
        <details class="faqzin-item" role="listitem">
            <summary class="faqzin-question-wrapper" aria-expanded="false">
                <span class="faqzin-question"><?php echo esc_html($question); ?></span>
                <span class="faqzin-icon">+</span>
            </summary>
            <div class="faqzin-answer" aria-hidden="true">
                <?php echo wpautop(wp_kses_post($answer)); ?>
            </div>
        </details>
        <?php
    }
    
    /**
     * Output FAQPage Schema
     */
    private function output_schema($faqs) {
        $schema = array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array(),
        );
        
        foreach ($faqs as $faq) {
            $schema['mainEntity'][] = array(
                '@type'          => 'Question',
                'name'           => $faq['question'],
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => $faq['answer'],
                ),
            );
        }
        
        ?>
        <script type="application/ld+json">
        <?php echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
        </script>
        <?php
    }
}

// Initialize
new FAQzin_Shortcode();
