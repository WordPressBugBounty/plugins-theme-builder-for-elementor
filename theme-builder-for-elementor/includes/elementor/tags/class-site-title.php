<?php
/**
 * Dynamic tag: title of the site.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;


defined( 'ABSPATH' ) || exit;

class Site_Title extends Base_Tag {

	public function get_name() {
		return 'tbfe-site-title';
	}

	public function get_title() {
		return esc_html__( 'Site Title', 'theme-builder-for-elementor' );
	}

	public function render() {
		echo esc_html( get_bloginfo( 'name' ) );
	}
}
