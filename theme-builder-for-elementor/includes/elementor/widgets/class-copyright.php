<?php
/**
 * Copyright text with the current year: {year}, {site}, {url}.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Copyright extends Base {

	public function get_name() {
		return 'tbfe-copyright';
	}

	public function get_title() {
		return esc_html__( 'Copyright', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-footer';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_text', array( 'label' => esc_html__( 'Copyright', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'text',
			array(
				'label'       => esc_html__( 'Text', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => '© {year} {site}',
				'description' => esc_html__( 'Placeholders: {year} current year, {site} site title, {url} home page link. Simple HTML is allowed.', 'theme-builder-for-elementor' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Text', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_align_control( 'align', '{{WRAPPER}} .tbfe-copyright' );
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .tbfe-copyright',
			)
		);
		$this->add_control(
			'color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-copyright' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'link_color',
			array(
				'label'     => esc_html__( 'Link color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-copyright a' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$text     = wp_kses_post( (string) $settings['text'] );
		$text     = strtr(
			$text,
			array(
				'{year}' => esc_html( wp_date( 'Y' ) ),
				'{site}' => esc_html( get_bloginfo( 'name' ) ),
				'{url}'  => sprintf( '<a href="%s">%s</a>', esc_url( home_url( '/' ) ), esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ) ),
			)
		);
		echo '<div class="tbfe-copyright">' . wp_kses_post( $text ) . '</div>';
	}
}
