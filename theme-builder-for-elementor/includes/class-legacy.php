<?php
/**
 * What sites of version 1 still need: the three widget areas "Sidebar #1–3".
 *
 * @package TBFE
 */

namespace TBFE;

defined( 'ABSPATH' ) || exit;

class Legacy {

	public static function init() {
		add_action( 'widgets_init', array( __CLASS__, 'sidebars' ) );
	}

	public static function sidebars() {
		if ( ! get_option( 'tbfe_legacy_site' ) ) {
			return;
		}
		for ( $i = 1; $i <= 3; $i++ ) {
			register_sidebar(
				array(
					/* translators: %d: number of the widget area */
					'name'          => sprintf( esc_html__( 'Theme Builder Sidebar #%d', 'theme-builder-for-elementor' ), $i ),
					'id'            => 'tbfe-sidebar-' . $i,
					'before_widget' => '<div id="%1$s" class="widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<div class="widgettitle"><h3>',
					'after_title'   => '</h3></div>',
				)
			);
		}
	}
}
