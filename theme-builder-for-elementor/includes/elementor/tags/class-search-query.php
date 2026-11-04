<?php
/**
 * Dynamic tag: the searched words.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;


defined( 'ABSPATH' ) || exit;

class Search_Query extends Base_Tag {

	public function get_name() {
		return 'tbfe-search-query';
	}

	public function get_title() {
		return esc_html__( 'Search Query', 'theme-builder-for-elementor' );
	}

	public function render() {
		echo esc_html( get_search_query() );
	}
}
