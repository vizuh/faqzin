<?php
/**
 * Plugin Name: faqzin
 * Description: Lightweight FAQ plugin with a simple shortcode.
 * Version: 0.1.1
 * Author: Vizuh
 * Author URI: https://vizuh.com
 * Plugin URI: https://github.com/vizuh/faqzin
 * License: GPL-2.0-or-later
 * Text Domain: faqzin
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FAQZIN_VER', '0.1.1');
define('FAQZIN_FILE', __FILE__);
define('FAQZIN_URL', plugin_dir_url(__FILE__));
define('FAQZIN_PATH', plugin_dir_path(__FILE__));

require_once FAQZIN_PATH . 'includes/class-faqzin.php';

new FAQZIN();
