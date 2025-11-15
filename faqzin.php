<?php
/**
 * Plugin Name: faqzin
 * Description: Lightweight FAQ plugin with a simple shortcode.
 * Version: 0.1.0
 * Author: Your Org Name
 * Author URI: https://yourorg.com
 * Plugin URI: https://github.com/your-org/faqzin
 * License: GPL-2.0-or-later
 * Text Domain: faqzin
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FAQZIN_VER', '0.1.0');
define('FAQZIN_URL', plugin_dir_url(__FILE__));
define('FAQZIN_PATH', plugin_dir_path(__FILE__));

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('faqzin', FAQZIN_URL . 'assets/faqzin.css', [], FAQZIN_VER);
    wp_enqueue_script('faqzin', FAQZIN_URL . 'assets/faqzin.js', ['jquery'], FAQZIN_VER, true);
});

add_shortcode('faqzin', function ($atts = [], $content = null) {
    $content = $content ?: '';

    ob_start();
    ?>
    <div class="faqzin" role="list">
        <?php echo do_shortcode($content); ?>
    </div>
    <?php
    return trim(ob_get_clean());
});
