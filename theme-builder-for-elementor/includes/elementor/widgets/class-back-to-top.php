<?php
/**
 * Back to Top: a button in the corner of the screen that appears after scrolling and takes the visitor up.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;

defined( 'ABSPATH' ) || exit;

class Back_To_Top extends Base {

	const UNIVERSAL = true;

	public function get_name() {
		return 'tbfe-back-to-top';
	}

	public function get_title() {
		return esc_html__( 'Back to Top', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-arrow-up';
	}

	public function get_keywords() {
		return array( 'top', 'scroll', 'up', 'button' );
	}

	public function get_style_depends(): array {
		return array( 'tbfe-widgets', 'tbfe-back-to-top' );
	}

	public function get_script_depends() {
		return array( 'tbfe-back-to-top' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_button', array( 'label' => esc_html__( 'Button', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'The button floats in the corner of the screen. Put it into the footer, so it is on every page.', 'theme-builder-for-elementor' ),
				'content_classes' => 'elementor-descriptor',
			)
		);
		$this->add_control(
			'icon',
			array(
				'label'   => esc_html__( 'Icon', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-arrow-up',
					'library' => 'fa-solid',
				),
			)
		);
		$this->add_control(
			'position',
			array(
				'label'   => esc_html__( 'Position', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'right',
				'options' => array(
					'right' => esc_html__( 'Bottom right', 'theme-builder-for-elementor' ),
					'left'  => esc_html__( 'Bottom left', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'show_after',
			array(
				'label'   => esc_html__( 'Show after scrolling (px)', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 400,
				'min'     => 0,
			)
		);
		$this->add_control(
			'progress',
			array(
				'label'       => esc_html__( 'Show the reading progress', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'A ring around the button fills up while the page scrolls.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
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
		$this->add_responsive_control(
			'size',
			array(
				'label'      => esc_html__( 'Size', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 28, 'max' => 90 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-to-top' => '--tbfe-top-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'distance',
			array(
				'label'      => esc_html__( 'Distance from the edge', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 120 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-to-top' => '--tbfe-top-distance: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'radius',
			array(
				'label'      => esc_html__( 'Border radius', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-to-top' => 'border-radius: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'progress' => '' ),
			)
		);
		$this->start_controls_tabs( 'colors' );
		$this->start_controls_tab( 'colors_normal', array( 'label' => esc_html__( 'Normal', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'bg',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-to-top' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'color',
			array(
				'label'     => esc_html__( 'Icon', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-to-top' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'ring',
			array(
				'label'     => esc_html__( 'Progress ring', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-to-top' => '--tbfe-top-ring: {{VALUE}};' ),
				'condition' => array( 'progress' => 'yes' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'colors_hover', array( 'label' => esc_html__( 'Hover', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'bg_hover',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-to-top:hover, {{WRAPPER}} .tbfe-to-top:focus-visible' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'color_hover',
			array(
				'label'     => esc_html__( 'Icon', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-to-top:hover, {{WRAPPER}} .tbfe-to-top:focus-visible' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => 'shadow',
				'selector'  => '{{WRAPPER}} .tbfe-to-top',
				'separator' => 'before',
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$icon     = '';
		if ( ! empty( $settings['icon']['value'] ) ) {
			ob_start();
			Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) );
			$icon = ob_get_clean();
		}
		$ring = ! empty( $settings['progress'] ) ? '<svg class="tbfe-to-top__ring" viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="18" pathLength="100"></circle></svg>' : '';
		printf(
			'<button type="button" class="tbfe-to-top tbfe-to-top--%1$s%2$s%3$s" data-show-after="%4$d" aria-label="%5$s">%6$s<span class="tbfe-to-top__icon" aria-hidden="true">%7$s</span></button>',
			'left' === $settings['position'] ? 'left' : 'right',
			$ring ? ' tbfe-to-top--progress' : '',
			$this->is_editor() ? ' is-visible' : '',
			absint( $settings['show_after'] ),
			esc_attr__( 'Back to top', 'theme-builder-for-elementor' ),
			\TBFE\Plugin::kses( $ring ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with Plugin::kses().
			\TBFE\Plugin::kses( $icon ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with Plugin::kses().
		);
	}
}
