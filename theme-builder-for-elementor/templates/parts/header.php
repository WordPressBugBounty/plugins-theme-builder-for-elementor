<?php
/**
 * Header of the plugin (replaces header.php of a classic theme).
 *
 * @package TBFE
 */

defined( 'ABSPATH' ) || exit;

require __DIR__ . '/document-start.php';

TBFE\Render\Theme_Support::location_markup( 'header' );
