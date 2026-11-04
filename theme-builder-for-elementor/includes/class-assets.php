<?php
/**
 * Files of the plugin for the browser: the minified version (.min.css / .min.js) unless SCRIPT_DEBUG is on, and the
 * handles of the widgets that have their own CSS or JS file (only loaded on the pages with that widget).
 *
 * The minified files are made by "npm run build:assets" (bin/minify.js).
 *
 * @package TBFE
 */

namespace TBFE;

defined( 'ABSPATH' ) || exit;

class Assets {

	/**
	 * Widgets with their own files in assets/css/widgets/ and assets/js/widgets/: name => array( css, js ).
	 * The handle is "tbfe-<name>"; the files depend on the base "tbfe-widgets" (CSS) and the core "tbfe-widgets" (JS).
	 */
	const WIDGETS = array(
		'share-buttons'    => array( true, true ),
		'search-toggle'    => array( true, true ),
		'toc'              => array( true, true ),
		'back-to-top'      => array( true, true ),
		'reading-progress' => array( true, true ),
		'advanced-heading' => array( true, true ),
		'slider'           => array( true, true ),
	);

	/**
	 * The file to load: the minified one when it exists (and SCRIPT_DEBUG is off).
	 *
	 * @param string $file Path in the plugin, e.g. assets/js/widgets.js.
	 */
	public static function file( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			return $file;
		}
		$min = preg_replace( '/\.(css|js)$/', '.min.$1', $file );
		return is_readable( TBFE_PATH . $min ) ? $min : $file;
	}

	public static function url( $file ) {
		return TBFE_URL . self::file( $file );
	}

	public static function version( $file ) {
		return Plugin::asset_version( self::file( $file ) );
	}

	/**
	 * Styles of the widgets with their own file (on elementor/frontend/after_register_styles).
	 */
	public static function register_widget_styles() {
		foreach ( self::WIDGETS as $name => $files ) {
			if ( $files[0] ) {
				$file = 'assets/css/widgets/' . $name . '.css';
				wp_register_style( 'tbfe-' . $name, self::url( $file ), array( 'tbfe-widgets' ), self::version( $file ) );
			}
		}
	}

	/**
	 * Scripts of the widgets with their own file (on elementor/frontend/after_register_scripts).
	 */
	public static function register_widget_scripts() {
		foreach ( self::WIDGETS as $name => $files ) {
			if ( $files[1] ) {
				$file = 'assets/js/widgets/' . $name . '.js';
				wp_register_script( 'tbfe-' . $name, self::url( $file ), array( 'tbfe-widgets' ), self::version( $file ), true );
			}
		}
	}
}
