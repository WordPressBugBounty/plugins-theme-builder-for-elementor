<?php
/**
 * Dynamic tag: date of the post.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Controls_Manager;


defined( 'ABSPATH' ) || exit;

class Post_Date extends Base_Tag {

	public function get_name() {
		return 'tbfe-post-date';
	}

	public function get_title() {
		return esc_html__( 'Post Date', 'theme-builder-for-elementor' );
	}

	protected function register_controls() {
		$this->add_control(
			'type',
			array(
				'label'   => esc_html__( 'Date', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'published',
				'options' => array(
					'published' => esc_html__( 'Published', 'theme-builder-for-elementor' ),
					'modified'  => esc_html__( 'Last modified', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'format',
			array(
				'label'       => esc_html__( 'Format', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => get_option( 'date_format' ),
			)
		);
	}

	public function render() {
		$post = $this->post();
		if ( ! $post ) {
			return;
		}
		$format = (string) $this->get_settings( 'format' );
		$format = '' !== $format ? $format : get_option( 'date_format' );
		echo esc_html( 'modified' === $this->get_settings( 'type' ) ? get_the_modified_date( $format, $post ) : get_the_date( $format, $post ) );
	}
}
