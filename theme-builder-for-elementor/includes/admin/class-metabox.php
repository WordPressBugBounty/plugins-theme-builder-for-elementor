<?php
/**
 * Per-post choice of the header, footer and single template (meta box in the post editor).
 *
 * @package TBFE
 */

namespace TBFE\Admin;

use TBFE\Conditions\Conditions;
use TBFE\Conditions\Resolver;
use TBFE\Templates\Post_Type;
use TBFE\Templates\Template;

defined( 'ABSPATH' ) || exit;

class Metabox {

	const NONCE = 'tbfe_overrides_nonce';

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add' ) );
		add_action( 'save_post', array( __CLASS__, 'save' ), 10, 2 );
	}

	public static function add( $post_type ) {
		if ( ! array_key_exists( $post_type, Conditions::post_types() ) ) {
			return;
		}
		add_meta_box( 'tbfe-overrides', __( 'Theme Builder', 'theme-builder-for-elementor' ), array( __CLASS__, 'render' ), $post_type, 'side', 'default' );
	}

	private static function templates( $type ) {
		return get_posts(
			array(
				'post_type'      => Post_Type::NAME,
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'meta_key'       => Template::META_TYPE, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $type, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
	}

	public static function render( $post ) {
		wp_nonce_field( 'tbfe_overrides_' . $post->ID, self::NONCE );
		$fields = array(
			'header' => __( 'Header', 'theme-builder-for-elementor' ),
			'single' => __( 'Layout (Single template)', 'theme-builder-for-elementor' ),
			'footer' => __( 'Footer', 'theme-builder-for-elementor' ),
		);
		echo '<p class="description">' . esc_html__( 'By default the templates follow their display conditions. Choose another one for this page only.', 'theme-builder-for-elementor' ) . '</p>';
		foreach ( $fields as $location => $label ) {
			$value = Resolver::override( $post->ID, $location );
			printf( '<p><label for="tbfe-%1$s" style="display:block;font-weight:600;margin-bottom:4px">%2$s</label>', esc_attr( $location ), esc_html( $label ) );
			// Border-box: the padding and border of the select stay inside the narrow sidebar of the block editor.
			printf( '<select id="tbfe-%1$s" name="tbfe_overrides[%1$s]" style="box-sizing:border-box;width:100%%;max-width:100%%;min-width:0">', esc_attr( $location ) );
			printf( '<option value="0"%s>%s</option>', selected( 0, $value, false ), esc_html__( 'By display conditions', 'theme-builder-for-elementor' ) );
			printf( '<option value="-1"%s>%s</option>', selected( -1, $value, false ), esc_html__( 'None (theme default)', 'theme-builder-for-elementor' ) );
			foreach ( self::templates( $location ) as $template ) {
				printf( '<option value="%d"%s>%s</option>', (int) $template->ID, selected( $template->ID, $value, false ), esc_html( get_the_title( $template ) ) );
			}
			echo '</select></p>';
		}
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ], $_POST['tbfe_overrides'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE ] ) ), 'tbfe_overrides_' . $post_id ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$input = array_map( 'intval', (array) wp_unslash( $_POST['tbfe_overrides'] ) );
		$out   = array();
		foreach ( array( 'header', 'single', 'footer' ) as $location ) {
			$value = isset( $input[ $location ] ) ? $input[ $location ] : 0;
			if ( -1 === $value ) {
				$out[ $location ] = -1;
			} elseif ( $value > 0 && Template::is_template( $value ) && Template::type( $value ) === $location ) {
				$out[ $location ] = $value;
			}
		}
		if ( $out ) {
			update_post_meta( $post_id, Resolver::META_OVERRIDES, $out );
		} else {
			delete_post_meta( $post_id, Resolver::META_OVERRIDES );
		}
	}
}
