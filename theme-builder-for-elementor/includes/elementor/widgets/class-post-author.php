<?php
/**
 * Post author (blog-author of version 1).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

class Post_Author extends Meta_Base {

	const ICON = 'fas fa-user';

	public function get_name() {
		return 'blog-author';
	}

	public function get_title() {
		return esc_html__( 'Post Author', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	protected function register_value_controls() {
		$this->add_control(
			'link',
			array(
				'label'   => esc_html__( 'Link to author archive', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);
	}

	protected function register_controls() {
		parent::register_controls();
		$this->update_control( 'text', array( 'default' => esc_html__( 'Author:', 'theme-builder-for-elementor' ) ) );
	}

	protected function value_html( \WP_Post $post, array $settings ) {
		$name = get_the_author_meta( 'display_name', $post->post_author );
		if ( '' === $name ) {
			return '';
		}
		if ( empty( $settings['link'] ) ) {
			return esc_html( $name );
		}
		return sprintf( '<a href="%s" rel="author">%s</a>', esc_url( get_author_posts_url( $post->post_author ) ), esc_html( $name ) );
	}
}
