<?php
/**
 * Shortcodes: [tbfe_template id="123"] and [elementor-template id="123"] of version 1.
 *
 * Only published templates of the plugin and of the Elementor library can be shown.
 *
 * @package TBFE
 */

namespace TBFE;

use TBFE\Templates\Post_Type;

defined( 'ABSPATH' ) || exit;

class Shortcodes {

	/** @var int[] */
	private static $stack = array();

	public static function init() {
		add_shortcode( 'tbfe_template', array( __CLASS__, 'render' ) );
		add_action(
			'init',
			static function () {
				if ( ! shortcode_exists( 'elementor-template' ) ) {
					add_shortcode( 'elementor-template', array( __CLASS__, 'render' ) );
				}
			},
			20
		);
	}

	public static function render( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts );
		$id   = absint( $atts['id'] );
		if ( ! $id || in_array( $id, self::$stack, true ) ) {
			return '';
		}
		$post = get_post( $id );
		if ( ! $post || 'publish' !== $post->post_status || ! in_array( $post->post_type, array( Post_Type::NAME, 'elementor_library' ), true ) ) {
			return '';
		}
		self::$stack[] = $id;
		$html          = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, true );
		array_pop( self::$stack );
		return $html;
	}
}
