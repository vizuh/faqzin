<?php
/**
 * Plugin Name: FAQzin
 * Plugin URI: https://vizuh.com
 * Description: Professional FAQ Manager with Schema, HTML5 Accordion, Divi & Elementor Ready
 * Version: 2.0.0
 * Author: Vizuh
 * Author URI: https://vizuh.com
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: faqzin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

define('FAQZIN_VERSION', '2.0.0');
define('FAQZIN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FAQZIN_PLUGIN_URL', plugin_dir_url(__FILE__));

class FAQzin_Manager {
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    private function includes() {
        require_once FAQZIN_PLUGIN_DIR . 'includes/class-cpt.php';
        require_once FAQZIN_PLUGIN_DIR . 'includes/class-shortcode.php';
        require_once FAQZIN_PLUGIN_DIR . 'includes/class-settings.php';
    }
    
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('faqzin', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function enqueue_assets() {
        // Conditional loading
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'faqzin')) {
            
            wp_enqueue_style('faqzin-styles', FAQZIN_PLUGIN_URL . 'assets/faqzin.css', array(), FAQZIN_VERSION);
            wp_enqueue_script('faqzin-accordion', FAQZIN_PLUGIN_URL . 'assets/faqzin.js', array('jquery'), FAQZIN_VERSION, true);
            
            // Custom CSS inline
            $custom_css = get_option('faqzin_custom_css', '');
            if (!empty($custom_css)) {
                wp_add_inline_style('faqzin-styles', $custom_css);
            }
        }
    }
}

// Initialize plugin
function faqzin_manager() {
    return FAQzin_Manager::instance();
}

faqzin_manager();
