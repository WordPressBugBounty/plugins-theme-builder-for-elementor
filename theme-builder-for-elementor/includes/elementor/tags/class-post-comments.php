<?php
/**
 * Dynamic tag: number of comments of the post.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Controls_Manager;


defined( 'ABSPATH' ) || exit;

class Post_Comments extends Base_Tag {

	public function get_name() {
		return 'tbfe-post-comments';
	}

	public function get_title() {
		return esc_html__( 'Comments Count', 'theme-builder-for-elementor' );
	}

	protected function register_controls() {
		$this->add_control(
			'none',
			array(
				'label'   => esc_html__( 'No comments', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'No comments', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'one',
			array(
				'label'   => esc_html__( 'One comment', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '1 comment', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'many',
			array(
				'label'       => esc_html__( 'More comments', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				/* translators: %s: number of comments */
				'default'     => esc_html__( '%s comments', 'theme-builder-for-elementor' ),
				/* translators: Help of the field above; keep %s, it stands for the value. */
				'description' => esc_html__( '%s is replaced with the number.', 'theme-builder-for-elementor' ),
			)
		);
	}

	public function render() {
		$post = $this->post();
		if ( ! $post ) {
			return;
		}
		$count = (int) get_comments_number( $post );
		$key   = 0 === $count ? 'none' : ( 1 === $count ? 'one' : 'many' );
		echo esc_html( str_replace( '%s', number_format_i18n( $count ), (string) $this->get_settings( $key ) ) );
	}
}
