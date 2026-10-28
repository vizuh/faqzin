<?php
/**
 * Settings Page Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class FAQzin_Settings {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        // The FAQ URLs come and go with headless mode: rebuild the rewrite rules on the next load.
        add_action('update_option_faqzin_headless', array($this, 'reset_rewrite_rules'));
        add_action('add_option_faqzin_headless', array($this, 'reset_rewrite_rules'));
    }
    
    /**
     * Add settings page to WordPress admin
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=faq',
            __('Settings', 'faqzin'),
            __('Settings', 'faqzin'),
            'manage_options',
            'faqzin-settings',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting(
            'faqzin_settings_group',
            'faqzin_custom_css',
            array(
                'type'              => 'string',
                'sanitize_callback' => array($this, 'sanitize_css'),
                'default'           => '',
            )
        );
        register_setting(
            'faqzin_settings_group',
            'faqzin_headless',
            array(
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => false,
            )
        );
    }
    
    public function reset_rewrite_rules() {
        delete_option('rewrite_rules');
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('FAQzin Settings', 'faqzin'); ?></h1>
            
            <div style="background: #fff; padding: 20px; margin-top: 20px; border-left: 4px solid #007bff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2><?php esc_html_e('Quick Guide', 'faqzin'); ?></h2>
                <p><strong>Shortcode:</strong> <code>[faqzin]</code></p>
                <p><strong>Parameters:</strong></p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><code>category="slug"</code> - Filter by category</li>
                    <li><code>limit="5"</code> - Limit number of FAQs</li>
                    <li><code>schema="yes"</code> - Enable/Disable Schema (default: yes)</li>
                </ul>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('faqzin_settings_group');
                do_settings_sections('faqzin-settings');
                ?>
                
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Headless mode', 'faqzin'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="faqzin_headless" value="1" <?php checked(faqzin_is_headless()); ?>>
                                <?php esc_html_e('Another site shows the FAQs (over WPGraphQL)', 'faqzin'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('WordPress then publishes no FAQ pages, category archives or FAQ schema; the shortcode still works.', 'faqzin'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Custom CSS', 'faqzin'); ?></th>
                        <td>
                            <textarea 
                                name="faqzin_custom_css" 
                                rows="15" 
                                cols="50" 
                                class="large-text code"
                                placeholder="/* Add your custom CSS here */"
                            ><?php echo esc_textarea(get_option('faqzin_custom_css')); ?></textarea>
                            <p class="description"><?php esc_html_e('Add custom CSS to style your FAQs. This will be loaded inline.', 'faqzin'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Sanitize CSS input
     */
    public function sanitize_css($css) {
        $css = wp_unslash($css);
        $css = wp_strip_all_tags($css, true);
        return trim($css);
    }
}

// Initialize
new FAQzin_Settings();
