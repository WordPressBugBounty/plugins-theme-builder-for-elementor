<?php
/**
 * Finds the template of each location (header, footer, single, archive, 404) for the current request.
 *
 * Order: per-post choice (meta box) → the most specific matching conditions → newest template.
 *
 * @package TBFE
 */

namespace TBFE\Conditions;

use TBFE\Settings;
use TBFE\Templates\Post_Type;
use TBFE\Templates\Template;
use TBFE\Templates\Types;

defined( 'ABSPATH' ) || exit;

class Resolver {

	const CACHE = 'tbfe_conditions_cache';

	/** Per-post choice of the templates. */
	const META_OVERRIDES = '_tbfe_overrides';

	/** @var array Resolved locations of this request. */
	private static $resolved = array();

	/**
	 * Published templates with conditions, grouped by location. Kept in an option, rebuilt after every change.
	 */
	public static function index() {
		$index = get_option( self::CACHE );
		if ( is_array( $index ) && isset( $index['v'] ) && 2 === $index['v'] ) {
			return $index['locations'];
		}

		$locations = array();
		$ids       = get_posts(
			array(
				'post_type'      => Post_Type::NAME,
				'post_status'    => 'publish',
				'posts_per_page' => 500,
				'fields'         => 'ids',
				'orderby'        => 'modified',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			)
		);
		foreach ( $ids as $id ) {
			$type = Types::get( Template::type( $id ) );
			if ( ! $type || '' === $type['location'] ) {
				continue;
			}
			$rules = Template::conditions( $id );
			if ( $rules ) {
				$locations[ $type['location'] ][ $id ] = $rules;
			}
		}
		update_option(
			self::CACHE,
			array(
				'v'         => 2,
				'locations' => $locations,
			),
			true
		);
		return $locations;
	}

	/**
	 * Template ID used at a location of the current request (0 = the theme's own).
	 */
	public static function get( $location ) {
		if ( array_key_exists( $location, self::$resolved ) ) {
			return self::$resolved[ $location ];
		}
		$id = self::resolve( $location );

		/**
		 * Filters the template of a location (0 = theme default).
		 *
		 * @param int    $id       Template ID.
		 * @param string $location header, footer, single, archive, 404.
		 */
		$id = (int) apply_filters( 'tbfe/location_template', $id, $location );

		self::$resolved[ $location ] = ( $id && 'publish' === get_post_status( $id ) ) ? $id : 0;
		return self::$resolved[ $location ];
	}

	public static function reset() {
		self::$resolved = array();
	}

	private static function resolve( $location ) {
		// The template being edited in Elementor is shown alone.
		if ( is_singular( Post_Type::NAME ) ) {
			return 0;
		}

		if ( 'single' === $location && ! is_singular() ) {
			return 0;
		}
		if ( 'archive' === $location && ! ( is_archive() || is_home() || is_search() ) ) {
			return 0;
		}
		if ( 'search' === $location && ! is_search() ) {
			return 0;
		}
		if ( '404' === $location && ! is_404() ) {
			return 0;
		}

		// Per-post choice.
		if ( is_singular() && in_array( $location, array( 'header', 'footer', 'single' ), true ) ) {
			$override = self::override( get_queried_object_id(), $location );
			if ( -1 === $override ) {
				return 0; // Theme default chosen for this post.
			}
			if ( $override > 0 ) {
				return $override;
			}
			if ( 'single' === $location && self::is_built_with_elementor( get_queried_object_id() ) && Settings::get( 'skip_elementor_built' ) ) {
				return 0;
			}
		}

		$index = self::index();
		if ( empty( $index[ $location ] ) ) {
			return 0;
		}

		// Products and the shop of WooCommerce: All singular / All archives of a blog Single or Archive template do
		// not count there. The blog layout would take the add to cart and the shop loop away; the theme's shop (or the
		// product templates of PRO) stay, unless a condition chooses them, like Products.
		$shop = in_array( $location, array( 'single', 'archive' ), true ) && function_exists( 'is_woocommerce' ) && is_woocommerce();

		$best       = 0;
		$best_score = -1;
		foreach ( $index[ $location ] as $id => $rules ) {
			if ( $shop && in_array( Template::type( $id ), array( 'single', 'archive' ), true ) ) {
				$rules = array_filter(
					$rules,
					static function ( $rule ) {
						return 'exclude' === $rule['type'] || ! in_array( $rule['name'], array( 'singular', 'archive' ), true );
					}
				);
			}
			$score = self::rules_score( $rules );
			if ( $score > $best_score ) {
				$best       = (int) $id;
				$best_score = $score;
			}
		}
		return $best_score >= 0 ? $best : 0;
	}

	/**
	 * Score of the best matching include rule, -1 if no include matches or an exclude matches.
	 */
	public static function rules_score( array $rules ) {
		$score = -1;
		foreach ( $rules as $rule ) {
			if ( 'exclude' === $rule['type'] ) {
				if ( Conditions::matches( $rule['name'], $rule['ids'] ) ) {
					return -1;
				}
				continue;
			}
			if ( Conditions::matches( $rule['name'], $rule['ids'] ) ) {
				$score = max( $score, Conditions::score( $rule ) );
			}
		}
		return $score;
	}

	/**
	 * Per-post choice: template ID, -1 = theme default, 0 = by conditions.
	 */
	public static function override( $post_id, $location ) {
		$overrides = get_post_meta( $post_id, self::META_OVERRIDES, true );
		if ( ! is_array( $overrides ) || ! isset( $overrides[ $location ] ) ) {
			return 0;
		}
		$value = (int) $overrides[ $location ];
		if ( $value > 0 && ! Template::is_template( $value ) ) {
			return 0;
		}
		return $value;
	}

	public static function is_built_with_elementor( $post_id ) {
		if ( ! $post_id || ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}
		$document = \Elementor\Plugin::$instance->documents->get( $post_id );
		return $document && $document->is_built_with_elementor();
	}

	/**
	 * All templates used on the current page (for the CSS).
	 */
	public static function used() {
		$ids = array();
		/**
		 * Filters the locations that can be used on a page (PRO adds cart, checkout, my-account and thankyou).
		 *
		 * @param string[] $locations Locations.
		 */
		$locations = (array) apply_filters( 'tbfe/locations', array( 'header', 'footer', 'single', 'archive', 'search', '404' ) );
		foreach ( $locations as $location ) {
			$id = self::get( $location );
			if ( $id ) {
				$ids[ $location ] = $id;
			}
		}
		return $ids;
	}
}
