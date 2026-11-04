<?php
/**
 * Administration: the "Theme Builder" menu with the React application (build/admin.js).
 *
 * @package TBFE
 */

namespace TBFE\Admin;

use TBFE\Library\Library;
use TBFE\Templates\Post_Type;

defined( 'ABSPATH' ) || exit;

class Admin {

	const SLUG = 'tbfe';

	/** @var string */
	private static $hook = '';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_init', array( __CLASS__, 'activation_redirect' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( TBFE_FILE ), array( __CLASS__, 'action_links' ) );
		add_filter( 'admin_body_class', array( __CLASS__, 'body_class' ) );
		// The post list of the post type is not used: back to the application.
		add_action( 'load-edit.php', array( __CLASS__, 'redirect_list' ) );
		// "Websites Import" next to "Add New" in the lists of pages and posts, "Import design" next to "Edit with
		// Elementor" when a page or post is edited.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'import_buttons' ) );
		// Without PRO the menu items show what PRO adds (mega menu, icon, badge), locked.
		if ( ! \TBFE\Pro::installed() ) {
			add_action( 'wp_nav_menu_item_custom_fields', array( __CLASS__, 'menu_item_teaser' ), 10, 3 );
		}
	}

	private static function icon() {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="black" d="M3 2h14a1 1 0 0 1 1 1v3H2V3a1 1 0 0 1 1-1Zm-1 6h6v10H3a1 1 0 0 1-1-1V8Zm8 0h8v4h-8V8Zm0 6h8v3a1 1 0 0 1-1 1h-7v-4Z"/></svg>';
		return 'data:image/svg+xml;base64,' . base64_encode( $svg ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- menu icon.
	}

	public static function menu() {
		$title      = __( 'Theme Builder', 'theme-builder-for-elementor' );
		self::$hook = add_menu_page( $title, $title, Post_Type::CAPABILITY, self::SLUG, array( __CLASS__, 'page' ), self::icon(), '20.7' ); // Right below Pages.

		$pages = array(
			''          => __( 'Dashboard', 'theme-builder-for-elementor' ),
			'templates' => __( 'Templates', 'theme-builder-for-elementor' ),
			'websites'  => __( 'Websites Import', 'theme-builder-for-elementor' ),
			'library'   => __( 'Template Library', 'theme-builder-for-elementor' ),
			'settings'  => __( 'Settings', 'theme-builder-for-elementor' ),
		);
		foreach ( $pages as $route => $label ) {
			add_submenu_page( self::SLUG, $label, $label, Post_Type::CAPABILITY, '' === $route ? self::SLUG : self::SLUG . '#/' . $route, '' === $route ? array( __CLASS__, 'page' ) : '__return_null' );
		}
	}

	public static function page() {
		echo '<div id="tbfe-app" class="tbfe-app"><div class="tbfe-app__loading">' . esc_html__( 'Loading Theme Builder…', 'theme-builder-for-elementor' ) . '</div></div>';
	}

	public static function assets( $hook ) {
		if ( $hook !== self::$hook ) {
			return;
		}
		$asset_file = TBFE_PATH . 'build/admin.asset.php';
		$asset      = is_readable( $asset_file ) ? require $asset_file : array(
			'dependencies' => array( 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n' ),
			'version'      => TBFE_VERSION,
		);

		wp_enqueue_script( 'tbfe-admin', TBFE_URL . 'build/admin.js', $asset['dependencies'], $asset['version'], true );
		// The icons of the widgets (Settings › Widgets).
		if ( ! wp_style_is( 'elementor-icons', 'registered' ) && defined( 'ELEMENTOR_ASSETS_URL' ) ) {
			wp_register_style( 'elementor-icons', ELEMENTOR_ASSETS_URL . 'lib/eicons/css/elementor-icons.min.css', array(), defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : false );
		}
		wp_enqueue_style( 'elementor-icons' );
		// Its own version: the version of the asset file follows only the JS.
		wp_enqueue_style( 'tbfe-admin', TBFE_URL . 'build/admin.css', array( 'wp-components' ), \TBFE\Plugin::asset_version( 'build/admin.css' ) );
		wp_set_script_translations( 'tbfe-admin', 'theme-builder-for-elementor', TBFE_PATH . 'languages' );

		wp_add_inline_script(
			'tbfe-admin',
			'window.tbfeAdmin = ' . wp_json_encode(
				array(
					'version'   => TBFE_VERSION,
					'adminUrl'  => admin_url(),
					'siteUrl'   => home_url( '/' ),
					'pluginUrl' => TBFE_URL,
					'docsUrl'   => 'https://blocks-wp.com/docs/theme-builder-for-elementor/',
					'demosUrl'  => 'https://demos.blocks-wp.com/',
					'into'      => self::import_target(),
				)
			) . ';',
			'before'
		);
	}

	public static function body_class( $classes ) {
		$screen = get_current_screen();
		if ( $screen && $screen->id === self::$hook ) {
			$classes .= ' tbfe-admin-page';
		}
		return $classes;
	}

	public static function action_links( $links ) {
		array_unshift( $links, sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=' . self::SLUG ) ), esc_html__( 'Theme Builder', 'theme-builder-for-elementor' ) ) );
		return $links;
	}

	public static function activation_redirect() {
		if ( ! get_option( 'tbfe_activation_redirect' ) ) {
			return;
		}
		delete_option( 'tbfe_activation_redirect' );
		if ( wp_doing_ajax() || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( Post_Type::CAPABILITY ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only.
			return;
		}
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG ) );
		exit;
	}

	/**
	 * Small additions to other admin screens (assets/js/admin-extras.js, assets/css/admin-extras.css). Data of the
	 * calls is merged into window.tbfeExtras.
	 *
	 * @param array $data Data for the script.
	 */
	public static function extras( $data = array() ) {
		$js  = 'assets/js/admin-extras.js';
		$css = 'assets/css/admin-extras.css';
		if ( ! wp_script_is( 'tbfe-admin-extras', 'enqueued' ) ) {
			wp_enqueue_script( 'tbfe-admin-extras', \TBFE\Assets::url( $js ), array(), \TBFE\Assets::version( $js ), true );
			wp_enqueue_style( 'tbfe-admin-extras', \TBFE\Assets::url( $css ), array(), \TBFE\Assets::version( $css ) );
			$data['ajaxUrl'] = admin_url( 'admin-ajax.php' );
		}
		wp_add_inline_script( 'tbfe-admin-extras', 'window.tbfeExtras = Object.assign( window.tbfeExtras || {}, ' . wp_json_encode( $data ) . ' );', 'before' );
	}

	/**
	 * Button "Websites Import" after the "Add New" button of the page and post lists, button "Import design" after the
	 * "Edit with Elementor" button of the block editor and the classic editor (it opens Websites Import, where one demo
	 * page is picked for this page or post).
	 *
	 * @param string $hook Admin screen.
	 */
	public static function import_buttons( $hook ) {
		$screen = get_current_screen();
		if ( ! in_array( $hook, array( 'edit.php', 'post.php', 'post-new.php' ), true ) || ! $screen || ! in_array( $screen->post_type, array( 'page', 'post' ), true ) || ! Rest::can_import_pages() || ! \TBFE\Settings::get( 'library' ) ) {
			return;
		}
		if ( 'edit.php' === $hook ) {
			self::extras(
				array(
					'listButton' => sprintf(
						'<a href="%s" class="page-title-action tbfe-list-import">%s</a>',
						esc_url( admin_url( 'admin.php?page=' . self::SLUG . '#/websites' ) ),
						esc_html__( 'Websites Import', 'theme-builder-for-elementor' )
					),
				)
			);
			return;
		}
		$post = get_post();
		if ( ! $post || is_wp_error( Library::import_target( $post->ID ) ) ) {
			return;
		}
		self::extras(
			array(
				'editorButton' => sprintf(
					'<a href="%s" class="button button-large tbfe-editor-import"><span class="dashicons dashicons-download" aria-hidden="true"></span>%s</a>',
					esc_url( admin_url( 'admin.php?page=' . self::SLUG . '&into=' . $post->ID . '#/websites' ) ),
					esc_html__( 'Import design', 'theme-builder-for-elementor' )
				),
			)
		);
	}

	/**
	 * Page or post from ?into= that gets a demo page in Websites Import (null = a normal import of new pages).
	 */
	private static function import_target() {
		$id = isset( $_GET['into'] ) ? absint( wp_unslash( $_GET['into'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only, checked below.
		if ( ! $id || is_wp_error( Library::import_target( $id ) ) ) {
			return null;
		}
		$title = get_the_title( $id );
		return array(
			'id'    => $id,
			'title' => '' !== $title ? $title : __( '(no title)', 'theme-builder-for-elementor' ),
			'type'  => get_post_type( $id ),
			'back'  => get_edit_post_link( $id, 'raw' ),
		);
	}

	/**
	 * The locked PRO fields of a menu item.
	 */
	public static function menu_item_teaser( $item_id, $item, $depth ) {
		$action = \TBFE\Pro::action();
		printf(
			'<p class="description description-wide" style="clear:both;padding-top:8px;border-top:1px solid #dcdcde;color:#6d28d9">%1$s <a href="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a></p>',
			esc_html( 0 === (int) $depth ? __( '🔒 Mega menu, icon and badge: Theme Builder PRO.', 'theme-builder-for-elementor' ) : __( '🔒 Icon and badge: Theme Builder PRO.', 'theme-builder-for-elementor' ) ),
			esc_url( $action ? $action['url'] : '' ),
			esc_html( $action ? $action['label'] : '' )
		);
	}

	public static function redirect_list() {
		$type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only.
		if ( Post_Type::NAME === $type && ! isset( $_GET['post_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only.
			wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG . '#/templates' ) );
			exit;
		}
	}
}
