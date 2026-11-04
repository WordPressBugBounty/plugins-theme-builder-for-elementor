<?php
/**
 * Class autoloader: TBFE\Render\Location_Resolver → includes/render/class-location-resolver.php
 *
 * @package TBFE
 */

namespace TBFE;

defined( 'ABSPATH' ) || exit;

class Autoloader {

	public static function register() {
		spl_autoload_register( array( __CLASS__, 'load' ) );
	}

	public static function load( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ . '\\' ) ) {
			return;
		}
		$parts = explode( '\\', substr( $class, strlen( __NAMESPACE__ ) + 1 ) );
		$name  = array_pop( $parts );
		$dir   = $parts ? strtolower( str_replace( '_', '-', implode( '/', $parts ) ) ) . '/' : '';
		$file  = TBFE_PATH . 'includes/' . $dir . 'class-' . strtolower( str_replace( '_', '-', $name ) ) . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
