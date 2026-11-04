<?php
/**
 * Display conditions: where a template is used.
 *
 * A rule is array( 'type' => 'include'|'exclude', 'name' => condition key, 'ids' => int[] ). With ids, the condition
 * is limited to those posts, terms or users. The PRO add-on adds conditions with the filter tbfe/conditions.
 *
 * @package TBFE
 */

namespace TBFE\Conditions;

use TBFE\Templates\Types;

defined( 'ABSPATH' ) || exit;

class Conditions {

	/** @var array|null */
	private static $definitions = null;

	/**
	 * All conditions: key => group, label, object (what the ids are: post:<type>, term:<taxonomy>, user, ''),
	 * score (how specific the condition is; with ids it is more specific).
	 */
	public static function definitions() {
		if ( null !== self::$definitions ) {
			return self::$definitions;
		}

		$defs = array(
			'entire_site' => array(
				'group' => 'general',
				'label' => __( 'Entire site', 'theme-builder-for-elementor' ),
				'score' => 10,
			),
			'singular'    => array(
				'group' => 'singular',
				'label' => __( 'All singular', 'theme-builder-for-elementor' ),
				'score' => 20,
			),
			'front_page'  => array(
				'group' => 'singular',
				'label' => __( 'Front page', 'theme-builder-for-elementor' ),
				'score' => 50,
			),
		);

		foreach ( self::post_types() as $pt => $obj ) {
			$defs[ 'single_' . $pt ] = array(
				'group'  => 'singular',
				'label'  => $obj->labels->name,
				'object' => 'post:' . $pt,
				'score'  => 30,
			);
			foreach ( get_object_taxonomies( $pt, 'objects' ) as $tax ) {
				if ( ! $tax->public || ! $tax->show_ui ) {
					continue;
				}
				$defs[ 'in_' . $pt . '_' . $tax->name ] = array(
					'group'  => 'singular',
					/* translators: 1: post type, 2: taxonomy, e.g. Posts in Category */
					'label'  => sprintf( __( '%1$s in %2$s', 'theme-builder-for-elementor' ), $obj->labels->name, $tax->labels->singular_name ),
					'object' => 'term:' . $tax->name,
					'score'  => 40,
				);
			}
		}
		$defs['by_author'] = array(
			'group'  => 'singular',
			'label'  => __( 'Posts by author', 'theme-builder-for-elementor' ),
			'object' => 'user',
			'score'  => 35,
		);
		$defs['not_found'] = array(
			'group' => 'singular',
			'label' => __( '404 page', 'theme-builder-for-elementor' ),
			'score' => 50,
		);

		$defs['archive'] = array(
			'group' => 'archive',
			'label' => __( 'All archives', 'theme-builder-for-elementor' ),
			'score' => 20,
		);
		$defs['blog']    = array(
			'group' => 'archive',
			'label' => __( 'Blog (posts page)', 'theme-builder-for-elementor' ),
			'score' => 35,
		);
		$defs['search']  = array(
			'group' => 'archive',
			'label' => __( 'Search results', 'theme-builder-for-elementor' ),
			'score' => 35,
		);
		$defs['author']  = array(
			'group'  => 'archive',
			'label'  => __( 'Author archive', 'theme-builder-for-elementor' ),
			'object' => 'user',
			'score'  => 30,
		);
		$defs['date']    = array(
			'group' => 'archive',
			'label' => __( 'Date archive', 'theme-builder-for-elementor' ),
			'score' => 30,
		);
		foreach ( get_taxonomies( array( 'public' => true, 'show_ui' => true ), 'objects' ) as $tax ) {
			$defs[ 'tax_' . $tax->name ] = array(
				'group'  => 'archive',
				'label'  => $tax->labels->name,
				'object' => 'term:' . $tax->name,
				'score'  => 30,
			);
		}
		foreach ( self::post_types() as $pt => $obj ) {
			if ( $obj->has_archive ) {
				$defs[ 'archive_' . $pt ] = array(
					'group' => 'archive',
					/* translators: %s: post type name */
					'label' => sprintf( __( '%s archive', 'theme-builder-for-elementor' ), $obj->labels->name ),
					'score' => 30,
				);
			}
		}

		/**
		 * Filters the display conditions. A custom condition needs a callback: function( array $ids ): bool.
		 *
		 * @param array $defs Conditions keyed by name.
		 */
		self::$definitions = (array) apply_filters( 'tbfe/conditions', $defs );
		return self::$definitions;
	}

	/**
	 * Forgets the definitions (they are built again with all post types and taxonomies registered).
	 */
	public static function reset() {
		self::$definitions = null;
	}

	public static function groups() {
		/**
		 * Filters the groups of the display conditions (PRO adds the product and shop conditions).
		 *
		 * @param array $groups Group key => label.
		 */
		return (array) apply_filters(
			'tbfe/condition_groups',
			array(
				'general'  => __( 'General', 'theme-builder-for-elementor' ),
				'singular' => __( 'Singular', 'theme-builder-for-elementor' ),
				'archive'  => __( 'Archives', 'theme-builder-for-elementor' ),
			)
		);
	}

	/**
	 * Public post types usable in conditions.
	 */
	public static function post_types() {
		$types = get_post_types( array( 'public' => true ), 'objects' );
		unset( $types['attachment'], $types['elementor_library'], $types['e-landing-page'] );
		return $types;
	}

	/**
	 * Cleans rules from the administration: known names allowed for the template type, ids as integers.
	 */
	public static function sanitize_rules( $rules, $template_type ) {
		$type   = Types::get( $template_type );
		$groups = $type ? (array) $type['conditions'] : array();
		$defs   = self::definitions();
		$out    = array();

		foreach ( (array) $rules as $rule ) {
			if ( ! is_array( $rule ) || empty( $rule['name'] ) ) {
				continue;
			}
			$name = sanitize_key( $rule['name'] );
			if ( ! isset( $defs[ $name ] ) || ! in_array( $defs[ $name ]['group'], $groups, true ) ) {
				continue;
			}
			$ids = array();
			if ( ! empty( $defs[ $name ]['object'] ) && ! empty( $rule['ids'] ) ) {
				$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $rule['ids'] ) ) ) );
			}
			$item = array(
				'type' => ( isset( $rule['type'] ) && 'exclude' === $rule['type'] ) ? 'exclude' : 'include',
				'name' => $name,
				'ids'  => $ids,
			);
			$out[ md5( wp_json_encode( $item ) ) ] = $item;
		}
		return array_values( $out );
	}

	/**
	 * Does the condition match the current request?
	 */
	public static function matches( $name, array $ids = array() ) {
		$defs = self::definitions();
		if ( ! isset( $defs[ $name ] ) ) {
			return false;
		}
		if ( isset( $defs[ $name ]['callback'] ) && is_callable( $defs[ $name ]['callback'] ) ) {
			return (bool) call_user_func( $defs[ $name ]['callback'], $ids );
		}

		$object = get_queried_object();

		switch ( $name ) {
			case 'entire_site':
				return true;
			case 'singular':
				return is_singular() && ! is_404();
			case 'front_page':
				return is_front_page() && ! is_home();
			case 'not_found':
				return is_404();
			case 'by_author':
				return is_singular() && $object instanceof \WP_Post && ( ! $ids || in_array( (int) $object->post_author, $ids, true ) );
			case 'archive':
				return is_archive() || is_home() || is_search();
			case 'blog':
				return is_home();
			case 'search':
				return is_search();
			case 'author':
				return is_author() && ( ! $ids || in_array( (int) get_queried_object_id(), $ids, true ) );
			case 'date':
				return is_date();
		}

		if ( 0 === strpos( $name, 'single_' ) ) {
			$pt = substr( $name, 7 );
			return is_singular( $pt ) && ( ! $ids || in_array( (int) get_queried_object_id(), $ids, true ) );
		}
		if ( 0 === strpos( $name, 'in_' ) && ! empty( $defs[ $name ]['object'] ) ) {
			$tax = substr( $defs[ $name ]['object'], 5 );
			$pt  = substr( $name, 3, strlen( $name ) - 4 - strlen( $tax ) );
			if ( ! is_singular( $pt ) ) {
				return false;
			}
			return $ids ? has_term( $ids, $tax, get_queried_object_id() ) : has_term( '', $tax, get_queried_object_id() );
		}
		if ( 0 === strpos( $name, 'tax_' ) ) {
			$tax = substr( $name, 4 );
			if ( 'category' === $tax ) {
				$hit = is_category();
			} elseif ( 'post_tag' === $tax ) {
				$hit = is_tag();
			} else {
				$hit = is_tax( $tax );
			}
			if ( ! $hit || ! $ids ) {
				return $hit;
			}
			// A term also covers its child terms.
			$term_id = (int) get_queried_object_id();
			foreach ( $ids as $id ) {
				if ( $term_id === $id || term_is_ancestor_of( $id, $term_id, $tax ) ) {
					return true;
				}
			}
			return false;
		}
		if ( 0 === strpos( $name, 'archive_' ) ) {
			return is_post_type_archive( substr( $name, 8 ) );
		}
		return false;
	}

	/**
	 * How specific a rule is (the most specific template wins).
	 */
	public static function score( array $rule ) {
		$defs  = self::definitions();
		$score = isset( $defs[ $rule['name'] ]['score'] ) ? (int) $defs[ $rule['name'] ]['score'] : 10;
		return ! empty( $rule['ids'] ) ? $score + 15 : $score;
	}

	/**
	 * Short text of the rules for the template list, e.g. "Pages, Blog · except About us".
	 */
	public static function summary( array $rules, $template_type ) {
		$type = Types::get( $template_type );
		if ( ! $type || ! $type['conditions'] ) {
			return '';
		}
		$defs = self::definitions();
		$in   = array();
		$out  = array();
		foreach ( $rules as $rule ) {
			if ( ! isset( $defs[ $rule['name'] ] ) ) {
				continue;
			}
			$label = $defs[ $rule['name'] ]['label'];
			if ( ! empty( $rule['ids'] ) ) {
				$names = array_slice( array_map( array( __CLASS__, 'object_label' ), array_fill( 0, count( $rule['ids'] ), $defs[ $rule['name'] ]['object'] ), $rule['ids'] ), 0, 3 );
				$label .= ': ' . implode( ', ', $names ) . ( count( $rule['ids'] ) > 3 ? '…' : '' );
			}
			if ( 'exclude' === $rule['type'] ) {
				$out[] = $label;
			} else {
				$in[] = $label;
			}
		}
		$text = implode( ', ', $in );
		if ( $out ) {
			/* translators: %s: list of excluded conditions */
			$text .= ' · ' . sprintf( __( 'except %s', 'theme-builder-for-elementor' ), implode( ', ', $out ) );
		}
		return $text;
	}

	/**
	 * Name of a post, term or user in a condition.
	 */
	public static function object_label( $object, $id ) {
		if ( 0 === strpos( (string) $object, 'post:' ) ) {
			$title = get_the_title( $id );
			return '' !== $title ? $title : '#' . $id;
		}
		if ( 0 === strpos( (string) $object, 'term:' ) ) {
			$term = get_term( $id );
			return ( $term && ! is_wp_error( $term ) ) ? $term->name : '#' . $id;
		}
		if ( 'user' === $object ) {
			$user = get_userdata( $id );
			return $user ? $user->display_name : '#' . $id;
		}
		return '#' . $id;
	}

	/**
	 * Data for the conditions editor in the administration.
	 */
	public static function for_admin() {
		$list = array();
		foreach ( self::definitions() as $name => $def ) {
			$list[] = array(
				'name'   => $name,
				'group'  => $def['group'],
				'label'  => $def['label'],
				'object' => isset( $def['object'] ) ? $def['object'] : '',
			);
		}
		return array(
			'groups'     => self::groups(),
			'conditions' => $list,
		);
	}
}
