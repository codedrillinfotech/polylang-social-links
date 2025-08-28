<?php
/**
 * Admin functionality for Polylang Social Links
 *
 * @package Polylang_Social_Links
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class PSL_Admin {

    /**
     * The page slug for the settings page
     *
     * @var string
     */
    private $page_slug = 'polylang-social-links';

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_options_page(
            __('Polylang Social Links Settings', 'polylang-social-links'),
            __('Polylang Social Links', 'polylang-social-links'),
            'manage_options',
            $this->page_slug,
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Register a single setting that will store all languages' data
        register_setting(
            'psl_settings_group',
            'psl_social_links',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_links'),
                'default' => array(),
            )
        );
    }

    /**
     * Sanitize social links
     *
     * @param array $all_links All links data
     * @return array Sanitized array
     */
    public function sanitize_links($new_links) {
        // Get all existing links first
        $existing_links = get_option('psl_social_links', array());
        if (!is_array($existing_links)) {
            $existing_links = array();
        }

        // Get the current language from the form
        $current_lang = isset($_POST['psl_current_lang']) ? sanitize_text_field($_POST['psl_current_lang']) : '';
        
        if (empty($current_lang)) {
            return $existing_links; // Don't save if we don't know the language
        }

        // Get the submitted data
        $submitted_links = array();
        
        // Check for submitted links in the new format
        if (isset($_POST['psl_social_links'][$current_lang]) && is_array($_POST['psl_social_links'][$current_lang])) {
            foreach ($_POST['psl_social_links'][$current_lang] as $key => $link) {
                if (empty(trim($link['url'] ?? '')) && empty(trim($link['svg'] ?? ''))) {
                    continue; // Skip empty rows
                }

                $submitted_links[] = array(
                    'url' => esc_url_raw($link['url'] ?? ''),
                    'svg' => wp_kses($link['svg'] ?? '', array(
                        'svg' => array(
                            'xmlns' => array(),
                            'viewbox' => array(),
                            'width' => array(),
                            'height' => array(),
                            'fill' => array(),
                            'class' => array(),
                        ),
                        'path' => array(
                            'd' => array(),
                            'fill' => array(),
                        ),
                    )),
                    'new_tab' => !empty($link['new_tab']) ? 1 : 0,
                );
            }
        }
        
        // Update only the current language's links while preserving others
        $existing_links[$current_lang] = $submitted_links;
        
        // Clean up any completely empty language entries
        foreach ($existing_links as $lang => $links) {
            if (empty($links)) {
                unset($existing_links[$lang]);
            }
        }
        
        return $existing_links;
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook The current admin page
     */
    public function enqueue_admin_scripts($hook) {
        if ('settings_page_' . $this->page_slug !== $hook) {
            return;
        }

        wp_enqueue_style(
            'psl-admin',
            PSL_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            PSL_VERSION
        );

        wp_enqueue_script(
            'psl-admin',
            PSL_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-sortable'),
            PSL_VERSION,
            true
        );

        wp_localize_script(
            'psl-admin',
            'pslAdmin',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('psl_nonce'),
                'i18n' => array(
                    'confirmRemove' => __('Are you sure you want to remove this social link?', 'polylang-social-links'),
                    'error' => __('An error occurred. Please try again.', 'polylang-social-links'),
                ),
            )
        );
    }

    /**
     * Render the settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get the current language from URL or use default
        $active_tab = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : '';
        $languages = function_exists('pll_languages_list') ? pll_languages_list(array('fields' => '')) : array();
        
        // If no languages found, create a default one
        if (empty($languages)) {
            $languages = array((object)array('slug' => 'en', 'name' => 'English'));
        }
        
        // Set default active tab if not set or invalid
        $language_slugs = wp_list_pluck($languages, 'slug');
        if (empty($active_tab) || !in_array($active_tab, $language_slugs)) {
            $active_tab = $languages[0]->slug;
        }
        
        // Get all social links
        $all_social_links = get_option('psl_social_links', array());
        
        // Get social links for the active language
        $social_links = isset($all_social_links[$active_tab]) ? $all_social_links[$active_tab] : array();
        
        // Ensure we have at least one empty row for new entry
        if (empty($social_links)) {
            $social_links = array(
                array(
                    'url' => '',
                    'svg' => '',
                    'new_tab' => 0,
                )
            );
        } else {
            // Add an empty row for new entry
            $social_links[] = array(
                'url' => '',
                'svg' => '',
                'new_tab' => 0,
            );
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if (!function_exists('pll_languages_list')) : ?>
                <div class="notice notice-warning">
                    <p><?php _e('Polylang is not properly configured. Some features may not work as expected.', 'polylang-social-links'); ?></p>
                </div>
            <?php endif; ?>
            
            <h2 class="nav-tab-wrapper">
                <?php foreach ($languages as $language) : ?>
                    <a href="?page=<?php echo esc_attr($this->page_slug); ?>&lang=<?php echo esc_attr($language->slug); ?>" 
                       class="nav-tab <?php echo $active_tab === $language->slug ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html($language->name); ?>
                    </a>
                <?php endforeach; ?>
            </h2>
            
            <form method="post" action="options.php" class="psl-settings-form">
                <?php
                // Only output settings fields for the current language
                settings_fields('psl_settings_group');
                do_settings_sections('psl_settings_group');
                ?>
                
                <!-- Hidden field to track current language -->
                <input type="hidden" name="psl_current_lang" value="<?php echo esc_attr($active_tab); ?>">
                
                <div class="psl-social-links-container">
                    <div class="psl-social-links-header">
                        <div class="psl-col-icon"><?php _e('Icon (SVG)', 'polylang-social-links'); ?></div>
                        <div class="psl-col-url"><?php _e('URL', 'polylang-social-links'); ?></div>
                        <div class="psl-col-actions"><?php _e('Actions', 'polylang-social-links'); ?></div>
                    </div>
                    
                    <div class="psl-social-links-sortable">
                        <?php foreach ($social_links as $index => $link) : ?>
                            <div class="psl-social-link-row">
                                <div class="psl-col-icon">
                                    <textarea name="psl_social_links[<?php echo esc_attr($active_tab); ?>][<?php echo $index; ?>][svg]" 
                                              class="psl-svg-code" 
                                              rows="3" 
                                              placeholder="<?php esc_attr_e('Paste SVG code here', 'polylang-social-links'); ?>"><?php echo esc_textarea($link['svg'] ?? ''); ?></textarea>
                                    <div class="psl-svg-preview">
                                        <?php 
                                        if (!empty($link['svg'])) {
                                            echo wp_kses($link['svg'], array(
                                                'svg' => array(
                                                    'xmlns' => array(),
                                                    'viewbox' => array(),
                                                    'width' => array(),
                                                    'height' => array(),
                                                    'fill' => array(),
                                                    'class' => array(),
                                                ),
                                                'path' => array(
                                                    'd' => array(),
                                                    'fill' => array(),
                                                ),
                                            ));
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="psl-col-url">
                                    <input type="url" 
                                           name="psl_social_links[<?php echo esc_attr($active_tab); ?>][<?php echo $index; ?>][url]" 
                                           value="<?php echo esc_attr($link['url'] ?? ''); ?>" 
                                           class="regular-text" 
                                           placeholder="https://example.com">
                                    <label class="psl-checkbox-label">
                                        <input type="checkbox" 
                                               name="psl_social_links[<?php echo esc_attr($active_tab); ?>][<?php echo $index; ?>][new_tab]" 
                                               value="1" 
                                               <?php checked(!empty($link['new_tab'])); ?>>
                                        <?php _e('Open in new tab', 'polylang-social-links'); ?>
                                    </label>
                                </div>
                                <div class="psl-col-actions">
                                    <button type="button" class="button psl-remove-link">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                    <span class="psl-sort-handle dashicons dashicons-menu"></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="psl-add-link-container">
                        <button type="button" class="button psl-add-link">
                            <span class="dashicons dashicons-plus"></span>
                            <?php _e('Add Social Link', 'polylang-social-links'); ?>
                        </button>
                    </div>
                </div>
                
                <?php submit_button(__('Save Changes', 'polylang-social-links')); ?>
            </form>
        </div>
        <?php
    }
}
