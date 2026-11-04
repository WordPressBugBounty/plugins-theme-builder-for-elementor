<?php
/**
 * The post type of the templates (tbfe_template).
 *
 * The templates are not public: only users who can edit them see them on the front end (the Elementor editor
 * previews them there). They are never indexed and have no archive.
 *
 * @package TBFE
 */

namespace TBFE\Templates;

defined( 'ABSPATH' ) || exit;

class Post_Type {

	const NAME = 'tbfe_template';

	/** The capability that manages templates. */
	const CAPABILITY = 'edit_theme_options';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ), 5 );
		add_filter( 'user_has_cap', array( __CLASS__, 'grant_caps' ), 10, 3 );
		add_action( 'template_redirect', array( __CLASS__, 'protect_front_end' ), 1 );
		add_filter( 'wp_robots', array( __CLASS__, 'robots' ) );
		add_filter( 'option_elementor_cpt_support', array( __CLASS__, 'elementor_support' ) );
		add_filter( 'default_option_elementor_cpt_support', array( __CLASS__, 'elementor_support' ) );
		add_action( 'save_post_' . self::NAME, array( __CLASS__, 'flush_cache' ) );
		add_action( 'before_delete_post', array( __CLASS__, 'flush_cache' ) );
		add_action( 'trashed_post', array( __CLASS__, 'flush_cache' ) );
		add_action( 'untrashed_post', array( __CLASS__, 'flush_cache' ) );
	}

	public static function register() {
		if ( post_type_exists( self::NAME ) ) {
			return;
		}
		register_post_type(
			self::NAME,
			array(
				'labels'              => array(
					'name'               => __( 'Theme Templates', 'theme-builder-for-elementor' ),
					'singular_name'      => __( 'Theme Template', 'theme-builder-for-elementor' ),
					'add_new_item'       => __( 'Add New Template', 'theme-builder-for-elementor' ),
					'edit_item'          => __( 'Edit Template', 'theme-builder-for-elementor' ),
					'view_item'          => __( 'Preview Template', 'theme-builder-for-elementor' ),
					'search_items'       => __( 'Search Templates', 'theme-builder-for-elementor' ),
					'not_found'          => __( 'No templates found.', 'theme-builder-for-elementor' ),
					'not_found_in_trash' => __( 'No templates found in Trash.', 'theme-builder-for-elementor' ),
				),
				'public'              => false,
				'publicly_queryable'  => true, // The Elementor editor previews the template on the front end.
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => self::NAME,
				'hierarchical'        => false,
				'capability_type'     => array( 'tbfe_template', 'tbfe_templates' ),
				'map_meta_cap'        => true,
				'supports'            => array( 'title', 'author', 'revisions', 'elementor' ),
			)
		);
	}

	/**
	 * Users who can edit the theme (administrators) get all template capabilities.
	 */
	public static function grant_caps( $allcaps, $caps, $args ) {
		if ( empty( $allcaps[ self::CAPABILITY ] ) ) {
			return $allcaps;
		}
		foreach ( $caps as $cap ) {
			if ( false !== strpos( $cap, 'tbfe_template' ) ) {
				$allcaps[ $cap ] = true;
			}
		}
		return $allcaps;
	}

	/**
	 * Only editors of the template may see it on the front end (the editor preview).
	 */
	public static function protect_front_end() {
		if ( ! is_singular( self::NAME ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', get_queried_object_id() ) ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
		}
	}

	public static function robots( $robots ) {
		if ( is_singular( self::NAME ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}

	/**
	 * Elementor may always edit the templates.
	 */
	public static function elementor_support( $value ) {
		$value = is_array( $value ) ? $value : array( 'post', 'page' );
		// Post types of version 1 (migrated to tbfe_template).
		$value = array_values( array_diff( $value, array_keys( \TBFE\Migration\Migrator::POST_TYPES ) ) );
		if ( ! in_array( self::NAME, $value, true ) ) {
			$value[] = self::NAME;
		}
		return $value;
	}

	public static function flush_cache( $post_id = 0 ) {
		if ( $post_id && self::NAME !== get_post_type( $post_id ) ) {
			return;
		}
		delete_option( 'tbfe_conditions_cache' );
	}
}
