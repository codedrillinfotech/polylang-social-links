<?php
/**
 * Plugin Name: Polylang Social Links
 * Plugin URI: https://yourwebsite.com/polylang-social-links
 * Description: A custom widget to display social links with Polylang support
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: polylang-social-links
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package Polylang_Social_Links
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PSL_VERSION', '1.0.0');
define('PSL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PSL_PLUGIN_URL', plugin_dir_url(__FILE__));

// Check if Polylang is active
if (!in_array('polylang/polylang.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', 'psl_polylang_notice');
    return;
}

// Admin notice if Polylang is not active
function psl_polylang_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('Polylang Social Links requires Polylang to be installed and activated.', 'polylang-social-links'); ?></p>
    </div>
    <?php
}

// Include required files
require_once PSL_PLUGIN_DIR . 'includes/class-psl-widget.php';
require_once PSL_PLUGIN_DIR . 'includes/class-psl-admin.php';

// Register the widget
function psl_register_widget() {
    register_widget('PSL_Social_Links_Widget');
}
add_action('widgets_init', 'psl_register_widget');

// Initialize the plugin
function psl_init() {
    // Register widget
    add_action('widgets_init', function() {
        register_widget('PSL_Social_Links_Widget');
    });
    
    // Initialize admin
    if (is_admin()) {
        new PSL_Admin();
    }
    
    // Register shortcode
    add_shortcode('polylang_social_links', 'psl_social_links_shortcode');
}
add_action('plugins_loaded', 'psl_init');

/**
 * Shortcode to display social links
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function psl_social_links_shortcode($atts) {
    $atts = shortcode_atts(array(
        'title' => '',
        'class' => 'psl-social-links',
    ), $atts, 'polylang_social_links');
    
    // Get current language
    $current_lang = function_exists('pll_current_language') ? pll_current_language() : '';
    $default_lang = function_exists('pll_default_language') ? pll_default_language() : '';
    
    // Get all social links
    $all_social_links = get_option('psl_social_links', array());
    $social_links = array();
    
    // Get links for current language
    if (!empty($current_lang) && isset($all_social_links[$current_lang])) {
        $social_links = $all_social_links[$current_lang];
    }
    // Fallback to default language if no links for current language
    elseif (!empty($default_lang) && isset($all_social_links[$default_lang])) {
        $social_links = $all_social_links[$default_lang];
    }
    
    // If still no links, return empty
    if (empty($social_links)) {
        return '';
    }
    
    // Start output buffering
    ob_start();
    
    // Output the social links
    echo '<div class="' . esc_attr($atts['class']) . '">';
    
    // Add title if provided
    if (!empty($atts['title'])) {
        echo '<h3 class="psl-social-links-title">' . esc_html($atts['title']) . '</h3>';
    }
    
    echo '<div class="psl-social-links-container">';
    
    foreach ($social_links as $link) {
        if (empty($link['url']) || empty($link['svg'])) {
            continue;
        }
        
        $target = !empty($link['new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
        echo sprintf(
            '<a href="%s" class="psl-social-link"%s>%s</a>',
            esc_url($link['url']),
            $target,
            $link['svg'] // Already sanitized when saved
        );
    }
    
    echo '</div></div>';
    
    // Return the buffered content
    return ob_get_clean();
}

// Load text domain
function psl_load_textdomain() {
    load_plugin_textdomain('polylang-social-links', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'psl_load_textdomain');
