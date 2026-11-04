<?php
/**
 * REST API of the administration (namespace tbfe/v1). Every route needs the capability edit_theme_options.
 *
 * @package TBFE
 */

namespace TBFE\Admin;

use TBFE\Conditions\Conditions;
use TBFE\Library\Library;
use TBFE\Migration\Migrator;
use TBFE\Pro;
use TBFE\Settings;
use TBFE\Templates\Post_Type;
use TBFE\Templates\Template;
use TBFE\Templates\Types;

defined( 'ABSPATH' ) || exit;

class Rest {

	const NS = 'tbfe/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function can_manage() {
		return current_user_can( Post_Type::CAPABILITY );
	}

	public static function routes() {
		$admin = array( __CLASS__, 'can_manage' );
		$id    = array(
			'id' => array(
				'type'              => 'integer',
				'required'          => true,
				'sanitize_callback' => 'absint',
			),
		);

		register_rest_route( self::NS, '/bootstrap', array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'bootstrap' ), 'permission_callback' => $admin ) );
		register_rest_route(
			self::NS,
			'/templates',
			array(
				array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'list_templates' ), 'permission_callback' => $admin ),
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'create_template' ),
					'permission_callback' => $admin,
					'args'                => array(
						'type'  => array(
							'type'     => 'string',
							'required' => true,
						),
						'title' => array( 'type' => 'string' ),
					),
				),
			)
		);
		register_rest_route(
			self::NS,
			'/templates/(?P<id>\d+)',
			array(
				array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'update_template' ), 'permission_callback' => $admin, 'args' => $id ),
				array( 'methods' => 'DELETE', 'callback' => array( __CLASS__, 'delete_template' ), 'permission_callback' => $admin, 'args' => $id ),
			)
		);
		register_rest_route( self::NS, '/templates/(?P<id>\d+)/duplicate', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'duplicate_template' ), 'permission_callback' => $admin, 'args' => $id ) );
		register_rest_route( self::NS, '/objects', array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'objects' ), 'permission_callback' => $admin ) );
		register_rest_route( self::NS, '/settings', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'save_settings' ), 'permission_callback' => $admin ) );
		register_rest_route( self::NS, '/settings/section/(?P<section>[a-z0-9-]+)', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'save_section' ), 'permission_callback' => array( __CLASS__, 'can_configure' ) ) );
		register_rest_route(
			self::NS,
			'/widgets',
			array(
				array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'widgets' ), 'permission_callback' => array( __CLASS__, 'can_configure' ) ),
				array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'save_widgets' ), 'permission_callback' => array( __CLASS__, 'can_configure' ) ),
			)
		);
		register_rest_route( self::NS, '/library', array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'library' ), 'permission_callback' => $admin ) );
		register_rest_route( self::NS, '/library/keep', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'library_keep' ), 'permission_callback' => $admin ) );
		register_rest_route( self::NS, '/library/import', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'library_import' ), 'permission_callback' => $admin ) );
		register_rest_route( self::NS, '/library/palette', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'library_palette' ), 'permission_callback' => $admin ) );
		register_rest_route( self::NS, '/library/content', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'library_content' ), 'permission_callback' => array( __CLASS__, 'can_insert' ) ) );
		register_rest_route( self::NS, '/library/import-page', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'library_import_page' ), 'permission_callback' => array( __CLASS__, 'can_import_pages' ) ) );
		register_rest_route( self::NS, '/library/import-finish', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'library_import_finish' ), 'permission_callback' => array( __CLASS__, 'can_import_pages' ) ) );
		register_rest_route( self::NS, '/tools/(?P<tool>[a-z-]+)', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'tool' ), 'permission_callback' => $admin ) );
	}

	/**
	 * Everything the administration needs at start.
	 */
	public static function bootstrap() {
		$types = array();
		// The WooCommerce types of PRO are listed (locked) also when PRO is not installed.
		foreach ( Types::all() + array_diff_key( Pro::teaser_types(), Types::all() ) as $slug => $type ) {
			$types[] = array(
				'slug'        => $slug,
				'label'       => $type['label'],
				'plural'      => $type['plural'],
				'description' => $type['description'],
				'help'        => isset( $type['help'] ) ? $type['help'] : $type['description'],
				'location'    => $type['location'],
				'conditions'  => array_values( (array) $type['conditions'] ),
				'pro'         => ! empty( $type['pro'] ),
				'group'       => isset( $type['group'] ) ? (string) $type['group'] : '',
				'locked'      => ! Pro::type_unlocked( $slug ),
			);
		}
		return rest_ensure_response(
			array(
				'types'      => $types,
				'conditions' => Conditions::for_admin(),
				'templates'  => self::templates(),
				'settings'   => Settings::all(),
				'migration'  => get_option( Migrator::REPORT, null ),
				'reverted'   => (bool) get_option( Migrator::PAUSED ),
				'theme'      => array(
					'name'  => wp_get_theme()->get( 'Name' ),
					'block' => function_exists( 'wp_is_block_theme' ) && wp_is_block_theme(),
				),
				'elementor'  => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '',
				'pro'        => Pro::for_admin(),
				'sections'   => current_user_can( 'manage_options' ) ? \TBFE\Settings_Hub::sections() : array(),
			)
		);
	}

	private static function templates( $type = '' ) {
		$args = array(
			'post_type'      => Post_Type::NAME,
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 500,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		);
		if ( $type ) {
			$args['meta_key']   = Template::META_TYPE; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['meta_value'] = $type; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		}
		return array_map( array( Template::class, 'to_array' ), get_posts( $args ) );
	}

	public static function list_templates( \WP_REST_Request $request ) {
		return rest_ensure_response( self::templates( sanitize_key( (string) $request->get_param( 'type' ) ) ) );
	}

	public static function create_template( \WP_REST_Request $request ) {
		$type = sanitize_key( $request['type'] );
		if ( ! Pro::type_unlocked( $type ) ) {
			return Pro::locked_error();
		}
		$id   = Template::create( $type, (string) $request['title'], 'publish' );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		if ( is_array( $request['conditions'] ) ) {
			Template::set_conditions( $id, $request['conditions'] );
		}
		return rest_ensure_response( Template::to_array( $id ) );
	}

	private static function get_template( $id ) {
		if ( ! Template::is_template( $id ) ) {
			return new \WP_Error( 'tbfe_not_found', __( 'Template not found.', 'theme-builder-for-elementor' ), array( 'status' => 404 ) );
		}
		return get_post( $id );
	}

	public static function update_template( \WP_REST_Request $request ) {
		$post = self::get_template( (int) $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}
		// A PRO template without an active license keeps working, but it cannot be changed.
		if ( ! Pro::type_unlocked( (string) get_post_meta( $post->ID, Template::META_TYPE, true ) ) ) {
			return Pro::locked_error();
		}
		$update = array( 'ID' => $post->ID );
		if ( null !== $request['title'] ) {
			$update['post_title'] = sanitize_text_field( $request['title'] );
		}
		if ( null !== $request['status'] && in_array( $request['status'], array( 'publish', 'draft' ), true ) ) {
			$update['post_status'] = $request['status'];
		}
		if ( count( $update ) > 1 ) {
			// Templates have no page template; an old value (e.g. elementor_canvas) would make wp_update_post() fail.
			delete_post_meta( $post->ID, '_wp_page_template' );
			$result = wp_update_post( $update, true );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}
		if ( is_array( $request['conditions'] ) ) {
			Template::set_conditions( $post->ID, $request['conditions'] );
		}
		if ( is_array( $request['settings'] ) ) {
			Template::set_settings( $post->ID, $request['settings'] );
		}
		Post_Type::flush_cache();
		return rest_ensure_response( Template::to_array( $post->ID ) );
	}

	public static function delete_template( \WP_REST_Request $request ) {
		$post = self::get_template( (int) $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}
		$result = wp_trash_post( $post->ID );
		if ( ! $result ) {
			return new \WP_Error( 'tbfe_delete', __( 'The template could not be moved to the trash.', 'theme-builder-for-elementor' ) );
		}
		Post_Type::flush_cache();
		return rest_ensure_response( array( 'deleted' => $post->ID ) );
	}

	public static function duplicate_template( \WP_REST_Request $request ) {
		$post = self::get_template( (int) $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}
		if ( ! Pro::type_unlocked( (string) get_post_meta( $post->ID, Template::META_TYPE, true ) ) ) {
			return Pro::locked_error();
		}
		$id = wp_insert_post(
			array(
				'post_type'   => Post_Type::NAME,
				/* translators: %s: template title */
				'post_title'  => sprintf( __( '%s (copy)', 'theme-builder-for-elementor' ), $post->post_title ),
				'post_status' => 'draft',
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$skip = array( '_edit_lock', '_edit_last', Template::META_CONDITIONS, '_elementor_css', '_tbfe_legacy_post_type' );
		foreach ( get_post_meta( $post->ID ) as $key => $values ) {
			if ( in_array( $key, $skip, true ) ) {
				continue;
			}
			foreach ( $values as $value ) {
				add_post_meta( $id, $key, wp_slash( maybe_unserialize( $value ) ) );
			}
		}
		Post_Type::flush_cache();
		return rest_ensure_response( Template::to_array( $id ) );
	}

	/**
	 * Posts, terms or users for the ID choice in a condition.
	 */
	public static function objects( \WP_REST_Request $request ) {
		$object  = (string) $request->get_param( 'object' );
		$search  = sanitize_text_field( (string) $request->get_param( 'search' ) );
		$include = array_filter( array_map( 'absint', explode( ',', (string) $request->get_param( 'include' ) ) ) );
		$out     = array();

		if ( 0 === strpos( $object, 'post:' ) ) {
			$pt = substr( $object, 5 );
			if ( ! array_key_exists( $pt, Conditions::post_types() ) ) {
				return rest_ensure_response( array() );
			}
			$args = array(
				'post_type'      => $pt,
				'post_status'    => array( 'publish', 'private', 'draft', 'future' ),
				'posts_per_page' => 20,
				'no_found_rows'  => true,
			);
			if ( $include ) {
				$args['post__in']       = $include;
				$args['posts_per_page'] = count( $include );
			} elseif ( '' !== $search ) {
				$args['s'] = $search;
			}
			foreach ( get_posts( $args ) as $post ) {
				$out[] = array(
					'id'    => $post->ID,
					'label' => get_the_title( $post ) ? get_the_title( $post ) : '#' . $post->ID,
				);
			}
		} elseif ( 0 === strpos( $object, 'term:' ) ) {
			$tax = substr( $object, 5 );
			if ( ! taxonomy_exists( $tax ) ) {
				return rest_ensure_response( array() );
			}
			$args = array(
				'taxonomy'   => $tax,
				'hide_empty' => false,
				'number'     => 20,
			);
			if ( $include ) {
				$args['include'] = $include;
			} elseif ( '' !== $search ) {
				$args['search'] = $search;
			}
			foreach ( get_terms( $args ) as $term ) {
				$out[] = array(
					'id'    => $term->term_id,
					'label' => $term->name,
				);
			}
		} elseif ( 'user' === $object ) {
			$args = array(
				'number'  => 20,
				'fields'  => array( 'ID', 'display_name' ),
				'orderby' => 'display_name',
			);
			if ( $include ) {
				$args['include'] = $include;
			} elseif ( '' !== $search ) {
				$args['search']         = '*' . $search . '*';
				$args['search_columns'] = array( 'display_name', 'user_login', 'user_nicename' );
			} else {
				$args['has_published_posts'] = true;
			}
			foreach ( get_users( $args ) as $user ) {
				$out[] = array(
					'id'    => (int) $user->ID,
					'label' => $user->display_name,
				);
			}
		}
		return rest_ensure_response( $out );
	}

	/**
	 * The feature pages and the widget switches change the whole site: administrators only.
	 */
	public static function can_configure() {
		return self::can_manage() && current_user_can( 'manage_options' );
	}

	public static function save_section( \WP_REST_Request $request ) {
		$values = $request->get_json_params();
		$saved  = \TBFE\Settings_Hub::save( sanitize_key( $request['section'] ), is_array( $values ) ? $values : array() );
		return is_wp_error( $saved ) ? $saved : rest_ensure_response( $saved );
	}

	public static function widgets() {
		return rest_ensure_response( \TBFE\Elementor\Integration::widget_list() );
	}

	public static function save_widgets( \WP_REST_Request $request ) {
		$disabled = array_values( array_unique( array_filter( array_map( 'sanitize_key', (array) $request['disabled'] ) ) ) );
		update_option( \TBFE\Elementor\Integration::DISABLED, $disabled, true );
		// The pages keep the output of their widgets in the cache of Elementor: they are built again.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		return rest_ensure_response( \TBFE\Elementor\Integration::widget_list() );
	}

	public static function save_settings( \WP_REST_Request $request ) {
		$values = $request->get_json_params();
		return rest_ensure_response( Settings::update( is_array( $values ) ? $values : array() ) );
	}

	public static function library( \WP_REST_Request $request ) {
		if ( ! Settings::get( 'library' ) ) {
			return new \WP_Error( 'tbfe_library_off', __( 'The library is turned off in the settings.', 'theme-builder-for-elementor' ), array( 'status' => 403 ) );
		}
		$index = Library::for_admin( (bool) $request->get_param( 'refresh' ) );
		return is_wp_error( $index ) ? $index : rest_ensure_response( $index );
	}

	/**
	 * Keeps copies of the previews the administration has shown (they are then served by this site).
	 */
	public static function library_keep( \WP_REST_Request $request ) {
		if ( ! Settings::get( 'library' ) ) {
			return rest_ensure_response( array() );
		}
		return rest_ensure_response( \TBFE\Library\Previews::keep( array_map( 'strval', (array) $request['keys'] ) ) );
	}

	private static function library_off() {
		return Settings::get( 'library' ) ? null : new \WP_Error( 'tbfe_library_off', __( 'The library is turned off in the settings.', 'theme-builder-for-elementor' ), array( 'status' => 403 ) );
	}

	/**
	 * Website import: templates and pages, also the front page and menus.
	 */
	public static function can_import_pages() {
		return self::can_manage() && current_user_can( 'publish_pages' ) && current_user_can( 'manage_options' );
	}

	/**
	 * Imports one template. With "activate" it gets the usual conditions of its type (entire site, all posts, all
	 * archives, the 404 page).
	 */
	public static function library_import( \WP_REST_Request $request ) {
		$off = self::library_off();
		if ( $off ) {
			return $off;
		}
		self::use_colors( $request );
		$activate = (bool) $request['activate'];
		$id       = Library::import( absint( $request['id'] ), $activate ? 'publish' : 'draft' );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		if ( $activate ) {
			$defaults = array(
				'header'    => 'entire_site',
				'footer'    => 'entire_site',
				'error-404' => 'entire_site',
				'single'    => 'single_post',
				'archive'   => 'archive',
				'search'    => 'entire_site',
				// WooCommerce templates of PRO.
				'popup'           => 'entire_site',
				'product'         => 'product_all',
				'product-archive' => 'shop_all',
				'cart'            => 'entire_site',
				'checkout'        => 'entire_site',
				'my-account'      => 'entire_site',
				'thankyou'        => 'entire_site',
			);
			$type     = Template::type( $id );
			if ( isset( $defaults[ $type ] ) ) {
				Template::set_conditions(
					$id,
					array(
						array(
							'type' => 'include',
							'name' => $defaults[ $type ],
							'ids'  => array(),
						),
					)
				);
				// Instead of the current one: the other templates of this type with the same display are switched off.
				if ( ! empty( $request['replace'] ) ) {
					self::switch_off_others( $id );
				}
			}
		}
		return rest_ensure_response( Template::to_array( $id ) );
	}

	/**
	 * Inserting demos in the Elementor editor: the user edits with Elementor and may use the library.
	 */
	public static function can_insert() {
		return self::can_manage() && current_user_can( 'edit_posts' ) && current_user_can( 'upload_files' );
	}

	/**
	 * Content of a template or a demo page for the Elementor editor (it is inserted there, nothing is saved here).
	 */
	public static function library_content( \WP_REST_Request $request ) {
		$off = self::library_off();
		if ( $off ) {
			return $off;
		}
		\TBFE\Library\Colors::set( $request['colors'] );
		$result = Library::content( 'page' === $request['kind'] ? 'page' : 'item', absint( $request['id'] ) );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	/**
	 * Palette of demos before the import: their colors with a role, and the global colors of the site.
	 */
	public static function library_palette( \WP_REST_Request $request ) {
		$off = self::library_off();
		if ( $off ) {
			return $off;
		}
		$result = \TBFE\Library\Colors::for_entries( (array) $request['entries'] );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	/**
	 * The colors chosen in the import window (with "globals" the global colors of Elementor get them too).
	 */
	private static function use_colors( \WP_REST_Request $request ) {
		\TBFE\Library\Colors::set( $request['colors'] );
		\TBFE\Library\Colors::update_site_colors();
	}

	/**
	 * Switches off (draft) the other published templates of the type of a template that have exactly its display
	 * conditions. Templates with other conditions (e.g. only one category) stay.
	 *
	 * @return int[] IDs of the switched off templates.
	 */
	private static function switch_off_others( $template_id ) {
		$type  = Template::type( $template_id );
		$rules = wp_json_encode( Template::conditions( $template_id ) );
		$off   = array();
		$posts = get_posts(
			array(
				'post_type'      => \TBFE\Templates\Post_Type::NAME,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'post__not_in'   => array( $template_id ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
				'meta_key'       => '_tbfe_type', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $type, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		foreach ( $posts as $other ) {
			if ( wp_json_encode( Template::conditions( $other ) ) === $rules ) {
				wp_update_post(
					array(
						'ID'          => $other,
						'post_status' => 'draft',
					)
				);
				$off[] = (int) $other;
			}
		}
		return $off;
	}

	public static function library_import_page( \WP_REST_Request $request ) {
		$off = self::library_off();
		if ( $off ) {
			return $off;
		}
		self::use_colors( $request );
		$result = Library::import_page( absint( $request['page'] ), 'publish' === $request['status'] ? 'publish' : 'draft', absint( $request['into'] ), sanitize_key( (string) $request['layout'] ) );
		return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
	}

	public static function library_import_finish( \WP_REST_Request $request ) {
		$off = self::library_off();
		if ( $off ) {
			return $off;
		}
		$pages = array();
		foreach ( (array) $request['pages'] as $page ) {
			if ( is_array( $page ) && ! empty( $page['id'] ) ) {
				$pages[] = array(
					'id'       => absint( $page['id'] ),
					'demo_url' => isset( $page['demo_url'] ) ? esc_url_raw( $page['demo_url'] ) : '',
					'home'     => ! empty( $page['home'] ),
				);
			}
		}
		$result = Library::finish(
			$pages,
			array_map( 'absint', (array) $request['templates'] ),
			array(
				'front' => (bool) $request['front'],
				'menu'  => (bool) $request['menu'],
				'name'  => sanitize_text_field( (string) $request['name'] ),
			)
		);
		Post_Type::flush_cache();
		return rest_ensure_response( $result );
	}

	public static function tool( \WP_REST_Request $request ) {
		switch ( $request['tool'] ) {
			case 'regenerate-css':
				\Elementor\Plugin::$instance->files_manager->clear_cache();
				return rest_ensure_response( array( 'message' => __( 'Elementor CSS will be generated again on the next page view.', 'theme-builder-for-elementor' ) ) );
			case 'migrate':
				delete_option( Migrator::OPTION );
				$report = Migrator::migrate();
				return rest_ensure_response( array( 'message' => __( 'The data of version 1 was checked and migrated.', 'theme-builder-for-elementor' ), 'report' => $report ) );
			case 'revert':
				$count = Migrator::revert();
				/* translators: %d: number of templates */
				return rest_ensure_response( array( 'message' => sprintf( __( '%d templates were returned to version 1. You can now install version 1.2.5.', 'theme-builder-for-elementor' ), $count ) ) );
		}
		return new \WP_Error( 'tbfe_tool', __( 'Unknown tool.', 'theme-builder-for-elementor' ), array( 'status' => 404 ) );
	}
}
