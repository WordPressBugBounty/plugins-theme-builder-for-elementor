<?php
/**
 * Dynamic tag: base of the text tags of the plugin.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use TBFE\Elementor\Context;
use TBFE\Elementor\Dynamic_Tags;


defined( 'ABSPATH' ) || exit;

abstract class Base_Tag extends Tag {

	public function get_group() {
		return Dynamic_Tags::GROUP;
	}

	public function get_categories() {
		return array( Module::TEXT_CATEGORY );
	}

	/**
	 * @return \WP_Post|null
	 */
	protected function post() {
		return Context::post();
	}
}
