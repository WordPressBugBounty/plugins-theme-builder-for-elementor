<?php
/**
 * Elementor document: single post or page.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Documents;

defined( 'ABSPATH' ) || exit;

class Single extends Theme_Document {
	const TBFE_TYPE        = 'single';
	const HAS_PREVIEW_POST = true;
}
