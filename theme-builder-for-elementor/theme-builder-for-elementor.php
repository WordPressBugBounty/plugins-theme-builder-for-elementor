<?php
/**
 * Plugin Name:       Theme Builder For Elementor
 * Plugin URI:        https://blocks-wp.com/theme-builder-for-elementor/
 * Description:       Build the header, footer, single posts and pages, archives and the 404 page of any theme with the free Elementor.
 * Version:           2.0.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Requires Plugins:  elementor
 * Author:            Blocks WP
 * Author URI:        https://blocks-wp.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       theme-builder-for-elementor
 * Elementor tested up to: 4.3.6
 *
 * @package TBFE
 */

defined( 'ABSPATH' ) || exit;

define( 'TBFE_VERSION', '2.0.1' );
define( 'TBFE_FILE', __FILE__ );
define( 'TBFE_PATH', plugin_dir_path( __FILE__ ) );
define( 'TBFE_URL', plugin_dir_url( __FILE__ ) );
define( 'TBFE_MIN_ELEMENTOR', '3.20.0' );

if ( ! defined( 'TBFE_LIBRARY_URL' ) ) {
	// Demo library (TBFE Demo Library plugin on the demos site).
	define( 'TBFE_LIBRARY_URL', 'https://demos.blocks-wp.com/wp-json/tbfe-library/v1' );
}

require_once TBFE_PATH . 'includes/class-autoloader.php';
TBFE\Autoloader::register();

register_activation_hook( __FILE__, array( 'TBFE\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'TBFE\\Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'TBFE\\Plugin', 'instance' ) );
