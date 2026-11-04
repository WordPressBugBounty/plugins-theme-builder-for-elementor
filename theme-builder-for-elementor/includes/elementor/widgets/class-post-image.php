<?php
/**
 * Featured image (tbfe-pro-blog-image of version 1).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;

defined( 'ABSPATH' ) || exit;

class Post_Image extends Base {

	public function get_name() {
		return 'tbfe-pro-blog-image';
	}

	public function get_title() {
		return esc_html__( 'Featured Image', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-featured-image';
	}

	private static function sizes() {
		$sizes = array();
		foreach ( get_intermediate_image_sizes() as $size ) {
			$sizes[ $size ] = ucwords( str_replace( array( '_', '-' ), ' ', $size ) );
		}
		$sizes['full'] = esc_html__( 'Full', 'theme-builder-for-elementor' );
		return $sizes;
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_image', array( 'label' => esc_html__( 'Image', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'size',
			array(
				'label'   => esc_html__( 'Image size', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'large',
				'options' => self::sizes(),
			)
		);
		$this->add_control(
			'link_to',
			array(
				'label'   => esc_html__( 'Link', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none' => esc_html__( 'None', 'theme-builder-for-elementor' ),
					'post' => esc_html__( 'Post', 'theme-builder-for-elementor' ),
					'file' => esc_html__( 'Image file', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'default-image',
			array(
				'label'       => esc_html__( 'Fallback image', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'Shown when the post has no featured image.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
			)
		);
		$this->add_control(
			'image',
			array(
				'label'     => esc_html__( 'Fallback image', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'default-image' => 'yes' ),
			)
		);
		$this->add_align_control( 'align', '{{WRAPPER}} .tbfe-post-image' );
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_image',
			array(
				'label' => esc_html__( 'Image', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'width',
			array(
				'label'      => esc_html__( 'Width', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%', 'px', 'vw' ),
				'range'      => array(
					'%'  => array( 'min' => 1, 'max' => 100 ),
					'px' => array( 'min' => 1, 'max' => 1600 ),
				),
				'selectors'  => array( '{{WRAPPER}} .tbfe-post-image img' => 'width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'space',
			array(
				'label'      => esc_html__( 'Max width', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%', 'px' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-post-image img' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'height',
			array(
				'label'      => esc_html__( 'Height', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array( 'px' => array( 'min' => 50, 'max' => 1000 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-post-image img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;' ),
			)
		);
		$this->start_controls_tabs( 'image_effects' );
		$this->start_controls_tab( 'normal', array( 'label' => esc_html__( 'Normal', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'opacity',
			array(
				'label'     => esc_html__( 'Opacity', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0.1,
						'step' => 0.01,
					),
				),
				'selectors' => array( '{{WRAPPER}} .tbfe-post-image img' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .tbfe-post-image img',
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'hover', array( 'label' => esc_html__( 'Hover', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'opacity_hover',
			array(
				'label'     => esc_html__( 'Opacity', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 1,
						'min'  => 0.1,
						'step' => 0.01,
					),
				),
				'selectors' => array( '{{WRAPPER}} .tbfe-post-image:hover img' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'css_filters_hover',
				'selector' => '{{WRAPPER}} .tbfe-post-image:hover img',
			)
		);
		$this->add_control(
			'background_hover_transition',
			array(
				'label'     => esc_html__( 'Transition (s)', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max'  => 3,
						'step' => 0.1,
					),
				),
				'selectors' => array( '{{WRAPPER}} .tbfe-post-image img' => 'transition-duration: {{SIZE}}s;' ),
			)
		);
		$this->add_control(
			'hover_animation',
			array(
				'label' => esc_html__( 'Hover animation', 'theme-builder-for-elementor' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'image_border',
				'selector'  => '{{WRAPPER}} .tbfe-post-image img',
				'separator' => 'before',
			)
		);
		$this->add_responsive_control(
			'image_border_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-post-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'image_box_shadow',
				'selector' => '{{WRAPPER}} .tbfe-post-image img',
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$post     = $this->post();
		$settings = $this->get_settings_for_display();
		$size     = isset( $settings['size'] ) ? $settings['size'] : 'large';
		if ( ! in_array( $size, get_intermediate_image_sizes(), true ) && 'full' !== $size ) {
			$size = 'large'; // E.g. tbfe-pro-single of version 1, never registered.
		}

		$image_id = $post ? (int) get_post_thumbnail_id( $post ) : 0;
		if ( ! $image_id && ! empty( $settings['default-image'] ) && ! empty( $settings['image']['id'] ) ) {
			$image_id = (int) $settings['image']['id'];
		}
		if ( ! $image_id ) {
			$this->editor_notice( __( 'This post has no featured image.', 'theme-builder-for-elementor' ) );
			return;
		}

		$class = 'tbfe-post-image';
		if ( ! empty( $settings['hover_animation'] ) ) {
			$class .= ' elementor-animation-' . sanitize_html_class( $settings['hover_animation'] );
		}
		$alt  = $post ? the_title_attribute( array( 'echo' => false, 'post' => $post ) ) : '';
		$html = wp_get_attachment_image( $image_id, $size, false, array( 'alt' => get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ? get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : $alt ) );

		$link = isset( $settings['link_to'] ) ? $settings['link_to'] : 'none';
		if ( 'post' === $link && $post ) {
			$html = sprintf( '<a href="%s" aria-hidden="true" tabindex="-1">%s</a>', esc_url( get_permalink( $post ) ), $html );
		} elseif ( 'file' === $link ) {
			$html = sprintf( '<a href="%s">%s</a>', esc_url( wp_get_attachment_url( $image_id ) ), $html );
		}
		echo '<div class="' . esc_attr( $class ) . '">' . \TBFE\Plugin::kses( $html ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with Plugin::kses().
	}
}
