<?php

/**
 * Shortcode for elementor
 *
 * Based on plugin https://wordpress.org/plugins/anywhere-elementor/
 *
 * @since 1.0.0
 */

namespace Elementor;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class TBFE_Shortcode {

    const SHORTCODE = 'elementor-template';

    public function __construct() {
        $this->add_actions();
    }

    public function admin_columns_headers($defaults) {
        $defaults['shortcode'] = esc_html__('Shortcode', 'tbfe');

        return $defaults;
    }

    public function admin_columns_content($column_name, $post_id) {
        if ('shortcode' === $column_name) {
            // %s = shortcode, %d = post_id
            $shortcode = esc_attr(sprintf('[%s id="%d"]', self::SHORTCODE, $post_id));
            printf('<input class="widefat" type="text" readonly onfocus="this.select()" value="%s" />', $shortcode);
        }
    }

    public function shortcode($attributes = []) {
        if (!class_exists('Elementor\Plugin')) {
            return '';
        }
        if (empty($attributes['id'])) {
            return '';
        }

        $response = Plugin::instance()->frontend->get_builder_content_for_display($attributes['id']);
        return $response;
    }

    public function css_head() {
        
        if (class_exists('\Elementor\Core\Files\CSS\Post')) {

            $options_page = get_option('tbfe_settings_page');
            $options_post = get_option('tbfe_settings_post');
            $options_archive = get_option('tbfe_settings_archive');
            $options_404 = get_option('tbfe_settings_404');

            // Pages
            if (is_page() && !is_page_template() && !tbfe_is_elementor() && isset($options_page) && $options_page !='') {
                foreach ($options_page as $key => $value) {
                    $value = new \Elementor\Core\Files\CSS\Post(absint($value));
                    $value->enqueue();
                }
            }
            // Posts
            if (is_single() && 'post' == get_post_type() && !tbfe_is_elementor() && isset($options_post) && $options_post !='') {
                foreach ($options_post as $key => $value) {
                    $value = new \Elementor\Core\Files\CSS\Post(absint($value));
                    $value->enqueue();
                }
            }
            // Archives
            if ((is_archive() || is_home() ) && get_post_type(get_the_ID()) != 'product' && isset($options_archive) && $options_archive !='' ) {
                foreach ($options_archive as $key => $value) {
                    $value = new \Elementor\Core\Files\CSS\Post(absint($value));
                    $value->enqueue();
                }
            }
            // 404
            if (is_404() && isset($options_404) && $options_404 !='') {
                foreach ($options_404 as $key => $value) {
                    $value = new \Elementor\Core\Files\CSS\Post(absint($value));
                    $value->enqueue();
                }
            }
        }
        if (class_exists('\Elementor\Plugin')) {
            $elementor = \Elementor\Plugin::instance();
            $elementor->frontend->enqueue_styles();
        }
    }

    private function add_actions() {
        add_action('wp_enqueue_scripts', [$this, 'css_head']);
        if (is_admin()) {
            add_action('manage_elementor_library_posts_columns', [$this, 'admin_columns_headers']);
            add_action('manage_elementor_library_posts_custom_column', [$this, 'admin_columns_content'], 10, 2);
        }

        add_shortcode(self::SHORTCODE, [$this, 'shortcode']);
    }

}

new TBFE_Shortcode();
