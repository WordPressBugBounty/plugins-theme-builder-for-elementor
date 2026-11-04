<?php
/**
 * Dynamic tag: link to the post.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Modules\DynamicTags\Module;


defined( 'ABSPATH' ) || exit;

class Post_URL extends Base_Data_Tag {

	public function get_name() {
		return 'tbfe-post-url';
	}

	public function get_title() {
		return esc_html__( 'Post URL', 'theme-builder-for-elementor' );
	}

	public function get_categories() {
		return array( Module::URL_CATEGORY );
	}

	protected function get_value( array $options = array() ) {
		$post = $this->post();
		return $post ? get_permalink( $post ) : '';
	}
}
