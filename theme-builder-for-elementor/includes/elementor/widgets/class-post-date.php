<?php
/**
 * Post date (blog-date of version 1).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

class Post_Date extends Meta_Base {

	const ICON = 'fas fa-calendar';

	public function get_name() {
		return 'blog-date';
	}

	public function get_title() {
		return esc_html__( 'Post Date', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-date';
	}

	protected function register_value_controls() {
		$this->add_control(
			'date_type',
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
			'date_format',
			array(
				'label'       => esc_html__( 'Format', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => get_option( 'date_format' ),
				'description' => esc_html__( 'PHP date format, empty = the format of the site settings.', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'link',
			array(
				'label'   => esc_html__( 'Link to the post', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);
	}

	protected function register_controls() {
		parent::register_controls();
		$this->update_control( 'text', array( 'default' => esc_html__( 'Published', 'theme-builder-for-elementor' ) ) );
	}

	protected function value_html( \WP_Post $post, array $settings ) {
		$format   = ! empty( $settings['date_format'] ) ? $settings['date_format'] : get_option( 'date_format' );
		$modified = isset( $settings['date_type'] ) && 'modified' === $settings['date_type'];
		$date     = $modified ? get_the_modified_date( $format, $post ) : get_the_date( $format, $post );
		$iso      = $modified ? get_the_modified_date( 'c', $post ) : get_the_date( 'c', $post );
		$html     = sprintf( '<time datetime="%s">%s</time>', esc_attr( $iso ), esc_html( $date ) );
		if ( ! empty( $settings['link'] ) ) {
			$html = sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $post ) ), $html );
		}
		return $html;
	}
}
