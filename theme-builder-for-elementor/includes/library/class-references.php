<?php
/**
 * References between demos of the library: a Posts widget shows a Post Card template, Section Template and Slider show
 * Section templates, a classic archive has a Section template as its sidebar. In the demo files they are the IDs of the
 * demos site; on import each referenced demo is imported too (once) and the reference points to the copy on this site.
 *
 * @package TBFE
 */

namespace TBFE\Library;

use TBFE\Templates\Post_Type;

defined( 'ABSPATH' ) || exit;

class References {

	/** @var int Depth of nested imports (a demo that refers to a demo that refers…). */
	private static $depth = 0;

	/**
	 * Elementor content with the references pointing to templates of this site.
	 */
	public static function content( array $elements ) {
		foreach ( $elements as $i => $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}
			$type = isset( $element['widgetType'] ) ? $element['widgetType'] : '';
			if ( isset( $element['settings'] ) && is_array( $element['settings'] ) ) {
				$settings = &$element['settings'];
				if ( 'tbfe-posts' === $type && ! empty( $settings['card_template'] ) ) {
					$settings['card_template'] = self::local( $settings['card_template'] );
				} elseif ( 'tbfe-template' === $type && ! empty( $settings['template_id'] ) ) {
					$settings['template_id'] = self::local( $settings['template_id'] );
				} elseif ( 'tbfe-slider' === $type && ! empty( $settings['slides'] ) && is_array( $settings['slides'] ) ) {
					foreach ( $settings['slides'] as $k => $slide ) {
						if ( ! empty( $slide['template_id'] ) ) {
							$settings['slides'][ $k ]['template_id'] = self::local( $slide['template_id'] );
						}
					}
				}
				unset( $settings );
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$element['elements'] = self::content( $element['elements'] );
			}
			$elements[ $i ] = $element;
		}
		return $elements;
	}

	/**
	 * Templates of this site that Elementor content refers to (Post Card, Section Template, slides of a Slider).
	 *
	 * @return int[]
	 */
	public static function ids( array $elements ) {
		$ids = array();
		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}
			$type     = isset( $element['widgetType'] ) ? $element['widgetType'] : '';
			$settings = isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : array();
			if ( 'tbfe-posts' === $type && ! empty( $settings['card_template'] ) ) {
				$ids[] = absint( $settings['card_template'] );
			} elseif ( 'tbfe-template' === $type && ! empty( $settings['template_id'] ) ) {
				$ids[] = absint( $settings['template_id'] );
			} elseif ( 'tbfe-slider' === $type && ! empty( $settings['slides'] ) && is_array( $settings['slides'] ) ) {
				foreach ( $settings['slides'] as $slide ) {
					if ( ! empty( $slide['template_id'] ) ) {
						$ids[] = absint( $slide['template_id'] );
					}
				}
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$ids = array_merge( $ids, self::ids( $element['elements'] ) );
			}
		}
		return array_values( array_unique( array_filter( $ids ) ) );
	}

	/**
	 * Template settings (the sidebar of a classic archive) pointing to a template of this site.
	 */
	public static function settings( array $settings ) {
		if ( ! empty( $settings['sidebar'] ) ) {
			$settings['sidebar'] = (int) self::local( $settings['sidebar'] );
		}
		return $settings;
	}

	/**
	 * ID on this site of a demo of the library ('' when it is not in the library or cannot be imported: the widget
	 * then shows its own default, e.g. the built-in post cards). A demo is imported only once; Theme Builder PRO uses
	 * this for the mega menus of an imported website.
	 *
	 * @param int|string $demo_id ID on the demos site.
	 * @return string
	 */
	public static function local( $demo_id ) {
		$demo_id = absint( $demo_id );
		if ( ! $demo_id ) {
			return '';
		}
		$found = get_posts(
			array(
				'post_type'      => Post_Type::NAME,
				'post_status'    => array( 'publish', 'draft', 'private' ),
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_tbfe_library_item', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $demo_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( $found ) {
			// An imported draft would not be shown by the widget.
			if ( 'publish' !== get_post_status( $found[0] ) ) {
				wp_update_post(
					array(
						'ID'          => $found[0],
						'post_status' => 'publish',
					)
				);
			}
			return (string) $found[0];
		}
		if ( self::$depth >= 3 ) {
			return '';
		}
		++self::$depth;
		$id = Library::import( $demo_id, 'publish' );
		--self::$depth;
		return is_wp_error( $id ) ? '' : (string) $id;
	}
}
