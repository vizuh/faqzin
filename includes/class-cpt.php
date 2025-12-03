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
        add_action('wp_head', array($this, 'add_single_faq_css'), 999);
        
        // Add Order column and Quick Edit
        add_filter('manage_faq_posts_columns', array($this, 'add_order_column'));
        add_action('manage_faq_posts_custom_column', array($this, 'show_order_column_editable'), 10, 2);
        add_filter('manage_edit-faq_sortable_columns', array($this, 'make_order_column_sortable'));
        add_action('pre_get_posts', array($this, 'order_by_menu_order'));
        
        // Add Quick Edit field
        add_action('quick_edit_custom_box', array($this, 'add_quick_edit_order'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // AJAX save order
        add_action('wp_ajax_faqzin_save_order', array($this, 'ajax_save_order'));
        
        // Add Re-Order submenu page
        add_action('admin_menu', array($this, 'add_reorder_submenu'));
    }
    
    /**
     * Add Re-Order submenu page
     */
    public function add_reorder_submenu() {
        add_submenu_page(
            'edit.php?post_type=faq',
            __('Re-Order FAQs', 'faqzin'),
            __('Re-Order', 'faqzin'),
            'edit_posts',
            'faqzin-reorder',
            array($this, 'render_reorder_page')
        );
    }
    
    /**
     * Render Re-Order page with drag & drop
     */
    public function render_reorder_page() {
        // Save order if submitted
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
                echo '<div class="notice notice-success"><p>' . __('Order saved successfully!', 'faqzin') . '</p></div>';
            }
        }
        
        // Get all FAQs ordered by current menu_order
        $faqs = get_posts(array(
            'post_type' => 'faq',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ));
        ?>
        <div class="wrap">
            <h1><?php _e('Re-Order FAQs', 'faqzin'); ?></h1>
            <p><?php _e('Drag and drop FAQs to reorder them. Click "Save Order" when done.', 'faqzin'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('faqzin_save_order', 'faqzin_order_nonce'); ?>
                
                <ul id="faqzin-sortable" style="max-width: 800px;">
                    <?php foreach ($faqs as $faq) : ?>
                        <li style="padding: 15px; margin: 5px 0; background: #fff; border: 1px solid #ccc; cursor: move; list-style: none;">
                            <input type="hidden" name="faq_order[]" value="<?php echo esc_attr($faq->ID); ?>">
                            <span style="display: inline-block; width: 40px; font-weight: bold; color: #666;">
                                <?php echo esc_html($faq->menu_order); ?>
                            </span>
                            <span class="dashicons dashicons-menu" style="margin-right: 10px; color: #666;"></span>
                            <strong><?php echo esc_html($faq->post_title); ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary button-large">
                        <?php _e('Save Order', 'faqzin'); ?>
                    </button>
                </p>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#faqzin-sortable').sortable({
                placeholder: 'ui-state-highlight',
                update: function(event, ui) {
                    // Update order numbers display
                    $('#faqzin-sortable li').each(function(index) {
                        $(this).find('span:first').text(index);
                    });
                }
            });
        });
        </script>
        
        <style>
        #faqzin-sortable {
            padding: 0;
        }
        #faqzin-sortable li:hover {
            background: #f0f0f1 !important;
        }
        .ui-state-highlight {
            height: 60px;
            background: #fffbcc;
            border: 2px dashed #999;
        }
        </style>
        <?php
    }
    
    /**
     * Enqueue admin scripts for inline editing
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        if ($post_type === 'faq') {
            // jQuery UI for sortable
            wp_enqueue_script('jquery-ui-sortable');
            
            // Inline edit script
            if ($hook === 'edit.php') {
                wp_enqueue_script('faqzin-inline-edit', plugin_dir_url(dirname(__FILE__)) . 'assets/inline-edit.js', array('jquery'), '1.0', true);
                wp_localize_script('faqzin-inline-edit', 'faqzinAjax', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('faqzin_inline_edit')
                ));
            }
        }
    }
    
    /**
     * AJAX handler to save order
     */
    public function ajax_save_order() {
        check_ajax_referer('faqzin_inline_edit', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }
        
        $post_id = intval($_POST['post_id']);
        $order = intval($_POST['order']);
        
        wp_update_post(array(
            'ID' => $post_id,
            'menu_order' => $order
        ));
        
        wp_send_json_success(array('order' => $order));
    }
    
    /**
     * Add Order column to FAQ list
     */
    public function add_order_column($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $value) {
            if ($key === 'title') {
                $new_columns['menu_order'] = __('Order', 'faqzin');
            }
            $new_columns[$key] = $value;
        }
        
        return $new_columns;
    }
    
    /**
     * Display Order value in column with inline editing
     */
    public function show_order_column_editable($column, $post_id) {
        if ($column === 'menu_order') {
            $order = get_post_field('menu_order', $post_id);
            ?>
            <div class="faqzin-order-wrapper" style="display: flex; align-items: center; gap: 5px;">
                <input 
                    type="number" 
                    class="faqzin-order-input" 
                    data-post-id="<?php echo esc_attr($post_id); ?>"
                    value="<?php echo esc_attr($order); ?>" 
                    style="width: 60px; text-align: center;"
                    min="0"
                >
                <span class="faqzin-order-status" style="color: #999; display: none;">
                    <span class="dashicons dashicons-yes" style="color: green;"></span>
                </span>
            </div>
            <?php
        }
    }
    
    /**
     * Add Quick Edit field for Order
     */
    public function add_quick_edit_order($column_name, $post_type) {
        if ($column_name !== 'menu_order' || $post_type !== 'faq') {
            return;
        }
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title"><?php _e('Order', 'faqzin'); ?></span>
                    <span class="input-text-wrap">
                        <input type="number" name="menu_order" class="ptitle" value="" min="0">
                    </span>
                </label>
            </div>
        </fieldset>
        <?php
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
            // If no orderby is set, order by menu_order
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
     * Add aggressive CSS to hide author/date on single FAQ pages
     */
    public function add_single_faq_css() {
        if (!is_singular('faq')) {
            return;
        }
        ?>
        <style type="text/css">
        /* Hide author and date on single FAQ pages - AGGRESSIVE */
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
        </style>
        <?php
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
            'show_in_rest'          => true, // Gutenberg/Elementor support
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
            'show_in_rest'          => true, // REST API support
            'rewrite'               => array('slug' => 'faq-category'),
        );
        
        register_taxonomy('faq_category', array('faq'), $args);
    }
    
    /**
     * Add schema.org markup to single FAQ pages
     */
    public function add_single_faq_schema() {
        // Only on single FAQ pages
        if (!is_singular('faq')) {
            return;
        }
        
        global $post;
        
        if (!$post) {
            return;
        }
        
        // Get FAQ data
        $question = get_the_title($post->ID);
        $answer_raw = get_post_field('post_content', $post->ID);
        $answer_text = wp_strip_all_tags($answer_raw);
        
        // Build QAPage schema
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
        
        // Output schema
        echo "\n<!-- FAQzin - Single FAQ Schema -->\n";
        echo '<script type="application/ld+json">';
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo '</script>';
        echo "\n<!-- /FAQzin Schema -->\n";
    }
}

// Initialize
new FAQzin_CPT();