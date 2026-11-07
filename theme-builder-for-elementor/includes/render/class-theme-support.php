<?php
/**
 * Replaces the header and footer of any theme.
 *
 * Classic themes: on get_header / get_footer the plugin prints its own header (with wp_head) or footer (with
 * wp_footer) and loads the theme's header.php / footer.php into a buffer that is thrown away. On pages the theme
 * renders itself, the content wrappers that header.php leaves open are printed again after the plugin's header and
 * closed before its footer, so the theme's content keeps its container.
 * Block themes: the header and footer template parts are replaced, and the plugin's page templates print the whole
 * document themselves.
 *
 * @package TBFE
 */

namespace TBFE\Render;

use TBFE\Conditions\Resolver;

defined( 'ABSPATH' ) || exit;

class Theme_Support {

	/** @var bool The page is rendered by a page template of the plugin (page_start()). */
	private static $plugin_page = false;

	/** @var array[] Content wrappers of the theme reopened after the plugin's header: tag, html. */
	private static $wrappers = array();

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
		self::remove_theme_part( 'header' );
		require TBFE_PATH . 'templates/parts/header.php';

		$templates = array();
		if ( '' !== (string) $name ) {
			$templates[] = "header-{$name}.php";
		}
		$templates[] = 'header.php';

		remove_all_actions( 'wp_head' );
		ob_start();
		locate_template( $templates, true );
		$html = ob_get_clean();

		// Pages the theme renders itself (no page template of the plugin): its content wrappers stay.
		if ( ! self::$plugin_page ) {
			self::$wrappers = self::content_wrappers( $html );
			foreach ( self::$wrappers as $wrapper ) {
				echo $wrapper['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- opening tag printed by the theme.
			}
		}
	}

	public static function replace_footer( $name = null ) {
		if ( self::is_block_theme() || ! Resolver::get( 'footer' ) ) {
			return;
		}
		self::remove_theme_part( 'footer' );
		foreach ( array_reverse( self::$wrappers ) as $wrapper ) {
			printf( '</%s>', esc_html( $wrapper['tag'] ) );
		}
		self::$wrappers = array();
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
	 * Elements that the theme's header.php leaves open after its header (Astra: #content.site-content >
	 * .ast-container, Twenty Twenty-One: #content > #primary > main…) and closes in footer.php. Wrappers around the
	 * theme's header (#page) are left out, the plugin's header is not inside them on any page.
	 *
	 * @param string $html Output of header.php.
	 * @return array[] tag, html (opening tag), outermost first.
	 */
	public static function content_wrappers( $html ) {
		if ( ! class_exists( 'WP_HTML_Tag_Processor' ) || false === stripos( $html, '<body' ) ) {
			return array();
		}
		$void      = array( 'AREA', 'BASE', 'BR', 'COL', 'EMBED', 'HR', 'IMG', 'INPUT', 'LINK', 'META', 'SOURCE', 'TRACK', 'WBR' );
		$processor = new \WP_HTML_Tag_Processor( $html );
		$stack     = array();
		$in_body   = false;
		$boundary  = null; // Depth of the parent of the theme's header.

		while ( $processor->next_tag( array( 'tag_closers' => 'visit' ) ) ) {
			$tag = $processor->get_tag();
			if ( ! $in_body ) {
				$in_body = 'BODY' === $tag && ! $processor->is_tag_closer();
				continue;
			}
			if ( $processor->is_tag_closer() ) {
				// Closes the last element with that tag (and the ones left open inside it, like svg paths).
				for ( $i = count( $stack ) - 1; $i >= 0 && $stack[ $i ]['tag'] !== $tag; $i-- ) {
				}
				if ( $i < 0 ) {
					continue;
				}
				$element = $stack[ $i ];
				$stack   = array_slice( $stack, 0, $i );
				if ( $element['header'] && ( null === $boundary || $i < $boundary ) ) {
					$boundary = $i;
				}
				continue;
			}
			if ( in_array( $tag, $void, true ) || in_array( $tag, array( 'BODY', 'HTML' ), true ) ) {
				continue;
			}

			$attributes = '';
			foreach ( (array) $processor->get_attribute_names_with_prefix( '' ) as $name ) {
				$value       = $processor->get_attribute( $name );
				$attributes .= true === $value ? ' ' . $name : sprintf( ' %s="%s"', $name, esc_attr( $value ) );
			}
			$id      = (string) $processor->get_attribute( 'id' );
			$class   = ' ' . $processor->get_attribute( 'class' ) . ' ';
			$stack[] = array(
				'tag'    => $tag,
				'html'   => sprintf( '<%s%s>', strtolower( $tag ), $attributes ),
				'header' => 'HEADER' === $tag || in_array( $id, array( 'masthead', 'site-header', 'header' ), true ) || false !== strpos( $class, ' site-header ' ),
			);
		}

		$wrappers = array_slice( $stack, (int) $boundary );
		foreach ( $wrappers as &$wrapper ) {
			$wrapper['tag'] = strtolower( $wrapper['tag'] );
			unset( $wrapper['header'] );
		}
		unset( $wrapper );

		/**
		 * Filters the content wrappers of the theme reopened after the plugin's header on pages the theme renders.
		 *
		 * @param array[] $wrappers array( array( 'tag' => 'div', 'html' => '<div id="content">' ), … ).
		 * @param string  $html     Output of the theme's header.php.
		 */
		return (array) apply_filters( 'tbfe/theme_support/content_wrappers', $wrappers, $html );
	}

	/**
	 * Classic themes that print their header on wp_body_open or their footer on wp_footer (not in header.php /
	 * footer.php), like the Envo themes: those callbacks are removed while the plugin's header or footer is shown
	 * and on canvas pages.
	 */
	public static function remove_theme_part( $location ) {
		$prefix = str_replace( '-', '_', get_template() );
		$parts  = array(
			'header' => array( array( 'wp_body_open', $prefix . '_body_header_code' ) ),
			'footer' => array( array( 'wp_footer', $prefix . '_wp_footer_code' ) ),
		);

		/**
		 * Filters the callbacks of the theme that print its header or footer outside header.php / footer.php.
		 *
		 * @param array  $callbacks array( array( hook, callback ), … ).
		 * @param string $location  header or footer.
		 */
		$callbacks = apply_filters( 'tbfe/theme_support/theme_part_callbacks', $parts[ $location ], $location );

		foreach ( (array) $callbacks as $callback ) {
			list( $hook, $function ) = $callback;
			$priority                = has_action( $hook, $function );
			if ( false !== $priority ) {
				remove_action( $hook, $function, $priority );
			}
		}
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
		self::$plugin_page = true;
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
