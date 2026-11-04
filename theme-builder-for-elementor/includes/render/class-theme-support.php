<?php
/**
 * Replaces the header and footer of any theme.
 *
 * Classic themes: on get_header / get_footer the plugin prints its own header (with wp_head) or footer (with
 * wp_footer) and loads the theme's header.php / footer.php into a buffer that is thrown away.
 * Block themes: the header and footer template parts are replaced, and the plugin's page templates print the whole
 * document themselves.
 *
 * @package TBFE
 */

namespace TBFE\Render;

use TBFE\Conditions\Resolver;

defined( 'ABSPATH' ) || exit;

class Theme_Support {

	public static function init() {
		add_action( 'get_header', array( __CLASS__, 'replace_header' ), 99 );
		add_action( 'get_footer', array( __CLASS__, 'replace_footer' ), 99 );
		add_filter( 'render_block_core/template-part', array( __CLASS__, 'replace_template_part' ), 10, 2 );
	}

	public static function is_block_theme() {
		return function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	}

	public static function replace_header( $name = null ) {
		if ( self::is_block_theme() || ! Resolver::get( 'header' ) ) {
			return;
		}
		require TBFE_PATH . 'templates/parts/header.php';

		$templates = array();
		if ( '' !== (string) $name ) {
			$templates[] = "header-{$name}.php";
		}
		$templates[] = 'header.php';

		remove_all_actions( 'wp_head' );
		ob_start();
		locate_template( $templates, true );
		ob_end_clean();
	}

	public static function replace_footer( $name = null ) {
		if ( self::is_block_theme() || ! Resolver::get( 'footer' ) ) {
			return;
		}
		require TBFE_PATH . 'templates/parts/footer.php';

		$templates = array();
		if ( '' !== (string) $name ) {
			$templates[] = "footer-{$name}.php";
		}
		$templates[] = 'footer.php';

		remove_all_actions( 'wp_footer' );
		ob_start();
		locate_template( $templates, true );
		ob_end_clean();
	}

	/**
	 * Block themes: header and footer template parts.
	 */
	public static function replace_template_part( $content, $block ) {
		$attrs = isset( $block['attrs'] ) ? $block['attrs'] : array();
		$area  = isset( $attrs['tagName'] ) ? $attrs['tagName'] : '';
		$slug  = isset( $attrs['slug'] ) ? $attrs['slug'] : '';

		foreach ( array( 'header', 'footer' ) as $location ) {
			if ( $location !== $area && $location !== $slug ) {
				continue;
			}
			if ( ! Resolver::get( $location ) ) {
				return $content;
			}
			ob_start();
			self::location_markup( $location );
			return ob_get_clean();
		}
		return $content;
	}

	/**
	 * <header> / <footer> element with the template.
	 */
	public static function location_markup( $location ) {
		$tag   = 'footer' === $location ? 'footer' : 'header';
		$id    = Resolver::get( $location );
		$extra = array(
			'classes' => array(),
			'style'   => '',
			'script'  => false,
		);
		// Sticky, shrinking, hiding or transparent header (settings of the header document).
		if ( 'header' === $location && $id ) {
			$extra = \TBFE\Elementor\Documents\Header::frontend_attributes( $id );
			if ( $extra['script'] ) {
				wp_enqueue_script( 'tbfe-header', \TBFE\Assets::url( 'assets/js/header.js' ), array(), \TBFE\Assets::version( 'assets/js/header.js' ), true );
			}
		}
		printf(
			'<%1$s id="%2$s" class="tbfe-location tbfe-location-%3$s tbfe-template-%4$d%5$s"%6$s%7$s>',
			esc_attr( $tag ),
			'footer' === $location ? 'site-footer' : 'site-header',
			esc_attr( $location ),
			(int) $id,
			$extra['classes'] ? ' ' . esc_attr( implode( ' ', $extra['classes'] ) ) : '',
			'' !== $extra['style'] ? ' style="' . esc_attr( $extra['style'] ) . '"' : '',
			'footer' === $location ? ' role="contentinfo"' : ' role="banner"'
		);
		Renderer::render_location( $location );
		printf( '</%s>', esc_attr( $tag ) );
	}

	/**
	 * Start of the page in the plugin's page templates.
	 */
	public static function page_start() {
		if ( ! self::is_block_theme() ) {
			get_header();
			return;
		}
		require TBFE_PATH . 'templates/parts/document-start.php';
		if ( Resolver::get( 'header' ) ) {
			self::location_markup( 'header' );
		} else {
			block_template_part( 'header' );
		}
	}

	/**
	 * End of the page in the plugin's page templates.
	 */
	public static function page_end() {
		if ( ! self::is_block_theme() ) {
			get_footer();
			return;
		}
		if ( Resolver::get( 'footer' ) ) {
			self::location_markup( 'footer' );
		} else {
			block_template_part( 'footer' );
		}
		require TBFE_PATH . 'templates/parts/document-end.php';
	}
}
