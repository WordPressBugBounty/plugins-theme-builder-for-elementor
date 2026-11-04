<?php
/**
 * Dynamic tag: link to the comments of the post.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Modules\DynamicTags\Module;


defined( 'ABSPATH' ) || exit;

class Comments_URL extends Base_Data_Tag {

	public function get_name() {
		return 'tbfe-comments-url';
	}

	public function get_title() {
		return esc_html__( 'Comments URL', 'theme-builder-for-elementor' );
	}

	public function get_categories() {
		return array( Module::URL_CATEGORY );
	}

	protected function get_value( array $options = array() ) {
		$post = $this->post();
		return $post ? get_comments_link( $post ) : '';
	}
}
