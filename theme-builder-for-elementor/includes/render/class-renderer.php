<?php
/**
 * Puts the templates on the page: the content templates (single, archive, 404) replace the template of the theme,
 * the header and footer are handled by Theme_Support. Elementor's CSS of the used templates goes to <head>.
 *
 * @package TBFE
 */

namespace TBFE\Render;

use TBFE\Conditions\Resolver;
use TBFE\Settings;
use TBFE\Templates\Post_Type;
use TBFE\Templates\Template;

defined( 'ABSPATH' ) || exit;

class Renderer {

	/** Page template of version 1 (header and footer of the plugin around the page content). */
	const LEGACY_PAGE_TEMPLATE = 'template-custom.php';

	/** Page template with the page content only (no header and footer). */
	const CANVAS_PAGE_TEMPLATE = 'tbfe-canvas.php';

	/** @var int[] Templates whose CSS is already on the page. */
	private static $css_done = array();

	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'template_include' ), 99 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		// The page templates of the plugin are rendered by the plugin, so they work with every theme (also block themes).
		add_filter( 'theme_page_templates', array( __CLASS__, 'page_templates' ) );
		add_filter( 'theme_post_templates', array( __CLASS__, 'page_templates' ) );
	}

	/**
	 * Page layouts of an import: layout => page template.
	 */
	public static function layouts() {
		return array(
			'full-width' => self::LEGACY_PAGE_TEMPLATE,
			'canvas'     => self::CANVAS_PAGE_TEMPLATE,
		);
	}

	public static function template_include( $template ) {
		if ( is_singular( Post_Type::NAME ) ) {
			return TBFE_PATH . 'templates/canvas.php';
		}
		if ( is_embed() || is_feed() ) {
			return $template;
		}

		if ( is_404() && Resolver::get( '404' ) ) {
			return TBFE_PATH . 'templates/404.php';
		}

		if ( is_singular() ) {
			$page_template = get_page_template_slug( get_queried_object_id() );
			if ( self::LEGACY_PAGE_TEMPLATE === $page_template ) {
				return TBFE_PATH . 'templates/full-width.php';
			}
			if ( self::CANVAS_PAGE_TEMPLATE === $page_template ) {
				return TBFE_PATH . 'templates/canvas.php';
			}
			// Elementor's own page templates and other page templates of the theme are respected.
			if ( $page_template && 'default' !== $page_template ) {
				return $template;
			}
			if ( Resolver::get( 'single' ) ) {
				return TBFE_PATH . 'templates/single.php';
			}
			return $template;
		}

		if ( is_search() && Resolver::get( 'search' ) ) {
			return TBFE_PATH . 'templates/archive.php';
		}
		if ( ( is_archive() || is_home() || is_search() ) && Resolver::get( 'archive' ) ) {
			return TBFE_PATH . 'templates/archive.php';
		}

		return $template;
	}

	/**
	 * Elementor styles and the CSS of every template used on this page, in <head>.
	 */
	public static function enqueue() {
		$ids = Resolver::used();
		$listing = isset( $ids['search'] ) ? $ids['search'] : ( isset( $ids['archive'] ) ? $ids['archive'] : 0 );
		if ( $listing ) {
			$settings = Template::settings( $listing );
			if ( 'loop' === $settings['mode'] && $settings['sidebar'] ) {
				$ids['sidebar'] = $settings['sidebar'];
			}
		}
		if ( ! $ids ) {
			return;
		}

		wp_enqueue_style( 'tbfe-frontend', \TBFE\Assets::url( 'assets/css/frontend.css' ), array(), \TBFE\Assets::version( 'assets/css/frontend.css' ) );
		wp_add_inline_style( 'tbfe-frontend', sprintf( ':root{--tbfe-container:%dpx}', (int) Settings::get( 'container_width' ) ) );

		$elementor = \Elementor\Plugin::$instance;
		$elementor->frontend->enqueue_styles();

		foreach ( array_unique( $ids ) as $id ) {
			if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
				\Elementor\Core\Files\CSS\Post::create( $id )->enqueue();
				self::$css_done[] = (int) $id;
			}
		}

		// The styles of the widgets in the templates go to the head too: printed in the footer, a drop-down of the
		// header menu shows open until they load.
		$types = array();
		foreach ( array_unique( $ids ) as $id ) {
			$data = get_post_meta( $id, '_elementor_data', true );
			$data = is_string( $data ) ? json_decode( $data, true ) : $data;
			self::widget_types( is_array( $data ) ? $data : array(), $types );
		}
		$manager = $elementor->widgets_manager;
		foreach ( array_keys( $types ) as $type ) {
			$widget = $manager ? $manager->get_widget_types( $type ) : null;
			if ( $widget ) {
				foreach ( $widget->get_style_depends() as $handle ) {
					wp_enqueue_style( $handle );
				}
			}
		}
		/**
		 * Fires after the assets of the used templates are enqueued in the head.
		 *
		 * @param string[] $types Widget types used in the templates.
		 * @param int[]    $ids   Template IDs.
		 */
		do_action( 'tbfe/enqueue_template_assets', array_keys( $types ), array_values( array_unique( $ids ) ) );
	}

	/**
	 * Widget types in Elementor data.
	 */
	private static function widget_types( array $elements, array &$types ) {
		foreach ( $elements as $element ) {
			if ( ! empty( $element['widgetType'] ) ) {
				$types[ $element['widgetType'] ] = true;
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				self::widget_types( $element['elements'], $types );
			}
		}
	}

	/**
	 * Prints the Elementor content of a template.
	 *
	 * @param int $id Template ID.
	 */
	/**
	 * Classes of the post element in the plugin's layouts: get_post_class() without the post_class filters of the
	 * theme. Themes add their own card and grid classes there (Astra: ast-article-post, ast-grid-common-col) and
	 * style them as boxes, which put a theme box around the cards of the Posts widget. Plugins (WooCommerce) stay.
	 *
	 * @param string $class Own class.
	 * @return string[]
	 */
	public static function post_class( $class = '' ) {
		global $wp_filter;
		$removed = array();
		if ( isset( $wp_filter['post_class'] ) ) {
			$dirs = array_unique( array( wp_normalize_path( get_template_directory() ) . '/', wp_normalize_path( get_stylesheet_directory() ) . '/' ) );
			foreach ( $wp_filter['post_class']->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback ) {
					$file = self::callback_file( $callback['function'] );
					foreach ( $dirs as $dir ) {
						if ( $file && 0 === strpos( $file, $dir ) ) {
							$removed[] = array( $callback['function'], $priority, $callback['accepted_args'] );
							break;
						}
					}
				}
			}
		}
		foreach ( $removed as $filter ) {
			remove_filter( 'post_class', $filter[0], $filter[1] );
		}
		$classes = get_post_class( $class );
		foreach ( $removed as $filter ) {
			add_filter( 'post_class', $filter[0], $filter[1], $filter[2] );
		}
		return $classes;
	}

	/**
	 * File that defines a callback ('' when unknown).
	 */
	private static function callback_file( $callback ) {
		try {
			if ( is_string( $callback ) && false !== strpos( $callback, '::' ) ) {
				$callback = explode( '::', $callback, 2 );
			}
			if ( is_array( $callback ) && 2 === count( $callback ) ) {
				$reflection = new \ReflectionMethod( $callback[0], $callback[1] );
			} elseif ( $callback instanceof \Closure || ( is_string( $callback ) && function_exists( $callback ) ) ) {
				$reflection = new \ReflectionFunction( $callback );
			} else {
				return '';
			}
			return wp_normalize_path( (string) $reflection->getFileName() );
		} catch ( \ReflectionException $e ) {
			return '';
		}
	}

	public static function render_template( $id ) {
		$id = (int) $id;
		if ( ! $id || ! Template::is_template( $id ) ) {
			return;
		}
		$with_css = ! in_array( $id, self::$css_done, true );
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor builds and escapes the markup.
		echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, $with_css );
		self::$css_done[] = $id;
	}

	/**
	 * Prints the template of a location.
	 *
	 * @param string $location header, footer, single, archive, 404.
	 */
	public static function render_location( $location ) {
		$id = Resolver::get( $location );
		if ( ! $id ) {
			return false;
		}
		do_action( 'tbfe/before_location', $location, $id );
		self::render_template( $id );
		do_action( 'tbfe/after_location', $location, $id );
		return true;
	}

	public static function body_class( $classes ) {
		foreach ( Resolver::used() as $location => $id ) {
			$classes[] = 'tbfe-has-' . sanitize_html_class( $location );
		}
		if ( Resolver::used() ) {
			$classes[] = 'tbfe';
		}
		return $classes;
	}

	/**
	 * Page templates of the plugin: "Full width" (the page between the header and footer, the template of version 1)
	 * and "Canvas" (the page alone).
	 */
	public static function page_templates( $templates ) {
		$templates[ self::LEGACY_PAGE_TEMPLATE ] = __( 'Theme Builder - Full width', 'theme-builder-for-elementor' );
		$templates[ self::CANVAS_PAGE_TEMPLATE ] = __( 'Theme Builder - Canvas', 'theme-builder-for-elementor' );
		return $templates;
	}
}
