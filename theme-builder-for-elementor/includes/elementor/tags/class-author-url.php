<?php
/**
 * Dynamic tag: link to the posts of the author.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Modules\DynamicTags\Module;
use TBFE\Elementor\Context;


defined( 'ABSPATH' ) || exit;

class Author_URL extends Base_Data_Tag {

	public function get_name() {
		return 'tbfe-author-url';
	}

	public function get_title() {
		return esc_html__( 'Author URL', 'theme-builder-for-elementor' );
	}

	public function get_categories() {
		return array( Module::URL_CATEGORY );
	}

	protected function get_value( array $options = array() ) {
		$author = Context::author_id();
		return $author ? get_author_posts_url( $author ) : '';
	}
}
