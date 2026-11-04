<?php
/**
 * Dynamic tag: link to the home page.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Modules\DynamicTags\Module;


defined( 'ABSPATH' ) || exit;

class Site_URL extends Base_Data_Tag {

	public function get_name() {
		return 'tbfe-site-url';
	}

	public function get_title() {
		return esc_html__( 'Site URL', 'theme-builder-for-elementor' );
	}

	public function get_categories() {
		return array( Module::URL_CATEGORY );
	}

	protected function get_value( array $options = array() ) {
		return home_url( '/' );
	}
}
