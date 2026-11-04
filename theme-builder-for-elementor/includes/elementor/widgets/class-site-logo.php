<?php
/**
 * Site logo: the logo of the site (Customizer) or a chosen image, linked to the home page.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

class Site_Logo extends Base {

	public function get_name() {
		return 'tbfe-site-logo';
	}

	public function get_title() {
		return esc_html__( 'Site Logo', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-site-logo';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_logo', array( 'label' => esc_html__( 'Logo', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'Image', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'site',
				'options' => array(
					'site'   => esc_html__( 'Logo of the site', 'theme-builder-for-elementor' ),
					'custom' => esc_html__( 'Custom image', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'image',
			array(
				'label'     => esc_html__( 'Image', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'source' => 'custom' ),
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
		$this->add_align_control( 'align', '{{WRAPPER}} .tbfe-site-logo' );
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Logo', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'width',
			array(
				'label'      => esc_html__( 'Width', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array( 'px' => array( 'min' => 20, 'max' => 600 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 160,
				),
				'selectors'  => array( '{{WRAPPER}} .tbfe-site-logo img' => 'width: {{SIZE}}{{UNIT}}; height: auto;' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$image_id = 'custom' === $settings['source'] && ! empty( $settings['image']['id'] ) ? (int) $settings['image']['id'] : (int) get_theme_mod( 'custom_logo' );
		$name     = get_bloginfo( 'name' );

		$html = $image_id
			? wp_get_attachment_image( $image_id, 'full', false, array( 'alt' => $name ) )
			: '<span class="tbfe-site-logo__text">' . esc_html( $name ) . '</span>';

		if ( ! empty( $settings['link'] ) ) {
			$html = sprintf( '<a href="%s" rel="home">%s</a>', esc_url( home_url( '/' ) ), $html );
		}
		echo '<div class="tbfe-site-logo">' . \TBFE\Plugin::kses( $html ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with Plugin::kses().
	}
}
