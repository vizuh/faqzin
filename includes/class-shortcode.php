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
        
        // OUTPUT INLINE CSS FIRST - ALWAYS LOADS!
        $this->output_inline_css();
        
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
        
        // Output inline JavaScript for accordion
        $this->output_inline_js();
        
        return ob_get_clean();
    }
    
    /**
     * Output inline CSS - ALWAYS LOADS!
     */
    private function output_inline_css() {
        static $css_loaded = false;
        if ($css_loaded) {
            return;
        }
        $css_loaded = true;
        ?>
<style data-no-optimize="1" data-noptimize="1">
.faqzin-container{max-width:100%;margin:0 auto}
.faqzin-list{display:flex;flex-direction:column;gap:12px}
.faqzin-item{background:#fff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;transition:all .3s ease}
.faqzin-item:hover{border-color:#d1d5db;box-shadow:0 2px 8px rgba(0,0,0,.05)}
.faqzin-item[open]{border-color:#3b82f6}
.faqzin-question-wrapper{display:flex;align-items:center;justify-content:space-between;padding:20px;cursor:pointer;user-select:none;transition:background-color .2s;gap:16px}
.faqzin-question-wrapper:hover{background-color:#f9fafb}
.faqzin-item[open] .faqzin-question-wrapper{background-color:#eff6ff}
.faqzin-question-wrapper::before{content:"Q";display:flex;align-items:center;justify-content:center;width:32px;height:32px;background:#3b82f6;color:#fff;border-radius:6px;font-weight:700;font-size:16px;flex-shrink:0}
.faqzin-question{flex:1;font-size:16px;font-weight:600;color:#1f2937;line-height:1.5}
.faqzin-icon{display:flex;align-items:center;justify-content:center;width:24px;height:24px;color:#3b82f6;font-size:20px;font-weight:700;transition:transform .3s;flex-shrink:0}
.faqzin-item[open] .faqzin-icon{transform:rotate(45deg)}
.faqzin-answer{padding:0 20px 20px 68px;color:#4b5563;font-size:15px;line-height:1.7;animation:slideDown .3s ease}
.faqzin-answer p{margin:0 0 12px 0}
.faqzin-answer p:last-child{margin-bottom:0}
@keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:768px){.faqzin-question-wrapper{padding:16px}.faqzin-question{font-size:15px}.faqzin-answer{padding:0 16px 16px 56px;font-size:14px}}
</style>
        <?php
    }
    
    /**
     * Output inline JavaScript for accordion
     */
    private function output_inline_js() {
        static $js_loaded = false;
        if ($js_loaded) {
            return;
        }
        $js_loaded = true;
        ?>
<script data-no-optimize="1" data-noptimize="1">
(function(){if(typeof window.faqzinLoaded!=='undefined')return;window.faqzinLoaded=true;document.addEventListener('DOMContentLoaded',function(){var items=document.querySelectorAll('.faqzin-item');items.forEach(function(item){var summary=item.querySelector('.faqzin-question-wrapper');var answer=item.querySelector('.faqzin-answer');if(summary&&answer){summary.addEventListener('click',function(e){var isOpen=item.hasAttribute('open');items.forEach(function(otherItem){if(otherItem!==item&&otherItem.hasAttribute('open')){otherItem.removeAttribute('open');var otherSummary=otherItem.querySelector('.faqzin-question-wrapper');var otherAnswer=otherItem.querySelector('.faqzin-answer');if(otherSummary)otherSummary.setAttribute('aria-expanded','false');if(otherAnswer)otherAnswer.setAttribute('aria-hidden','true')}});if(!isOpen){item.setAttribute('open','');summary.setAttribute('aria-expanded','true');answer.setAttribute('aria-hidden','false')}else{item.removeAttribute('open');summary.setAttribute('aria-expanded','false');answer.setAttribute('aria-hidden','true')}})}})})})();
</script>
        <?php
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