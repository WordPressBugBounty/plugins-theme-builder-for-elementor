<?php
/**
 * Installs and activates the plugins the demos need (WooCommerce) from the administration of Theme Builder: a plugin
 * that is there is only activated, a missing one is downloaded from WordPress.org first.
 *
 * REST: POST tbfe/v1/install-plugin { slug } → { slug, installed, active }. Needs the capabilities to install and
 * activate plugins.
 *
 * @package TBFE
 */

namespace TBFE\Admin;

defined( 'ABSPATH' ) || exit;

class Plugin_Installer {

	/** Plugins that can be installed here: slug => main file. */
	const PLUGINS = array(
		'woocommerce' => 'woocommerce/woocommerce.php',
	);

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function can_install() {
		return current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' );
	}

	public static function routes() {
		register_rest_route(
			Rest::NS,
			'/install-plugin',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'install' ),
				'permission_callback' => array( __CLASS__, 'can_install' ),
				'args'                => array(
					'slug' => array(
						'type'     => 'string',
						'required' => true,
						'enum'     => array_keys( self::PLUGINS ),
					),
				),
			)
		);
	}

	/**
	 * State of a plugin: installed (its files are there) and active.
	 */
	public static function state( $slug ) {
		$file = self::PLUGINS[ $slug ];
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return array(
			'slug'      => $slug,
			'installed' => file_exists( WP_PLUGIN_DIR . '/' . $file ),
			'active'    => is_plugin_active( $file ),
		);
	}

	public static function install( \WP_REST_Request $request ) {
		$slug  = (string) $request['slug'];
		$file  = self::PLUGINS[ $slug ];
		$state = self::state( $slug );

		if ( ! $state['installed'] ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';

			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'fields' => array( 'sections' => false ),
				)
			);
			if ( is_wp_error( $api ) ) {
				/* translators: %s: error message */
				return new \WP_Error( 'tbfe_install', sprintf( __( 'WordPress.org could not be reached: %s', 'theme-builder-for-elementor' ), $api->get_error_message() ), array( 'status' => 502 ) );
			}
			$skin     = new \WP_Ajax_Upgrader_Skin();
			$upgrader = new \Plugin_Upgrader( $skin );
			$result   = $upgrader->install( $api->download_link );
			if ( is_wp_error( $result ) ) {
				return new \WP_Error( 'tbfe_install', $result->get_error_message(), array( 'status' => 500 ) );
			}
			if ( is_wp_error( $skin->result ) ) {
				return new \WP_Error( 'tbfe_install', $skin->result->get_error_message(), array( 'status' => 500 ) );
			}
			if ( $skin->get_errors()->has_errors() ) {
				return new \WP_Error( 'tbfe_install', $skin->get_error_messages(), array( 'status' => 500 ) );
			}
			if ( ! $result ) {
				// No direct access to the files (FTP credentials needed): the plugin screen of WordPress asks for them.
				return new \WP_Error(
					'tbfe_install_filesystem',
					__( 'WordPress needs the FTP details of your server to install plugins. Install WooCommerce in Plugins › Add New.', 'theme-builder-for-elementor' ),
					array(
						'status' => 500,
						'url'    => admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ),
					)
				);
			}
			wp_clean_plugins_cache();
		}

		if ( ! self::state( $slug )['active'] ) {
			$activated = activate_plugin( $file, '', is_multisite() && is_network_admin() );
			if ( is_wp_error( $activated ) ) {
				return new \WP_Error( 'tbfe_activate', $activated->get_error_message(), array( 'status' => 500 ) );
			}
			// The setup wizard of WooCommerce would take over the next admin page: the user stays in Theme Builder.
			delete_transient( '_wc_activation_redirect' );
		}

		return rest_ensure_response( self::state( $slug ) );
	}
}
