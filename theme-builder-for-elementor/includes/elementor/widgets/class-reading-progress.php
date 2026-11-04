<?php
/**
 * Reading Progress: a thin bar on the top (or bottom) of the screen that fills up while the visitor reads.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

class Reading_Progress extends Base {

	const UNIVERSAL = true;

	public function get_name() {
		return 'tbfe-reading-progress';
	}

	public function get_title() {
		return esc_html__( 'Reading Progress', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-progress-tracker';
	}

	public function get_keywords() {
		return array( 'progress', 'reading', 'scroll', 'bar' );
	}

	public function get_style_depends(): array {
		return array( 'tbfe-widgets', 'tbfe-reading-progress' );
	}

	public function get_script_depends() {
		return array( 'tbfe-reading-progress' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_bar', array( 'label' => esc_html__( 'Progress bar', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'The bar is fixed to the edge of the screen. Put it into the Single template, so it follows the reading of a post.', 'theme-builder-for-elementor' ),
				'content_classes' => 'elementor-descriptor',
			)
		);
		$this->add_control(
			'position',
			array(
				'label'   => esc_html__( 'Position', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'top',
				'options' => array(
					'top'    => esc_html__( 'Top of the screen', 'theme-builder-for-elementor' ),
					'bottom' => esc_html__( 'Bottom of the screen', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'target',
			array(
				'label'       => esc_html__( 'Measure', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'content',
				'options'     => array(
					'content' => esc_html__( 'The text of the post', 'theme-builder-for-elementor' ),
					'page'    => esc_html__( 'The whole page', 'theme-builder-for-elementor' ),
				),
				'description' => esc_html__( 'With "The text of the post" the bar is full at the end of the article, before the comments and the footer.', 'theme-builder-for-elementor' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Progress bar', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'height',
			array(
				'label'      => esc_html__( 'Height', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 1, 'max' => 20 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-progress' => 'height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-progress__bar' => 'background: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'track',
			array(
				'label'     => esc_html__( 'Track', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-progress' => 'background: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		printf(
			'<div class="tbfe-progress tbfe-progress--%1$s%2$s" data-target="%3$s" role="progressbar" aria-label="%4$s" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="tbfe-progress__bar"></div></div>',
			'bottom' === $settings['position'] ? 'bottom' : 'top',
			$this->is_editor() ? ' is-editor' : '',
			'page' === $settings['target'] ? 'page' : 'content',
			esc_attr__( 'Reading progress', 'theme-builder-for-elementor' )
		);
	}
}
