<?php
/*
 * Plugin Name: Theme Builder For Elementor
 * Plugin URI: https://blocks-wp.com/theme-builder-for-elementor/
 * Description: Theme Builder For Elementor
 * Version: 1.2.2
 * Author: Blocks WP
 * Author URI: https://blocks-wp.com/
 * License: GPL-2.0+
 * WC requires at least: 3.3.0
 * WC tested up to: 8.3
 * Elementor tested up to: 3.17.0
 */
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('add_action')) {
    die('Nothing to do...');
}

$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'), false);
$plugin_version = $plugin_data['Version'];
// Define TBFE_PRO_CURRENT_VERSION.
if (!defined('TBFE_PRO_CURRENT_VERSION')) {
    define('TBFE_PRO_CURRENT_VERSION', $plugin_version);
}

//plugin constants
define('TBFE_PRO_PATH', plugin_dir_path(__FILE__));
define('TBFE_PRO_PLUGIN_BASE', plugin_basename(__FILE__));
define('TBFE_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));

//add_action('plugins_loaded', 'tbfe_pro_load_textdomain');
function tbfe_pro_load_textdomain() {
    load_plugin_textdomain('tbfe', false, basename(dirname(__FILE__)) . '/languages/');
}

/**
 * Check Elementor plugin
 */
function tbfe_pro_check_for_elementor() {
    // check for plugin using plugin name
    if (in_array('elementor/elementor.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        return true;
    }
    return false;
}

/**
 * Check Elementor PRO plugin
 */
function tbfe_pro_check_for_elementor_pro() {
    // check for plugin using plugin name
    if (in_array('elementor-pro/elementor-pro.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        return true;
    }
    return false;
}

/**
 * Register Elementor features
 */
if (tbfe_pro_check_for_elementor()) {
    include_once( TBFE_PRO_PATH . 'lib/elementor/widgets.php' );
    include_once( TBFE_PRO_PATH . 'lib/elementor/shortcode.php' );
    include_once( TBFE_PRO_PATH . 'lib/elementor/sections/module.php' );
    include_once( TBFE_PRO_PATH . 'lib/elementor/options.php' );
    include_once( TBFE_PRO_PATH . 'lib/elementor/installers.php' );
    include_once( TBFE_PRO_PATH . 'lib/metabox.php' );
}
include_once( TBFE_PRO_PATH . 'lib/templates/template-builder.php' );

/**
 * Check if is edited by Elementor
 */
function tbfe_is_elementor() {
    global $post;
    return \Elementor\Plugin::$instance->documents->get( $post->ID )->is_built_with_elementor();
}

/**
 * Register the Sidebar(s)
 */
function tbfe_widgets_init() {
    register_sidebar(
            array(
                'name' => esc_html__('Sidebar #1', 'tbfe'),
                'id' => 'tbfe-sidebar-1',
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widgettitle"><h3>',
                'after_title' => '</h3></div>',
            )
    );
    register_sidebar(
            array(
                'name' => esc_html__('Sidebar #2', 'tbfe'),
                'id' => 'tbfe-sidebar-2',
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widgettitle"><h3>',
                'after_title' => '</h3></div>',
            )
    );
    register_sidebar(
            array(
                'name' => esc_html__('Sidebar #3', 'tbfe'),
                'id' => 'tbfe-sidebar-3',
                'before_widget' => '<div id="%1$s" class="widget %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<div class="widgettitle"><h3>',
                'after_title' => '</h3></div>',
            )
    );
}

add_action('widgets_init', 'tbfe_widgets_init');

/**
 * Add Metadata on plugin activation.
 */
function tbfe_activate() {
    add_site_option('tbfe_active_time', time());
    add_option('tbfe_do_activation_redirect', true);
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'tbfe_activate');

add_action('admin_init', 'tbfe_plugin_redirect');

/**
 * Redirect after plugin activation
 */
function tbfe_plugin_redirect() {
    if (get_option('tbfe_do_activation_redirect', false)) {
        delete_option('tbfe_do_activation_redirect');
        if (!is_network_admin() || !isset($_GET['activate-multi'])) {
            wp_redirect('admin.php?page=tbfe&tab=display_welcome_tab');
        }
    }
}

if (!tbfe_pro_check_for_elementor()) {
    add_action('admin_notices', 'tbfe_plugin_requirements');
}

function tbfe_plugin_requirements() {
    ?>
    <div class="error">
        <p><?php echo __('Theme Builder For Elementor is enabled but not effective. It requires Elementor in order to work.', 'tbfe') ?></p>
    </div>
    <?php
}

function tbfe_scripts() {
    wp_enqueue_style('bootstrap', plugin_dir_url(__FILE__) . 'assets/css/bootstrap.css', array(), TBFE_PRO_CURRENT_VERSION);
}

add_action('wp_enqueue_scripts', 'tbfe_scripts', 9999);