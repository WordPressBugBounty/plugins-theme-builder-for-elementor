<?php
/**
 * The demo library inside the Elementor editor: a button next to "Add Template" in every "add section" area opens
 * the library, and the chosen section, template or demo page is inserted at that place.
 *
 * The content is prepared by the site (REST library/content: new element IDs, images in the media library) and
 * inserted with Elementor's own import command, so it can be undone with Ctrl+Z.
 *
 * @package TBFE
 */

namespace TBFE\Elementor;

use TBFE\Admin\Rest;
use TBFE\Pro;
use TBFE\Settings;
use TBFE\Templates\Types;

defined( 'ABSPATH' ) || exit;

class Editor_Library {

	public static function init() {
		add_action( 'elementor/editor/after_enqueue_scripts', array( __CLASS__, 'scripts' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'styles' ) );
		add_action( 'elementor/preview/enqueue_styles', array( __CLASS__, 'preview_styles' ) );
	}

	private static function enabled() {
		return Settings::get( 'library' ) && Rest::can_insert();
	}

	public static function scripts() {
		if ( ! self::enabled() ) {
			return;
		}
		wp_enqueue_script( 'tbfe-editor-library', \TBFE\Assets::url( 'assets/js/editor-library.js' ), array( 'jquery', 'backbone' ), \TBFE\Assets::version( 'assets/js/editor-library.js' ), true );

		$types = array();
		foreach ( Types::all() as $slug => $type ) {
			$types[ $slug ] = $type['plural'];
		}
		wp_add_inline_script(
			'tbfe-editor-library',
			'window.tbfeEditorLibrary = ' . wp_json_encode(
				array(
					'rest'       => esc_url_raw( rest_url( Rest::NS . '/' ) ),
					'nonce'      => wp_create_nonce( 'wp_rest' ),
					'pro'        => Pro::is_active(),
					'libraryUrl' => admin_url( 'admin.php?page=tbfe#/library' ),
					'types'      => $types,
					'i18n'       => array(
						'button'      => __( 'Theme Builder Library', 'theme-builder-for-elementor' ),
						'title'       => __( 'Theme Builder Library', 'theme-builder-for-elementor' ),
						'sections'    => __( 'Sections', 'theme-builder-for-elementor' ),
						'templates'   => __( 'Templates', 'theme-builder-for-elementor' ),
						'pages'       => __( 'Pages', 'theme-builder-for-elementor' ),
						'allTypes'    => __( 'All types', 'theme-builder-for-elementor' ),
						'search'      => __( 'Search demos', 'theme-builder-for-elementor' ),
						'insert'      => __( 'Insert', 'theme-builder-for-elementor' ),
						'inserting'   => __( 'Inserting…', 'theme-builder-for-elementor' ),
						'preview'     => __( 'Preview', 'theme-builder-for-elementor' ),
						'close'       => __( 'Close', 'theme-builder-for-elementor' ),
						'loading'     => __( 'Loading the library…', 'theme-builder-for-elementor' ),
						'empty'       => __( 'Nothing found.', 'theme-builder-for-elementor' ),
						'pro'         => __( 'PRO', 'theme-builder-for-elementor' ),
						'proLocked'   => __( 'Needs Theme Builder PRO', 'theme-builder-for-elementor' ),
						'inserted'    => __( 'Inserted. Ctrl+Z takes it back.', 'theme-builder-for-elementor' ),
						'manage'      => __( 'Open the Template Library', 'theme-builder-for-elementor' ),
						'imagesNote'  => __( 'The images are copied into your media library.', 'theme-builder-for-elementor' ),
						'siteColors'  => __( 'Use my site colors', 'theme-builder-for-elementor' ),
						'siteHelp'    => __( 'The main colors of the demo become the global colors of your site (Site Settings › Global Colors), linked to them.', 'theme-builder-for-elementor' ),
						'loadError'   => __( 'The library could not be loaded.', 'theme-builder-for-elementor' ),
						'insertError' => __( 'The demo could not be inserted.', 'theme-builder-for-elementor' ),
					),
				)
			) . ';',
			'before'
		);
	}

	public static function styles() {
		if ( self::enabled() ) {
			wp_enqueue_style( 'tbfe-editor-library', \TBFE\Assets::url( 'assets/css/editor-library.css' ), array(), \TBFE\Assets::version( 'assets/css/editor-library.css' ) );
		}
	}

	/**
	 * The button lives in the preview frame (the "add section" area).
	 */
	public static function preview_styles() {
		if ( self::enabled() ) {
			wp_enqueue_style( 'tbfe-editor-library-preview', \TBFE\Assets::url( 'assets/css/editor-library-preview.css' ), array(), \TBFE\Assets::version( 'assets/css/editor-library-preview.css' ) );
		}
	}
}
