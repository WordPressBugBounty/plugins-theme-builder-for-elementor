<?php
/**
 * Footer of the plugin (replaces footer.php of a classic theme).
 *
 * @package TBFE
 */

defined( 'ABSPATH' ) || exit;

TBFE\Render\Theme_Support::location_markup( 'footer' );

require __DIR__ . '/document-end.php';
