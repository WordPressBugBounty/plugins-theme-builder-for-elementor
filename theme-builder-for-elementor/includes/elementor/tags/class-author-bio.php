<?php
/**
 * Dynamic tag: biography of the author.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use TBFE\Elementor\Context;


defined( 'ABSPATH' ) || exit;

class Author_Bio extends Base_Tag {

	public function get_name() {
		return 'tbfe-author-bio';
	}

	public function get_title() {
		return esc_html__( 'Author Bio', 'theme-builder-for-elementor' );
	}

	public function render() {
		$author = Context::author_id();
		echo $author ? esc_html( get_the_author_meta( 'description', $author ) ) : '';
	}
}
