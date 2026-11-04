<?php
/**
 * Reading and writing one template (type, conditions, settings).
 *
 * @package TBFE
 */

namespace TBFE\Templates;

use TBFE\Conditions\Conditions;

defined( 'ABSPATH' ) || exit;

class Template {

	const META_TYPE       = '_tbfe_type';
	const META_CONDITIONS = '_tbfe_conditions';
	const META_SETTINGS   = '_tbfe_settings';

	public static function is_template( $post_id ) {
		return $post_id && Post_Type::NAME === get_post_type( $post_id );
	}

	public static function type( $post_id ) {
		$type = get_post_meta( $post_id, self::META_TYPE, true );
		return Types::exists( $type ) ? $type : 'section';
	}

	public static function conditions( $post_id ) {
		$rules = get_post_meta( $post_id, self::META_CONDITIONS, true );
		return is_array( $rules ) ? $rules : array();
	}

	public static function set_conditions( $post_id, $rules ) {
		$rules = Conditions::sanitize_rules( $rules, self::type( $post_id ) );
		update_post_meta( $post_id, self::META_CONDITIONS, $rules );
		Post_Type::flush_cache();
		return $rules;
	}

	/**
	 * Template settings. Archive: mode (layout = the template is the whole archive, loop = the template is repeated for
	 * each post like in version 1), width (boxed, full), sidebar (section template ID).
	 */
	public static function settings( $post_id ) {
		$saved = get_post_meta( $post_id, self::META_SETTINGS, true );
		return array_merge(
			array(
				'mode'    => 'layout',
				'width'   => 'boxed',
				'sidebar' => 0,
			),
			is_array( $saved ) ? $saved : array()
		);
	}

	public static function set_settings( $post_id, $values ) {
		$values = is_array( $values ) ? $values : array();
		$out    = self::settings( $post_id );

		if ( isset( $values['mode'] ) ) {
			$out['mode'] = 'loop' === $values['mode'] ? 'loop' : 'layout';
		}
		if ( isset( $values['width'] ) ) {
			$out['width'] = 'full' === $values['width'] ? 'full' : 'boxed';
		}
		if ( isset( $values['sidebar'] ) ) {
			$sidebar        = absint( $values['sidebar'] );
			$out['sidebar'] = ( $sidebar && self::is_template( $sidebar ) ) ? $sidebar : 0;
		}
		update_post_meta( $post_id, self::META_SETTINGS, $out );
		Post_Type::flush_cache();
		return $out;
	}

	/**
	 * Creates an empty template.
	 *
	 * @return int|\WP_Error
	 */
	public static function create( $type, $title = '', $status = 'publish' ) {
		if ( ! Types::exists( $type ) ) {
			return new \WP_Error( 'tbfe_type', __( 'Unknown template type.', 'theme-builder-for-elementor' ) );
		}
		if ( '' === trim( $title ) ) {
			$title = Types::get( $type )['label'];
		}
		$id = wp_insert_post(
			array(
				'post_type'   => Post_Type::NAME,
				'post_title'  => sanitize_text_field( $title ),
				'post_status' => in_array( $status, array( 'publish', 'draft' ), true ) ? $status : 'draft',
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		update_post_meta( $id, self::META_TYPE, $type );
		update_post_meta( $id, '_elementor_template_type', Types::document_type( $type ) );
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		Post_Type::flush_cache();
		return $id;
	}

	/**
	 * URL of the Elementor editor of a template.
	 */
	public static function edit_url( $post_id ) {
		return add_query_arg(
			array(
				'post'   => $post_id,
				'action' => 'elementor',
			),
			admin_url( 'post.php' )
		);
	}

	/**
	 * Data for the administration (REST).
	 */
	public static function to_array( $post ) {
		$post = get_post( $post );
		$type = self::type( $post->ID );
		// A template of PRO while PRO is not active: listed under its own type, locked.
		$raw = (string) get_post_meta( $post->ID, self::META_TYPE, true );
		if ( ! Types::exists( $raw ) && isset( \TBFE\Pro::teaser_types()[ $raw ] ) ) {
			$type = $raw;
		}
		return array(
			'id'         => $post->ID,
			'title'      => get_the_title( $post ),
			'type'       => $type,
			'locked'     => ! \TBFE\Pro::type_unlocked( $type ),
			'status'     => $post->post_status,
			'conditions' => self::conditions( $post->ID ),
			'settings'   => self::settings( $post->ID ),
			'summary'    => Conditions::summary( self::conditions( $post->ID ), $type ),
			'modified'   => get_post_modified_time( 'c', true, $post ),
			'author'     => get_the_author_meta( 'display_name', $post->post_author ),
			'edit_url'   => self::edit_url( $post->ID ),
			'preview'    => get_preview_post_link( $post ),
			'built'      => (bool) get_post_meta( $post->ID, '_elementor_data', true ),
			'shortcode'  => sprintf( '[tbfe_template id="%d"]', $post->ID ),
		);
	}
}
