<?php
/**
 * Dynamic tag: description of the current category, tag or author.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use TBFE\Elementor\Context;


defined( 'ABSPATH' ) || exit;

class Archive_Description extends Base_Tag {

	public function get_name() {
		return 'tbfe-archive-description';
	}

	public function get_title() {
		return esc_html__( 'Archive Description', 'theme-builder-for-elementor' );
	}

	public function render() {
		list( , $description ) = Context::archive_texts();
		echo wp_kses_post( $description );
	}
}
