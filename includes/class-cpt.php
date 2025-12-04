<?php
/**
 * Custom Post Type and Taxonomy Registration
 */

if (!defined('ABSPATH')) {
    exit;
}

class FAQzin_CPT {
    
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomy'));
        add_action('wp_head', array($this, 'add_single_faq_schema'));
        
        // Remove author/date from single FAQ pages
        add_filter('the_author', array($this, 'remove_author'));
        add_filter('the_date', array($this, 'remove_date'));
        add_filter('get_the_date', array($this, 'remove_date'));
        add_filter('the_time', array($this, 'remove_date'));
        add_filter('get_the_time', array($this, 'remove_date'));
        add_filter('the_modified_date', array($this, 'remove_date'));
        add_filter('get_the_modified_date', array($this, 'remove_date'));
        
        // SUPER PROTECTED CSS - Multiple methods
        add_action('wp_head', array($this, 'add_single_faq_css_protected'), 999);
        add_action('wp_footer', array($this, 'add_faq_css_fallback'), 999);
        
        // Add H1 title and spacing on single FAQ pages
        add_filter('the_content', array($this, 'add_faq_title_to_content'), 1);
        
        // Add Order column
        add_filter('manage_faq_posts_columns', array($this, 'add_order_column'));
        add_action('manage_faq_posts_custom_column', array($this, 'show_order_column'), 10, 2);
        add_filter('manage_edit-faq_sortable_columns', array($this, 'make_order_column_sortable'));
        add_action('pre_get_posts', array($this, 'order_by_menu_order'));
        
        // Drag & Drop functionality in All FAQs
        add_action('admin_enqueue_scripts', array($this, 'enqueue_drag_drop_scripts'));
        add_action('wp_ajax_faqzin_update_order', array($this, 'ajax_update_order'));
        
        // Add Re-Order submenu page
        add_action('admin_menu', array($this, 'add_reorder_submenu'));
        
        // Protect from ALL optimizers
        add_filter('autoptimize_filter_css_exclude', array($this, 'exclude_from_autoptimize'));
        add_filter('rocket_exclude_css', array($this, 'exclude_from_wp_rocket'));
        add_filter('litespeed_optimize_css_exclude', array($this, 'exclude_from_litespeed'));
        add_filter('psb_minify_html_exclude', array($this, 'exclude_from_page_speed_boost'), 10, 1);
        add_filter('psb_css_defer_exclude', array($this, 'exclude_from_page_speed_boost'), 10, 1);
    }
    
    /**
     * Exclude from Page Speed Boost
     */
    public function exclude_from_page_speed_boost($exclude) {
        if (is_array($exclude)) {
            $exclude[] = 'faqzin-critical-css';
            return $exclude;
        }
        return $exclude . ', faqzin-critical-css';
    }
    
    /**
     * Exclude from Autoptimize
     */
    public function exclude_from_autoptimize($exclude) {
        return $exclude . ', faqzin-critical-css';
    }
    
    /**
     * Exclude from WP Rocket
     */
    public function exclude_from_wp_rocket($excluded_files) {
        $excluded_files[] = 'faqzin-critical-css';
        return $excluded_files;
    }
    
    /**
     * Exclude from LiteSpeed Cache
     */
    public function exclude_from_litespeed($list) {
        $list[] = 'faqzin-critical-css';
        return $list;
    }
    
    /**
     * Add SUPER PROTECTED CSS - Method 1 (Head)
     */
    public function add_single_faq_css_protected() {
        if (!is_singular('faq')) {
            return;
        }
        
        $css = $this->get_faq_css();
        $encoded = base64_encode($css);
        ?>
<!-- FAQZIN CRITICAL CSS - PROTECTED -->
<script type="text/javascript">
(function() {
    var css = atob('<?php echo $encoded; ?>');
    var style = document.createElement('style');
    style.type = 'text/css';
    style.id = 'faqzin-critical-css';
    style.setAttribute('data-no-optimize', '1');
    style.setAttribute('data-noptimize', '1');
    style.setAttribute('data-cfasync', 'false');
    if (style.styleSheet) {
        style.styleSheet.cssText = css;
    } else {
        style.appendChild(document.createTextNode(css));
    }
    document.head.appendChild(style);
})();
</script>
<!-- /FAQZIN CRITICAL CSS -->
        <?php
    }
    
    /**
     * Add CSS Fallback - Method 2 (Footer)
     */
    public function add_faq_css_fallback() {
        if (!is_singular('faq')) {
            return;
        }
        ?>
<script type="text/javascript">
(function() {
    if (!document.getElementById('faqzin-critical-css')) {
        var css = <?php echo json_encode($this->get_faq_css()); ?>;
        var style = document.createElement('style');
        style.type = 'text/css';
        style.id = 'faqzin-critical-css-fallback';
        if (style.styleSheet) {
            style.styleSheet.cssText = css;
        } else {
            style.appendChild(document.createTextNode(css));
        }
        document.head.appendChild(style);
    }
})();
</script>
        <?php
    }
    
    /**
     * Get FAQ CSS (centralized)
     */
    private function get_faq_css() {
        return '
/* Hide author and date on single FAQ pages */
.single-faq .entry-meta,
.single-faq .post-meta,
.single-faq .entry-footer,
.single-faq .posted-on,
.single-faq .byline,
.single-faq .author,
.single-faq .vcard,
.single-faq .updated,
.single-faq time,
.single-faq .entry-date,
.single-faq .published,
body.single-faq .entry-meta,
body.single-faq .byline,
body.single-faq time,
body.single-faq .posted-on,
body.single-faq .author,
article.faq .entry-meta,
article.faq .byline,
article.faq time,
article.post-type-faq .entry-meta,
article.post-type-faq .byline,
article.post-type-faq time {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    height: 0 !important;
    width: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    overflow: hidden !important;
    position: absolute !important;
    left: -9999px !important;
}

/* Hide default title on single FAQ pages */
.single-faq .entry-title,
body.single-faq .entry-title,
article.faq .entry-title,
article.post-type-faq .entry-title {
    display: none !important;
}

/* Style custom H1 title */
.faqzin-single-title {
    font-size: 32px;
    font-weight: 700;
    line-height: 1.3;
    margin: 0 0 30px 0;
    padding: 0;
    color: #333;
}

/* Add spacing to content wrapper */
.single-faq .entry-content,
.single-faq article,
body.single-faq .entry-content,
body.single-faq article {
    padding-top: 40px !important;
    padding-bottom: 60px !important;
}

/* Ensure content has breathing room */
.single-faq main,
body.single-faq main {
    padding-top: 40px;
    padding-bottom: 60px;
}
';
    }
    
    /**
     * Add H1 title before content on single FAQ pages
     */
    public function add_faq_title_to_content($content) {
        if (is_singular('faq') && in_the_loop() && is_main_query()) {
            global $post;
            $title = '<h1 class="faqzin-single-title">' . esc_html(get_the_title()) . '</h1>';
            return $title . $content;
        }
        return $content;
    }
    
    /**
     * Add Re-Order submenu page
     */
    public function add_reorder_submenu() {
        $hook = add_submenu_page(
            'edit.php?post_type=faq',
            __('Re-Order FAQs', 'faqzin'),
            __('Re-Order', 'faqzin'),
            'edit_posts',
            'faqzin-reorder',
            array($this, 'render_reorder_page')
        );
        
        add_action('admin_print_scripts-' . $hook, array($this, 'enqueue_reorder_scripts'));
    }
    
    /**
     * Enqueue scripts for Re-Order page
     */
    public function enqueue_reorder_scripts() {
        wp_enqueue_script('jquery-ui-sortable');
    }
    
    /**
     * Render Re-Order page with drag & drop
     */
    public function render_reorder_page() {
        if (isset($_POST['faqzin_order_nonce']) && wp_verify_nonce($_POST['faqzin_order_nonce'], 'faqzin_save_order')) {
            if (isset($_POST['faq_order']) && is_array($_POST['faq_order'])) {
                $order = 0;
                foreach ($_POST['faq_order'] as $faq_id) {
                    wp_update_post(array(
                        'ID' => intval($faq_id),
                        'menu_order' => $order
                    ));
                    $order++;
                }
                echo '<div class="notice notice-success is-dismissible"><p><strong>' . __('Order saved successfully!', 'faqzin') . '</strong></p></div>';
            }
        }
        
        $faqs = get_posts(array(
            'post_type' => 'faq',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ));
        
        if (empty($faqs)) {
            ?>
            <div class="wrap">
                <h1><?php _e('Re-Order FAQs', 'faqzin'); ?></h1>
                <div class="notice notice-warning">
                    <p><?php _e('No FAQs found. Please create some FAQs first.', 'faqzin'); ?></p>
                </div>
            </div>
            <?php
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Re-Order FAQs', 'faqzin'); ?></h1>
            <p style="font-size: 14px; color: #666;">
                <strong><?php _e('Drag and drop FAQs to reorder them. Click "Save Order" when done.', 'faqzin'); ?></strong>
            </p>
            
            <form method="post" action="" id="faqzin-reorder-form">
                <?php wp_nonce_field('faqzin_save_order', 'faqzin_order_nonce'); ?>
                
                <ul id="faqzin-sortable" style="max-width: 900px; padding: 0; margin: 20px 0;">
                    <?php foreach ($faqs as $index => $faq) : ?>
                        <li data-id="<?php echo esc_attr($faq->ID); ?>" style="padding: 20px; margin: 8px 0; background: #fff; border: 1px solid #ddd; border-radius: 4px; cursor: move; list-style: none; display: flex; align-items: center; transition: all 0.3s;">
                            <input type="hidden" name="faq_order[]" value="<?php echo esc_attr($faq->ID); ?>">
                            <span style="display: inline-block; min-width: 50px; font-weight: bold; color: #2271b1; font-size: 18px;" class="order-number">
                                <?php echo esc_html($index); ?>
                            </span>
                            <span class="dashicons dashicons-menu" style="margin: 0 15px; color: #999; font-size: 24px;"></span>
                            <strong style="font-size: 15px; flex-grow: 1;"><?php echo esc_html($faq->post_title); ?></strong>
                            <?php 
                            $categories = get_the_terms($faq->ID, 'faq_category');
                            if ($categories && !is_wp_error($categories)) {
                                echo '<span style="background: #f0f0f1; padding: 4px 10px; border-radius: 3px; font-size: 12px; color: #666;">';
                                echo esc_html($categories[0]->name);
                                echo '</span>';
                            }
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <p style="margin-top: 30px;">
                    <button type="submit" class="button button-primary button-large" style="padding: 10px 30px;">
                        <span class="dashicons dashicons-yes" style="margin-top: 3px;"></span>
                        <?php _e('Save Order', 'faqzin'); ?>
                    </button>
                    <a href="<?php echo admin_url('edit.php?post_type=faq'); ?>" class="button button-secondary button-large" style="margin-left: 10px; padding: 10px 30px;">
                        <?php _e('Back to All FAQs', 'faqzin'); ?>
                    </a>
                </p>
            </form>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#faqzin-sortable').sortable({
                placeholder: 'faqzin-placeholder',
                cursor: 'move',
                opacity: 0.8,
                tolerance: 'pointer',
                start: function(e, ui) {
                    ui.placeholder.height(ui.item.height());
                },
                stop: function(e, ui) {
                    $('#faqzin-sortable li').each(function(index) {
                        $(this).find('.order-number').text(index);
                    });
                }
            });
            
            $('#faqzin-sortable li').hover(
                function() {
                    $(this).css({
                        'background': '#f6f7f7',
                        'border-color': '#2271b1',
                        'box-shadow': '0 2px 8px rgba(0,0,0,0.1)'
                    });
                },
                function() {
                    if (!$(this).hasClass('ui-sortable-helper')) {
                        $(this).css({
                            'background': '#fff',
                            'border-color': '#ddd',
                            'box-shadow': 'none'
                        });
                    }
                }
            );
        });
        </script>
        
        <style type="text/css">
        .faqzin-placeholder {
            background: #fff3cd !important;
            border: 2px dashed #ffc107 !important;
            height: 60px !important;
            visibility: visible !important;
            border-radius: 4px;
        }
        #faqzin-sortable li.ui-sortable-helper {
            box-shadow: 0 5px 15px rgba(0,0,0,0.3) !important;
            border-color: #2271b1 !important;
        }
        </style>
        <?php
    }
    
    /**
     * Enqueue drag & drop scripts for All FAQs page
     */
    public function enqueue_drag_drop_scripts($hook) {
        global $post_type;
        
        if ($hook === 'edit.php' && $post_type === 'faq') {
            wp_enqueue_script('jquery-ui-sortable');
            
            wp_add_inline_script('jquery-ui-sortable', "
                jQuery(document).ready(function($) {
                    var fixHelper = function(e, ui) {
                        ui.children().each(function() {
                            $(this).width($(this).width());
                        });
                        return ui;
                    };
                    
                    $('#the-list').sortable({
                        helper: fixHelper,
                        handle: '.column-menu_order',
                        cursor: 'move',
                        axis: 'y',
                        opacity: 0.65,
                        placeholder: 'faqzin-placeholder-list',
                        start: function(e, ui) {
                            ui.placeholder.height(ui.helper.height());
                            ui.placeholder.css('visibility', 'visible');
                        },
                        stop: function(e, ui) {
                            $('#the-list tr').each(function(index) {
                                $(this).find('.column-menu_order strong').text(index);
                            });
                        },
                        update: function(event, ui) {
                            var order = $('#the-list').sortable('serialize');
                            
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'faqzin_update_order',
                                    order: order,
                                    nonce: '" . wp_create_nonce('faqzin_sort_nonce') . "'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        if ($('.faqzin-notice').length === 0) {
                                            $('#wpbody-content .wrap h1').after('<div class=\"notice notice-success is-dismissible faqzin-notice\"><p><strong>Order saved successfully!</strong></p></div>');
                                            setTimeout(function() {
                                                $('.faqzin-notice').fadeOut();
                                            }, 3000);
                                        }
                                    }
                                }
                            });
                        }
                    });
                    
                    $('#the-list .column-menu_order').css('cursor', 'move');
                });
            ");
            
            wp_add_inline_style('wp-admin', "
                .faqzin-placeholder-list {
                    background: #fff3cd !important;
                    border: 2px dashed #ffc107 !important;
                }
                .column-menu_order {
                    cursor: move !important;
                    background: #f0f0f1;
                    text-align: center;
                    font-weight: bold;
                    width: 60px !important;
                    max-width: 60px !important;
                }
                .column-menu_order:hover {
                    background: #e0e0e1;
                }
                .ui-sortable-helper {
                    background: #fff;
                    opacity: 0.8;
                }
            ");
        }
    }
    
    /**
     * AJAX handler to update order
     */
    public function ajax_update_order() {
        check_ajax_referer('faqzin_sort_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        parse_str($_POST['order'], $data);
        
        if (!is_array($data)) {
            wp_send_json_error('Invalid data');
        }
        
        $order = 0;
        foreach ($data['post'] as $post_id) {
            wp_update_post(array(
                'ID' => intval($post_id),
                'menu_order' => $order
            ));
            $order++;
        }
        
        wp_send_json_success(array('message' => 'Order updated'));
    }
    
    /**
     * Add Order column
     */
    public function add_order_column($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            if ($key === 'title') {
                $new_columns['menu_order'] = __('☰', 'faqzin');
            }
            $new_columns[$key] = $value;
        }
        
        return $new_columns;
    }
    
    /**
     * Display Order value
     */
    public function show_order_column($column, $post_id) {
        if ($column === 'menu_order') {
            $order = get_post_field('menu_order', $post_id);
            echo '<strong>' . esc_html($order) . '</strong>';
        }
    }
    
    /**
     * Make Order column sortable
     */
    public function make_order_column_sortable($columns) {
        $columns['menu_order'] = 'menu_order';
        return $columns;
    }
    
    /**
     * Order FAQs by menu_order by default
     */
    public function order_by_menu_order($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        
        if ($query->get('post_type') === 'faq') {
            if (!$query->get('orderby')) {
                $query->set('orderby', 'menu_order');
                $query->set('order', 'ASC');
            }
        }
    }
    
    /**
     * Remove author from single FAQ pages
     */
    public function remove_author($author) {
        if (is_singular('faq')) {
            return '';
        }
        return $author;
    }
    
    /**
     * Remove date from single FAQ pages
     */
    public function remove_date($date) {
        if (is_singular('faq')) {
            return '';
        }
        return $date;
    }
    
    /**
     * Register FAQ Custom Post Type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => __('FAQs', 'faqzin'),
            'singular_name'         => __('FAQ', 'faqzin'),
            'add_new'               => __('Add New', 'faqzin'),
            'add_new_item'          => __('Add New FAQ', 'faqzin'),
            'edit_item'             => __('Edit FAQ', 'faqzin'),
            'new_item'              => __('New FAQ', 'faqzin'),
            'view_item'             => __('View FAQ', 'faqzin'),
            'search_items'          => __('Search FAQs', 'faqzin'),
            'not_found'             => __('No FAQs found', 'faqzin'),
            'not_found_in_trash'    => __('No FAQs found in Trash', 'faqzin'),
            'all_items'             => __('All FAQs', 'faqzin'),
            'menu_name'             => __('FAQs', 'faqzin'),
            'name_admin_bar'        => __('FAQ', 'faqzin'),
        );
        
        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'query_var'             => true,
            'rewrite'               => array('slug' => 'faq'),
            'capability_type'       => 'post',
            'has_archive'           => true,
            'hierarchical'          => false,
            'menu_position'         => 25,
            'menu_icon'             => 'dashicons-editor-help',
            'supports'              => array('title', 'editor', 'revisions', 'page-attributes'),
            'show_in_rest'          => true,
        );
        
        register_post_type('faq', $args);
    }
    
    /**
     * Register FAQ Category Taxonomy
     */
    public function register_taxonomy() {
        $labels = array(
            'name'                       => __('FAQ Categories', 'faqzin'),
            'singular_name'              => __('FAQ Category', 'faqzin'),
            'search_items'               => __('Search FAQ Categories', 'faqzin'),
            'popular_items'              => __('Popular FAQ Categories', 'faqzin'),
            'all_items'                  => __('All FAQ Categories', 'faqzin'),
            'edit_item'                  => __('Edit FAQ Category', 'faqzin'),
            'update_item'                => __('Update FAQ Category', 'faqzin'),
            'add_new_item'               => __('Add New FAQ Category', 'faqzin'),
            'new_item_name'              => __('New FAQ Category Name', 'faqzin'),
            'separate_items_with_commas' => __('Separate categories with commas', 'faqzin'),
            'add_or_remove_items'        => __('Add or remove categories', 'faqzin'),
            'choose_from_most_used'      => __('Choose from most used categories', 'faqzin'),
            'menu_name'                  => __('Categories', 'faqzin'),
        );
        
        $args = array(
            'labels'                => $labels,
            'hierarchical'          => true,
            'public'                => true,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'show_in_nav_menus'     => true,
            'show_tagcloud'         => true,
            'show_in_rest'          => true,
            'rewrite'               => array('slug' => 'faq-category'),
        );
        
        register_taxonomy('faq_category', array('faq'), $args);
    }
    
    /**
     * Add schema.org markup to single FAQ pages
     */
    public function add_single_faq_schema() {
        if (!is_singular('faq')) {
            return;
        }
        
        global $post;
        
        if (!$post) {
            return;
        }
        
        $question = get_the_title($post->ID);
        $answer_raw = get_post_field('post_content', $post->ID);
        $answer_text = wp_strip_all_tags($answer_raw);
        
        $schema = array(
            '@context'   => 'https://schema.org',
            '@type'      => array('WebPage', 'QAPage'),
            'mainEntity' => array(
                '@type'          => 'Question',
                'name'           => $question,
                'text'           => $question,
                'answerCount'    => 1,
                'datePublished'  => get_the_date('c', $post->ID),
                'dateModified'   => get_the_modified_date('c', $post->ID),
                'acceptedAnswer' => array(
                    '@type'        => 'Answer',
                    'text'         => $answer_text,
                    'dateCreated'  => get_the_date('c', $post->ID),
                    'upvoteCount'  => 0,
                ),
            ),
        );
        
        echo "\n<!-- FAQzin - Single FAQ Schema -->\n";
        echo '<script type="application/ld+json">';
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo '</script>';
        echo "\n<!-- /FAQzin Schema -->\n";
    }
}

new FAQzin_CPT();