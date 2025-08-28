<?php
/**
 * Social Links Widget
 *
 * @package Polylang_Social_Links
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class PSL_Social_Links_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'psl_social_links_widget',
            __('Polylang Social Links', 'polylang-social-links'),
            array(
                'description' => __('Display social media links with Polylang support', 'polylang-social-links'),
                'customize_selective_refresh' => true,
            )
        );
        

    }
    


    /**
     * Front-end display of widget.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? apply_filters('widget_title', $instance['title']) : '';
        $fill_color = !empty($instance['fill_color']) ? sanitize_hex_color($instance['fill_color']) : '#2f5d41';
        $icon_width = !empty($instance['icon_width']) ? absint($instance['icon_width']) : 18;
        $icon_height = !empty($instance['icon_height']) ? absint($instance['icon_height']) : 18;
        $margin_left = !empty($instance['margin_left']) ? absint($instance['margin_left']) : 5;
        $margin_right = !empty($instance['margin_right']) ? absint($instance['margin_right']) : 5;
        
        $current_lang = function_exists('pll_current_language') ? pll_current_language() : '';
        
        // Get all social links
        $all_social_links = get_option('psl_social_links', array());
        $social_links = array();
        
        // Get links for current language
        if (isset($all_social_links[$current_lang])) {
            $social_links = $all_social_links[$current_lang];
        }
        // If no links for current language, try to get default language links
        elseif (function_exists('pll_default_language')) {
            $default_lang = pll_default_language();
            if ($default_lang !== $current_lang && isset($all_social_links[$default_lang])) {
                $social_links = $all_social_links[$default_lang];
            }
        }

        // If still no links, don't display anything
        if (empty($social_links)) {
            return;
        }

        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        echo '<div class="psl-social-links">';
        
        foreach ($social_links as $link) {
            if (empty($link['url']) || empty($link['svg'])) {
                continue;
            }
            
            // Process SVG to apply custom styles
            $svg = $link['svg'];
            
            // Add or update width and height attributes
            if (preg_match('/<svg([^>]*)>/', $svg, $matches)) {
                $svg_attrs = $matches[1];
                
                // Remove existing width/height/fill attributes
                $svg_attrs = preg_replace('/(width|height|fill)="[^"]*"/', '', $svg_attrs);
                
                // Add new attributes
                $svg_attrs .= sprintf(' width="%d" height="%d"', $icon_width, $icon_height);
                
                // Replace the SVG opening tag
                $svg = str_replace($matches[0], '<svg' . $svg_attrs . '>', $svg);
                
                // Add fill color to all path elements
                $svg = str_replace('<path ', '<path fill="' . esc_attr($fill_color) . '" ', $svg);
            }
            
            $target = !empty($link['new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
            $style = sprintf(
                'style="margin: 0 %1$dpx 0 %2$dpx;"',
                $margin_right,
                $margin_left
            );
            echo sprintf(
                '<a href="%s" class="psl-social-link" %s %s>%s</a>',
                esc_url($link['url']),
                $target,
                $style,
                wp_kses($svg, array(
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
                ))
            );
        }
        
        echo '</div>';
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $fill_color = !empty($instance['fill_color']) ? $instance['fill_color'] : '#2f5d41';
        $icon_width = !empty($instance['icon_width']) ? absint($instance['icon_width']) : 18;
        $icon_height = !empty($instance['icon_height']) ? absint($instance['icon_height']) : 18;
        $margin_left = !empty($instance['margin_left']) ? absint($instance['margin_left']) : 5;
        $margin_right = !empty($instance['margin_right']) ? absint($instance['margin_right']) : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_attr_e('Title:', 'polylang-social-links'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('fill_color')); ?>">
                <?php esc_attr_e('Icon Color:', 'polylang-social-links'); ?>
            </label>
            <input class="widefat" 
                   id="<?php echo esc_attr($this->get_field_id('fill_color')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('fill_color')); ?>" 
                   type="text" 
                   value="<?php echo esc_attr($fill_color); ?>"
                   placeholder="#2f5d41">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('icon_width')); ?>">
                <?php esc_attr_e('Icon Width (px):', 'polylang-social-links'); ?>
            </label>
            <input class="tiny-text" 
                   id="<?php echo esc_attr($this->get_field_id('icon_width')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('icon_width')); ?>" 
                   type="number" 
                   step="1" 
                   min="1" 
                   value="<?php echo esc_attr($icon_width); ?>" 
                   size="3">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('icon_height')); ?>">
                <?php esc_attr_e('Icon Height (px):', 'polylang-social-links'); ?>
            </label>
            <input class="tiny-text" 
                   id="<?php echo esc_attr($this->get_field_id('icon_height')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('icon_height')); ?>" 
                   type="number" 
                   step="1" 
                   min="1" 
                   value="<?php echo esc_attr($icon_height); ?>" 
                   size="3">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('margin_left')); ?>">
                <?php esc_attr_e('Left Margin (px):', 'polylang-social-links'); ?>
            </label>
            <input class="tiny-text" 
                   id="<?php echo esc_attr($this->get_field_id('margin_left')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('margin_left')); ?>" 
                   type="number" 
                   step="1" 
                   min="0" 
                   value="<?php echo esc_attr($margin_left); ?>" 
                   size="3">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('margin_right')); ?>">
                <?php esc_attr_e('Right Margin (px):', 'polylang-social-links'); ?>
            </label>
            <input class="tiny-text" 
                   id="<?php echo esc_attr($this->get_field_id('margin_right')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('margin_right')); ?>" 
                   type="number" 
                   step="1" 
                   min="0" 
                   value="<?php echo esc_attr($margin_right); ?>" 
                   size="3">
        </p>
        
        <p class="description">
            <?php _e('Configure your social links in the Polylang Social Links settings page.', 'polylang-social-links'); ?>
            <br>
            <?php _e('Margins control the spacing between icons.', 'polylang-social-links'); ?>
        </p>
        
        <p class="description">
            <?php _e('Enter color in hex format (e.g., #2f5d41)', 'polylang-social-links'); ?>
        </p>
        <?php 
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['fill_color'] = (!empty($new_instance['fill_color'])) ? sanitize_hex_color($new_instance['fill_color']) : '#2f5d41';
        $instance['icon_width'] = (!empty($new_instance['icon_width'])) ? absint($new_instance['icon_width']) : 18;
        $instance['icon_height'] = (!empty($new_instance['icon_height'])) ? absint($new_instance['icon_height']) : 18;
        $instance['margin_left'] = (!empty($new_instance['margin_left'])) ? absint($new_instance['margin_left']) : 5;
        $instance['margin_right'] = (!empty($new_instance['margin_right'])) ? absint($new_instance['margin_right']) : 5;
        
        return $instance;
    }
}
