<?php
/**
 * Number of comments with a link (blog-comments of version 1).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

class Post_Comments_Count extends Meta_Base {

	const ICON           = 'fas fa-comments';
	const LABEL_COLOR_ID = 'text_separator_color';

	public function get_name() {
		return 'blog-comments';
	}

	public function get_title() {
		return esc_html__( 'Comments Count', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-comments';
	}

	protected function register_value_controls() {
		// "separator" is the text for closed comments in version 1.
		$this->add_control(
			'separator',
			array(
				'label'   => esc_html__( 'Text when comments are closed', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Off', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'hide_zero',
			array(
				'label'   => esc_html__( 'Hide when there are no comments', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);
	}

	protected function value_html( \WP_Post $post, array $settings ) {
		$count = (int) get_comments_number( $post );
		if ( ! comments_open( $post ) && 0 === $count ) {
			return ( isset( $settings['separator'] ) && '' !== $settings['separator'] ) ? '<span class="tbfe-meta__off">' . esc_html( $settings['separator'] ) . '</span>' : '';
		}
		if ( 0 === $count && ! empty( $settings['hide_zero'] ) ) {
			return '';
		}
		return sprintf(
			'<a href="%s" title="%s">%s</a>',
			esc_url( get_comments_link( $post ) ),
			/* translators: %s: post title */
			esc_attr( sprintf( __( 'Comments on %s', 'theme-builder-for-elementor' ), get_the_title( $post ) ) ),
			esc_html( number_format_i18n( $count ) )
		);
	}
}
