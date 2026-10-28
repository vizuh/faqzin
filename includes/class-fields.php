<?php
/**
 * Extra FAQ fields: "Featured" on each FAQ and an Order number on each FAQ category.
 * Both are read by the GraphQL fields (class-graphql.php); the shortcode does not use them.
 */

if (!defined('ABSPATH')) {
    exit;
}

class FAQzin_Fields {

    const FEATURED_META = '_faqzin_featured';
    const ORDER_META    = 'faqzin_order';

    public function __construct() {
        add_action('init', array($this, 'register_meta'));

        add_action('add_meta_boxes_faq', array($this, 'add_featured_box'));
        add_action('save_post_faq', array($this, 'save_featured'), 10, 2);
        add_filter('manage_faq_posts_columns', array($this, 'add_featured_column'));
        add_action('manage_faq_posts_custom_column', array($this, 'show_featured_column'), 10, 2);

        add_action('faq_category_add_form_fields', array($this, 'render_order_add_field'));
        add_action('faq_category_edit_form_fields', array($this, 'render_order_edit_field'));
        add_action('created_faq_category', array($this, 'save_order'));
        add_action('edited_faq_category', array($this, 'save_order'));
        add_filter('manage_edit-faq_category_columns', array($this, 'add_order_column'));
        add_filter('manage_faq_category_custom_column', array($this, 'show_order_column'), 10, 3);
    }

    public function register_meta() {
        register_post_meta('faq', self::FEATURED_META, array(
            'type'          => 'boolean',
            'single'        => true,
            'default'       => false,
            'show_in_rest'  => true,
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ));
        register_term_meta('faq_category', self::ORDER_META, array(
            'type'         => 'integer',
            'single'       => true,
            'show_in_rest' => true,
        ));
    }

    /**
     * Whether the FAQ is marked Featured.
     */
    public static function is_featured($post_id) {
        return (bool) get_post_meta($post_id, self::FEATURED_META, true);
    }

    /**
     * The category's Order, or null when it has none.
     */
    public static function category_order($term_id) {
        $order = get_term_meta($term_id, self::ORDER_META, true);
        return '' === $order ? null : (int) $order;
    }

    public function add_featured_box() {
        add_meta_box('faqzin-featured', __('Featured', 'faqzin'), array($this, 'render_featured_box'), 'faq', 'side');
    }

    public function render_featured_box($post) {
        wp_nonce_field('faqzin_featured', 'faqzin_featured_nonce');
        ?>
        <label>
            <input type="checkbox" name="faqzin_featured" value="1" <?php checked(self::is_featured($post->ID)); ?>>
            <?php esc_html_e('Featured FAQ', 'faqzin'); ?>
        </label>
        <p class="description"><?php esc_html_e('Highlight this FAQ, for example in a home page FAQ block.', 'faqzin'); ?></p>
        <?php
    }

    public function save_featured($post_id, $post) {
        if (!isset($_POST['faqzin_featured_nonce']) || !wp_verify_nonce(sanitize_key($_POST['faqzin_featured_nonce']), 'faqzin_featured')) {
            return;
        }
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) {
            return;
        }
        update_post_meta($post_id, self::FEATURED_META, empty($_POST['faqzin_featured']) ? 0 : 1);
    }

    public function add_featured_column($columns) {
        $columns['faqzin_featured'] = __('Featured', 'faqzin');
        return $columns;
    }

    public function show_featured_column($column, $post_id) {
        if ('faqzin_featured' === $column && self::is_featured($post_id)) {
            echo '<span class="dashicons dashicons-star-filled" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__('Featured', 'faqzin') . '</span>';
        }
    }

    public function render_order_add_field() {
        wp_nonce_field('faqzin_category_order', 'faqzin_order_nonce');
        ?>
        <div class="form-field">
            <label for="faqzin-order"><?php esc_html_e('Order', 'faqzin'); ?></label>
            <input type="number" name="faqzin_order" id="faqzin-order" min="0" step="1">
            <p><?php esc_html_e('Position of this category in FAQ lists, lowest first. Categories without an order go last.', 'faqzin'); ?></p>
        </div>
        <?php
    }

    public function render_order_edit_field($term) {
        $order = self::category_order($term->term_id);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="faqzin-order"><?php esc_html_e('Order', 'faqzin'); ?></label></th>
            <td>
                <?php wp_nonce_field('faqzin_category_order', 'faqzin_order_nonce'); ?>
                <input type="number" name="faqzin_order" id="faqzin-order" min="0" step="1" value="<?php echo null === $order ? '' : esc_attr($order); ?>">
                <p class="description"><?php esc_html_e('Position of this category in FAQ lists, lowest first. Categories without an order go last.', 'faqzin'); ?></p>
            </td>
        </tr>
        <?php
    }

    public function save_order($term_id) {
        if (!isset($_POST['faqzin_order_nonce']) || !wp_verify_nonce(sanitize_key($_POST['faqzin_order_nonce']), 'faqzin_category_order')) {
            return;
        }
        if (!current_user_can('manage_categories') || !isset($_POST['faqzin_order'])) {
            return;
        }
        $order = trim(sanitize_text_field(wp_unslash($_POST['faqzin_order'])));
        if ('' === $order) {
            delete_term_meta($term_id, self::ORDER_META);
        } else {
            update_term_meta($term_id, self::ORDER_META, max(0, (int) $order));
        }
    }

    public function add_order_column($columns) {
        $columns['faqzin_order'] = __('Order', 'faqzin');
        return $columns;
    }

    public function show_order_column($content, $column, $term_id) {
        if ('faqzin_order' === $column) {
            $order = self::category_order($term_id);
            return null === $order ? '—' : (string) $order;
        }
        return $content;
    }
}

new FAQzin_Fields();
