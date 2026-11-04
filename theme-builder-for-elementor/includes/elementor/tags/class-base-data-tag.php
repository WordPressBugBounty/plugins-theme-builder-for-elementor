<?php
/**
 * Dynamic tag: base of the URL and image tags of the plugin.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module;
use TBFE\Elementor\Context;
use TBFE\Elementor\Dynamic_Tags;


defined( 'ABSPATH' ) || exit;

abstract class Base_Data_Tag extends Data_Tag {

	public function get_group() {
		return Dynamic_Tags::GROUP;
	}

	public function get_categories() {
		return array( Module::URL_CATEGORY );
	}

	/**
	 * @return \WP_Post|null
	 */
	protected function post() {
		return Context::post();
	}
}
