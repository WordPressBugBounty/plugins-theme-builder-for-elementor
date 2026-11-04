<?php
/**
 * Dynamic tag: title of the post.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;


defined( 'ABSPATH' ) || exit;

class Post_Title extends Base_Tag {

	public function get_name() {
		return 'tbfe-post-title';
	}

	public function get_title() {
		return esc_html__( 'Post Title', 'theme-builder-for-elementor' );
	}

	public function render() {
		$post = $this->post();
		echo $post ? esc_html( get_the_title( $post ) ) : '';
	}
}
