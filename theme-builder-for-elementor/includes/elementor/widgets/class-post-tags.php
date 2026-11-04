<?php
/**
 * Post tags (blog-tags of version 1).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

defined( 'ABSPATH' ) || exit;

class Post_Tags extends Terms_Base {

	const ICON           = 'fas fa-tags';
	const TAXONOMY       = 'post_tag';
	const LABEL_COLOR_ID = 'text_separator_color';

	public function get_name() {
		return 'blog-tags';
	}

	public function get_title() {
		return esc_html__( 'Post Tags', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-tags';
	}

	protected function register_controls() {
		parent::register_controls();
		$this->update_control( 'text', array( 'default' => esc_html__( 'Tags:', 'theme-builder-for-elementor' ) ) );
	}
}
