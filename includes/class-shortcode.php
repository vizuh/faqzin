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
        
        // OUTPUT INLINE CSS FIRST - USER'S ORIGINAL CSS!
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
     * Output inline CSS - USER'S ORIGINAL CSS!
     */
    private function output_inline_css() {
        static $css_loaded = false;
        if ($css_loaded) {
            return;
        }
        $css_loaded = true;
        ?>
<style data-no-optimize="1" data-noptimize="1" data-cfasync="false">
.faqzin-container{width:100%;max-width:100%;padding:20px 0}
.faqzin-list{display:flex;flex-direction:column;gap:12px}
.faqzin-item{background:#fff;border:2px solid #e8e8e8;border-radius:8px;overflow:hidden;transition:border-color .3s,box-shadow .3s,transform .3s}
.faqzin-item:hover{border-color:#d0d0d0;box-shadow:0 4px 12px rgba(0,0,0,.08);transform:translateY(-2px)}
.faqzin-item[open]{border-color:#007bff;box-shadow:0 6px 20px rgba(0,123,255,.15)}
.faqzin-question-wrapper{display:flex;justify-content:space-between;align-items:center;padding:22px 24px;cursor:pointer;user-select:none;background:#fafafa;transition:background-color .3s;position:relative;list-style:none}
.faqzin-question-wrapper::-webkit-details-marker{display:none}
.faqzin-question-wrapper::marker{display:none}
.faqzin-question-wrapper:before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:transparent;transition:background .3s}
.faqzin-item:hover .faqzin-question-wrapper:before{background:#007bff}
.faqzin-item[open] .faqzin-question-wrapper{background:#f8f9ff}
.faqzin-item[open] .faqzin-question-wrapper:before{background:#007bff}
.faqzin-question{flex:1;font-weight:600;font-size:22px;color:#2c3e50;padding-right:24px;line-height:1.5;position:relative;padding-left:32px}
.faqzin-question:before{content:'Q';position:absolute;left:0;top:50%;transform:translateY(-50%);width:24px;height:24px;background:#007bff;color:#fff;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;box-shadow:0 2px 4px rgba(0,123,255,.3)}
.faqzin-item[open] .faqzin-question{color:#007bff}
.faqzin-icon{flex-shrink:0;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#007bff;background:#f0f4ff;border-radius:50%;transition:transform .3s,background-color .3s,color .3s}
.faqzin-item:hover .faqzin-icon{background:#e3ebff}
.faqzin-item[open] .faqzin-icon{transform:rotate(45deg);background:#007bff;color:#fff;box-shadow:0 4px 8px rgba(0,123,255,.3)}
.faqzin-answer{padding:0 24px 0 56px;font-size:16px;color:#555;line-height:1.8;background:#fafafa;position:relative;margin-top:-8px;padding-top:16px;padding-bottom:24px;border-top:1px solid #e8e8e8;animation:fadeInAnswer .3s ease}
@keyframes fadeInAnswer{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
.faqzin-answer:before{content:'A';display:inline-block;width:22px;height:22px;background:#28a745;color:#fff;border-radius:4px;text-align:center;line-height:22px;font-size:12px;font-weight:700;margin-right:12px;margin-left:-32px;vertical-align:middle;float:left;margin-top:2px;box-shadow:0 2px 4px rgba(40,167,69,.3)}
.faqzin-answer p{margin:0 0 16px 0}
.faqzin-answer p:last-child{margin-bottom:0}
.faqzin-answer ul,.faqzin-answer ol{margin:16px 0;padding-left:24px}
.faqzin-answer li{margin-bottom:10px;line-height:1.8}
.faqzin-answer a{color:#007bff;text-decoration:none;font-weight:500}
.faqzin-answer a:hover{text-decoration:underline}
.faqzin-answer strong{color:#2c3e50;font-weight:600}
.faqzin-answer code{background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;font-size:.9em}
@media(max-width:767px){.faqzin-question-wrapper{padding:18px 16px}.faqzin-question{font-size:16px;padding-left:28px;padding-right:16px}.faqzin-question:before{width:20px;height:20px;font-size:11px}.faqzin-answer{padding:16px 16px 20px 44px;font-size:14px}.faqzin-answer:before{width:20px;height:20px;line-height:20px;font-size:11px;margin-left:-28px}.faqzin-icon{width:28px;height:28px;font-size:20px}.faqzin-list{gap:10px}}
@media(max-width:480px){.faqzin-question-wrapper{padding:16px 14px}.faqzin-question{font-size:14px}.faqzin-answer{font-size:13px}}
.faqzin-question-wrapper:focus{outline:2px solid #007bff;outline-offset:2px}
@media print{.faqzin-item{page-break-inside:avoid}.faqzin-item[open]{border:1px solid #333}}
.faqzin-container .entry-meta,.faqzin-container .post-meta,.faqzin-container .entry-footer,.faqzin-container .posted-on,.faqzin-container .byline,.faqzin-container .author,.faqzin-container .updated,.faqzin-container .cat-links,.faqzin-container .tags-links,.faqzin-container .comments-link,.faqzin-container time,.faqzin-item .entry-meta,.faqzin-item .post-meta,.faqzin-item .entry-footer,.faqzin-item .posted-on,.faqzin-item .byline,.faqzin-answer .entry-meta,.faqzin-answer .post-meta,.faqzin-answer .posted-on,.faqzin-answer .byline,.faqzin-answer .author,.faqzin-answer time{display:none!important;visibility:hidden!important;opacity:0!important;height:0!important;margin:0!important;padding:0!important}
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
<script data-no-optimize="1" data-noptimize="1" data-cfasync="false">
(function(){if(typeof window.faqzinLoaded!=='undefined')return;window.faqzinLoaded=true;document.addEventListener('DOMContentLoaded',function(){var items=document.querySelectorAll('.faqzin-item');items.forEach(function(item){item.addEventListener('toggle',function(){if(item.open){var summary=item.querySelector('.faqzin-question-wrapper');var answer=item.querySelector('.faqzin-answer');if(summary)summary.setAttribute('aria-expanded','true');if(answer)answer.setAttribute('aria-hidden','false')}else{var summary=item.querySelector('.faqzin-question-wrapper');var answer=item.querySelector('.faqzin-answer');if(summary)summary.setAttribute('aria-expanded','false');if(answer)answer.setAttribute('aria-hidden','true')}})})})})();
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