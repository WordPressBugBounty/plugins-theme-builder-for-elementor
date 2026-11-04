<?php
/**
 * Read more button (blog-read-more of version 1).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;

defined( 'ABSPATH' ) || exit;

class Read_More extends Base {

	public function get_name() {
		return 'blog-read-more';
	}

	public function get_title() {
		return esc_html__( 'Read More', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-button';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_button', array( 'label' => esc_html__( 'Button', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'button_type',
			array(
				'label'        => esc_html__( 'Type', 'theme-builder-for-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => '',
				'options'      => array(
					''        => esc_html__( 'Default', 'theme-builder-for-elementor' ),
					'info'    => esc_html__( 'Info', 'theme-builder-for-elementor' ),
					'success' => esc_html__( 'Success', 'theme-builder-for-elementor' ),
					'warning' => esc_html__( 'Warning', 'theme-builder-for-elementor' ),
					'danger'  => esc_html__( 'Danger', 'theme-builder-for-elementor' ),
				),
				'prefix_class' => 'tbfe-button--',
			)
		);
		$this->add_control(
			'text',
			array(
				'label'   => esc_html__( 'Text', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Read more', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_align_control( 'align', '{{WRAPPER}} .tbfe-button-wrap' );
		$this->add_control(
			'size',
			array(
				'label'   => esc_html__( 'Size', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'sm',
				'options' => array(
					'xs' => esc_html__( 'Extra small', 'theme-builder-for-elementor' ),
					'sm' => esc_html__( 'Small', 'theme-builder-for-elementor' ),
					'md' => esc_html__( 'Medium', 'theme-builder-for-elementor' ),
					'lg' => esc_html__( 'Large', 'theme-builder-for-elementor' ),
					'xl' => esc_html__( 'Extra large', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'icon',
			array(
				'label' => esc_html__( 'Icon', 'theme-builder-for-elementor' ),
				'type'  => Controls_Manager::ICONS,
			)
		);
		$this->add_control(
			'icon_align',
			array(
				'label'     => esc_html__( 'Icon position', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'left',
				'options'   => array(
					'left'  => esc_html__( 'Before', 'theme-builder-for-elementor' ),
					'right' => esc_html__( 'After', 'theme-builder-for-elementor' ),
				),
				'condition' => array( 'icon[value]!' => '' ),
			)
		);
		$this->add_control(
			'icon_indent',
			array(
				'label'     => esc_html__( 'Icon spacing', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'max' => 50 ) ),
				'selectors' => array( '{{WRAPPER}} .tbfe-button' => 'gap: {{SIZE}}{{UNIT}};' ),
				'condition' => array( 'icon[value]!' => '' ),
			)
		);
		$this->add_control(
			'view',
			array(
				'type'    => Controls_Manager::HIDDEN,
				'default' => 'traditional',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Button', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .tbfe-button',
			)
		);
		$this->start_controls_tabs( 'button_tabs' );
		$this->start_controls_tab( 'button_normal', array( 'label' => esc_html__( 'Normal', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'button_text_color',
			array(
				'label'     => esc_html__( 'Text color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-button' => 'color: {{VALUE}}; fill: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'background_color',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-button' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'button_hover', array( 'label' => esc_html__( 'Hover', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'hover_color',
			array(
				'label'     => esc_html__( 'Text color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-button:hover, {{WRAPPER}} .tbfe-button:focus' => 'color: {{VALUE}}; fill: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'button_background_hover_color',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-button:hover, {{WRAPPER}} .tbfe-button:focus' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'button_hover_border_color',
			array(
				'label'     => esc_html__( 'Border color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-button:hover, {{WRAPPER}} .tbfe-button:focus' => 'border-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'border',
				'selector'  => '{{WRAPPER}} .tbfe-button',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'border_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .tbfe-button',
			)
		);
		$this->add_responsive_control(
			'text_padding',
			array(
				'label'      => esc_html__( 'Padding', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
				'separator'  => 'before',
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$post     = $this->post();
		$settings = $this->get_settings_for_display();
		$size     = isset( $settings['size'] ) && in_array( $settings['size'], array( 'xs', 'sm', 'md', 'lg', 'xl' ), true ) ? $settings['size'] : 'sm';
		$after    = isset( $settings['icon_align'] ) && 'right' === $settings['icon_align'];
		$url      = $post ? get_permalink( $post ) : '#';
		$label    = $post ? sprintf( /* translators: %s: post title */ __( 'Read more about %s', 'theme-builder-for-elementor' ), get_the_title( $post ) ) : '';

		$icon = '';
		if ( ! empty( $settings['icon']['value'] ) ) {
			ob_start();
			Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) );
			$icon = '<span class="tbfe-button__icon">' . ob_get_clean() . '</span>';
		}
		$text = '<span class="tbfe-button__text">' . esc_html( isset( $settings['text'] ) ? $settings['text'] : '' ) . '</span>';

		printf(
			'<div class="tbfe-button-wrap"><a class="tbfe-button tbfe-button--size-%1$s" href="%2$s" aria-label="%3$s">%4$s</a></div>',
			esc_attr( $size ),
			esc_url( $url ),
			esc_attr( $label ),
			$after ? $text . $icon : $icon . $text // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);
	}
}
