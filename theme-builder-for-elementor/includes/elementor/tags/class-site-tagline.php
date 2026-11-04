<?php
/**
 * Dynamic tag: tagline of the site.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;


defined( 'ABSPATH' ) || exit;

class Site_Tagline extends Base_Tag {

	public function get_name() {
		return 'tbfe-site-tagline';
	}

	public function get_title() {
		return esc_html__( 'Site Tagline', 'theme-builder-for-elementor' );
	}

	public function render() {
		echo esc_html( get_bloginfo( 'description' ) );
	}
}
