<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Custom widgets for Elementor
 *
 * This class handles custom widgets for Elementor
 *
 * @since 1.0.0
 */
final class tbfe_pro_Elementor_Extension {

    private static $_instance = null;

    public static function instance() {

        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Registers widgets in Elementor
     *
     *
     * @since 1.0.0
     * @access public
     */
    public function register_widgets() {
        /** @noinspection PhpIncludeInspection */

        require_once TBFE_PRO_PATH . '/lib/elementor/widgets/posts-pages/blog-feed-content.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new TBFE_Pro_Blog_Feed_Content());
        require_once TBFE_PRO_PATH . '/lib/elementor/widgets/posts-pages/blog-feed-date.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new TBFE_Pro_Blog_Feed_Date());
        require_once TBFE_PRO_PATH . '/lib/elementor/widgets/posts-pages/blog-feed-title.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new TBFE_Pro_Blog_Feed_Title());
        require_once TBFE_PRO_PATH . '/lib/elementor/widgets/posts-pages/blog-feed-read-more.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new TBFE_Pro_Blog_Feed_Read_More());
        require_once TBFE_PRO_PATH . '/lib/elementor/widgets/posts-pages/blog-feed-image.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new TBFE_Pro_Blog_Feed_Image());
        require_once TBFE_PRO_PATH . '/lib/elementor/widgets/posts-pages/blog-feed-author.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new TBFE_Pro_Blog_Feed_Author());
        require_once TBFE_PRO_PATH . '/lib/elementor/widgets/posts-pages/blog-feed-categories.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new TBFE_Pro_Blog_Feed_Categories());
        require_once TBFE_PRO_PATH . '/lib/elementor/widgets/posts-pages/blog-feed-tags.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new TBFE_Pro_Blog_Feed_Tags());
        require_once TBFE_PRO_PATH . '/lib/elementor/widgets/posts-pages/blog-feed-comments.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new TBFE_Pro_Blog_Feed_Comments());
        require_once TBFE_PRO_PATH . '/lib/elementor/widgets/posts-pages/blog-feed-sidebar.php';
        \Elementor\Plugin::instance()->widgets_manager->register(new TBFE_Pro_Blog_Feed_Sidebar());
    }

    /**
     * Registers widgets scripts
     *
     *
     * @since 1.0.0
     * @access public
     */
    public function widget_scripts() {
    }

    /**
     * Enqueue widgets scripts in preview mode, as later calls in widgets render will not work,
     * as it happens in admin env
     *
     *
     * @since 1.0.0
     * @access public
     */
    public function widget_scripts_preview() {

    }

    /**
     * Registers widgets styles
     *
     *
     * @since 1.0.0
     * @access public
     */
    public function widget_styles() {

    }

    public function widget_styles_preview() {

    }

    public function add_elementor_widget_categories($elements_manager) {
        $elements_manager->add_category(
                'blog-layout',
                [
                    'title' => __('Blog & Archive Layout', 'tbfe'),
                    'icon' => 'fa fa-plug',
                ]
        );
    }

    /**
     * Widget constructor.
     *
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct() {
        add_action('elementor/widgets/widgets_registered', [$this, 'register_widgets']);
        // Register Widget Styles
        add_action('elementor/frontend/after_register_styles', [$this, 'widget_styles']);
        // Register Widget Scripts
        add_action('elementor/frontend/after_register_scripts', [$this, 'widget_scripts']);
        // Enqueue ALL Widgets Scripts for preview
        add_action('elementor/preview/enqueue_scripts', [$this, 'widget_scripts_preview']);

        add_action('elementor/elements/categories_registered', [$this, 'add_elementor_widget_categories']);

        add_action('elementor/preview/enqueue_styles', [$this, 'widget_styles_preview']);
    }

}

tbfe_pro_Elementor_Extension::instance();
