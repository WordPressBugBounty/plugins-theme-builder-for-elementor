<?php
/**
 * Plugin bootstrap: wires the modules together.
 *
 * @package TBFE
 */

namespace TBFE;

use TBFE\Templates\Post_Type;
use TBFE\Migration\Migrator;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	/** @var Plugin|null */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Translations come from translate.wordpress.org; WordPress loads them by the text domain itself.
		Post_Type::init();
		Migrator::init();
		Settings::init();
		Library\Previews::init();
		Library\News::init();
		Legacy::init();

		if ( ! self::elementor_ready() ) {
			add_action( 'admin_notices', array( __CLASS__, 'requirements_notice' ) );
			return;
		}

		add_action( 'init', array( __CLASS__, 'maybe_clear_css' ), 20 );

		Render\Renderer::init();
		Render\Theme_Support::init();
		Elementor\Integration::init();
		Shortcodes::init();

		if ( is_admin() ) {
			Admin\Admin::init();
			Admin\Metabox::init();
		}
		Admin\Rest::init();
		Admin\Plugin_Installer::init();
	}

	/**
	 * Elementor is active and new enough.
	 */
	public static function elementor_ready() {
		return did_action( 'elementor/loaded' ) && defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, TBFE_MIN_ELEMENTOR, '>=' );
	}

	public static function requirements_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		if ( ! did_action( 'elementor/loaded' ) ) {
			$message = __( 'Theme Builder For Elementor needs the Elementor plugin. Please install and activate Elementor.', 'theme-builder-for-elementor' );
		} else {
			/* translators: %s: minimal Elementor version */
			$message = sprintf( __( 'Theme Builder For Elementor needs Elementor %s or newer. Please update Elementor.', 'theme-builder-for-elementor' ), TBFE_MIN_ELEMENTOR );
		}
		printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html( $message ) );
	}

	/**
	 * Version of an asset file: plugin version + time of the last change (browsers load changed files again).
	 *
	 * @param string $file Path relative to the plugin folder.
	 */
	public static function asset_version( $file ) {
		$time = @filemtime( TBFE_PATH . $file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- a missing file only loses the suffix.
		return $time ? TBFE_VERSION . '.' . $time : TBFE_VERSION;
	}

	/**
	 * Escapes markup of the widgets late: the tags of post content plus responsive images (srcset, sizes), inline SVG
	 * (icons of Elementor and of the widgets) and the fields of forms (the password form of WordPress).
	 *
	 * @param string $html Markup.
	 * @return string
	 */
	public static function kses( $html ) {
		static $tags = null;
		if ( null === $tags ) {
			$tags        = wp_kses_allowed_html( 'post' );
			$tags['img'] = array_merge( $tags['img'], array_fill_keys( array( 'srcset', 'sizes', 'decoding', 'fetchpriority' ), true ) );
			$svg         = array_fill_keys( array( 'class', 'id', 'style', 'role', 'aria-hidden', 'aria-label', 'focusable', 'xmlns', 'xmlns:xlink', 'viewbox', 'width', 'height', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'stroke-dasharray', 'stroke-dashoffset', 'fill-rule', 'clip-rule', 'opacity', 'fill-opacity', 'transform', 'preserveaspectratio', 'pathlength', 'd', 'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'points', 'href', 'xlink:href', 'offset', 'stop-color', 'stop-opacity', 'gradientunits', 'gradienttransform' ), true );
			foreach ( array( 'svg', 'g', 'path', 'circle', 'ellipse', 'rect', 'line', 'polyline', 'polygon', 'use', 'defs', 'symbol', 'title', 'lineargradient', 'radialgradient', 'stop', 'clippath', 'mask' ) as $tag ) {
				$tags[ $tag ] = $svg;
			}
			$field       = array_fill_keys( array( 'class', 'id', 'name', 'type', 'value', 'placeholder', 'size', 'maxlength', 'required', 'autocomplete', 'for', 'action', 'method', 'aria-label', 'aria-describedby', 'spellcheck' ), true );
			foreach ( array( 'form', 'input', 'label', 'button' ) as $tag ) {
				$tags[ $tag ] = array_merge( isset( $tags[ $tag ] ) ? $tags[ $tag ] : array(), $field );
			}
		}
		return wp_kses( (string) $html, $tags );
	}

	/**
	 * After an update of the plugin the CSS of Elementor is generated again (the widgets may have new selectors).
	 */
	public static function maybe_clear_css() {
		if ( get_option( 'tbfe_version' ) === TBFE_VERSION ) {
			return;
		}
		update_option( 'tbfe_version', TBFE_VERSION );
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	public static function activate() {
		Post_Type::register();
		update_option( 'tbfe_activation_redirect', 1, false );
		flush_rewrite_rules( false );
	}

	public static function deactivate() {
		wp_clear_scheduled_hook( Library\News::REFRESH );
		flush_rewrite_rules( false );
	}
}
