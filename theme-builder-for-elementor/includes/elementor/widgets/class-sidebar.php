<?php
/**
 * A widget area of WordPress (blog-sidebar of version 1).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Sidebar extends Base {

	const UNIVERSAL = true;

	public function get_name() {
		return 'blog-sidebar';
	}

	public function get_title() {
		return esc_html__( 'Widget Area', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-sidebar';
	}

	protected function register_controls() {
		global $wp_registered_sidebars;
		$options = array( '' => esc_html__( '— Select —', 'theme-builder-for-elementor' ) );
		foreach ( (array) $wp_registered_sidebars as $sidebar ) {
			$options[ $sidebar['id'] ] = $sidebar['name'];
		}

		$this->start_controls_section( 'section_sidebar', array( 'label' => esc_html__( 'Widget area', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'sidebar',
			array(
				'label'       => esc_html__( 'Widget area', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $options,
				'description' => esc_html__( 'Add widgets to the area in Appearance › Widgets.', 'theme-builder-for-elementor' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'text_style',
			array(
				'label' => esc_html__( 'Widget titles', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$title = '{{WRAPPER}} .widgettitle, {{WRAPPER}} .widget-title, {{WRAPPER}} .widgettitle h3, {{WRAPPER}} .wp-block-heading';
		$this->add_align_control( 'align', $title );
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'text_typography',
				'selector' => $title,
			)
		);
		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $title => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'text_bg_color',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .widgettitle, {{WRAPPER}} .widget-title' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'text_border',
				'selector' => '{{WRAPPER}} .widgettitle, {{WRAPPER}} .widget-title',
			)
		);
		$this->add_responsive_control(
			'text_text_padding',
			array(
				'label'      => esc_html__( 'Padding', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .widgettitle, {{WRAPPER}} .widget-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'text_text_margin',
			array(
				'label'      => esc_html__( 'Margin', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .widgettitle, {{WRAPPER}} .widget-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'cats_style',
			array(
				'label' => esc_html__( 'Widgets', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .widget',
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .widget' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'title_color_links',
			array(
				'label'     => esc_html__( 'Link color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .widget a' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'title_color_hover',
			array(
				'label'     => esc_html__( 'Link hover color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .widget a:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'widget_bg_color',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .widget' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'border',
				'selector' => '{{WRAPPER}} .widget',
			)
		);
		$this->add_control(
			'border_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( '{{WRAPPER}} .widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .widget',
			)
		);
		$this->add_responsive_control(
			'text_padding',
			array(
				'label'      => esc_html__( 'Padding', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'text_margin',
			array(
				'label'      => esc_html__( 'Spacing', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .widget' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$sidebar  = isset( $settings['sidebar'] ) ? (string) $settings['sidebar'] : '';
		if ( '' === $sidebar || ! is_active_sidebar( $sidebar ) ) {
			$this->editor_notice( __( 'Choose a widget area that has widgets.', 'theme-builder-for-elementor' ) );
			return;
		}
		echo '<div class="tbfe-sidebar">';
		dynamic_sidebar( $sidebar );
		echo '</div>';
	}
}
