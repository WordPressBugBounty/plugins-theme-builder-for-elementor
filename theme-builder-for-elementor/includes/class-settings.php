<?php
/**
 * Global plugin settings (option tbfe_settings).
 *
 * @package TBFE
 */

namespace TBFE;

defined( 'ABSPATH' ) || exit;

class Settings {

	const OPTION = 'tbfe_settings';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	public static function defaults() {
		return array(
			// Single templates are not used for posts and pages that are built with Elementor themselves.
			'skip_elementor_built' => true,
			// Width of the classic archive layout (archive templates repeated for each post).
			'container_width'      => 1140,
			// The demo library in the administration.
			'library'              => true,
		);
	}

	public static function register() {
		register_setting(
			'tbfe',
			self::OPTION,
			array(
				'type'              => 'object',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	public static function all() {
		$saved = get_option( self::OPTION, array() );
		return array_merge( self::defaults(), is_array( $saved ) ? $saved : array() );
	}

	public static function get( $key ) {
		$all = self::all();
		return isset( $all[ $key ] ) ? $all[ $key ] : null;
	}

	public static function sanitize( $input ) {
		$input = is_array( $input ) ? $input : array();
		$out   = self::defaults();

		$out['skip_elementor_built'] = ! empty( $input['skip_elementor_built'] );
		$out['library']              = ! empty( $input['library'] );
		if ( isset( $input['container_width'] ) ) {
			$out['container_width'] = max( 320, min( 3000, absint( $input['container_width'] ) ) );
		}
		return $out;
	}

	public static function update( array $values ) {
		$merged = self::sanitize( array_merge( self::all(), $values ) );
		update_option( self::OPTION, $merged );
		return $merged;
	}
}
