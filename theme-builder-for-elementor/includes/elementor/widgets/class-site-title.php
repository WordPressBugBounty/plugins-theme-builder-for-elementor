<?php
/**
 * Site title and tagline.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Site_Title extends Base {

	public function get_name() {
		return 'tbfe-site-title';
	}

	public function get_title() {
		return esc_html__( 'Site Title', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-site-title';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_title', array( 'label' => esc_html__( 'Site title', 'theme-builder-for-elementor' ) ) );
		$this->add_tag_control( 'html_tag', 'p' );
		$this->add_control(
			'tagline',
			array(
				'label'   => esc_html__( 'Tagline', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);
		$this->add_control(
			'link',
			array(
				'label'   => esc_html__( 'Link to home page', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Title', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_align_control( 'align', '{{WRAPPER}} .tbfe-site-title' );
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .tbfe-site-title__name',
			)
		);
		$this->add_control(
			'color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-site-title__name, {{WRAPPER}} .tbfe-site-title__name a' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'tagline_typography',
				'label'     => esc_html__( 'Tagline typography', 'theme-builder-for-elementor' ),
				'selector'  => '{{WRAPPER}} .tbfe-site-title__tagline',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'tagline_color',
			array(
				'label'     => esc_html__( 'Tagline color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-site-title__tagline' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$tag      = self::safe_tag( $settings['html_tag'], 'p' );
		$name     = esc_html( get_bloginfo( 'name' ) );
		if ( ! empty( $settings['link'] ) ) {
			$name = sprintf( '<a href="%s" rel="home">%s</a>', esc_url( home_url( '/' ) ), $name );
		}
		echo '<div class="tbfe-site-title">';
		printf( '<%1$s class="tbfe-site-title__name">%2$s</%1$s>', $tag, $name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted tag, escaped name.
		if ( ! empty( $settings['tagline'] ) ) {
			echo '<p class="tbfe-site-title__tagline">' . esc_html( get_bloginfo( 'description' ) ) . '</p>';
		}
		echo '</div>';
	}
}
