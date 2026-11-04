<?php
/**
 * Dynamic tag: link to the current archive.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Modules\DynamicTags\Module;
use TBFE\Elementor\Context;


defined( 'ABSPATH' ) || exit;

class Archive_URL extends Base_Data_Tag {

	public function get_name() {
		return 'tbfe-archive-url';
	}

	public function get_title() {
		return esc_html__( 'Archive URL', 'theme-builder-for-elementor' );
	}

	public function get_categories() {
		return array( Module::URL_CATEGORY );
	}

	protected function get_value( array $options = array() ) {
		return Context::archive_url();
	}
}
