<?php
/**
 * Dynamic tag: excerpt of the post (or the start of its text).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Controls_Manager;


defined( 'ABSPATH' ) || exit;

class Post_Excerpt extends Base_Tag {

	public function get_name() {
		return 'tbfe-post-excerpt';
	}

	public function get_title() {
		return esc_html__( 'Post Excerpt', 'theme-builder-for-elementor' );
	}

	protected function register_controls() {
		$this->add_control(
			'length',
			array(
				'label'   => esc_html__( 'Words', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 25,
				'min'     => 1,
			)
		);
	}

	public function render() {
		$post = $this->post();
		if ( ! $post ) {
			return;
		}
		$length = max( 1, (int) $this->get_settings( 'length' ) );
		$text   = has_excerpt( $post ) ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		echo esc_html( wp_trim_words( $text, $length ) );
	}
}
