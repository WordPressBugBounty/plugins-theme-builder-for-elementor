<?php
/**
 * Dynamic tag: name of the author (of the post or the author archive).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use TBFE\Elementor\Context;


defined( 'ABSPATH' ) || exit;

class Author_Name extends Base_Tag {

	public function get_name() {
		return 'tbfe-author-name';
	}

	public function get_title() {
		return esc_html__( 'Author Name', 'theme-builder-for-elementor' );
	}

	public function render() {
		$author = Context::author_id();
		echo $author ? esc_html( get_the_author_meta( 'display_name', $author ) ) : '';
	}
}
