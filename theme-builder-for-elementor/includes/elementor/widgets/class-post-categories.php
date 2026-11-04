<?php
/**
 * Post categories (blog-categories of version 1).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Post_Categories extends Terms_Base {

	const ICON           = 'fas fa-folder';
	const TAXONOMY       = 'category';
	const LABEL_COLOR_ID = 'text_color';

	public function get_name() {
		return 'blog-categories';
	}

	public function get_title() {
		return esc_html__( 'Post Categories', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-folder';
	}

	protected function register_controls() {
		parent::register_controls();
		$this->update_control( 'text', array( 'default' => esc_html__( 'Categories:', 'theme-builder-for-elementor' ) ) );
	}
}
