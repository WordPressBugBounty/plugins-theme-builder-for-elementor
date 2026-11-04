<?php
/**
 * Elementor document: header. Its settings make the header sticky, shrinking, hiding or transparent.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Documents;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

class Header extends Theme_Document {
	const TBFE_TYPE        = 'header';
	const HAS_PREVIEW_POST = false;

	/** Devices of the sticky and transparent options. */
	const DEVICES = array( 'all', 'desktop-tablet', 'desktop' );

	protected function register_controls() {
		parent::register_controls();

		$devices = array(
			'all'            => esc_html__( 'All devices', 'theme-builder-for-elementor' ),
			'desktop-tablet' => esc_html__( 'Desktop and tablet', 'theme-builder-for-elementor' ),
			'desktop'        => esc_html__( 'Desktop only', 'theme-builder-for-elementor' ),
		);

		$this->start_controls_section(
			'tbfe_header_section',
			array(
				'label' => esc_html__( 'Header behaviour', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			)
		);
		$this->add_control(
			'tbfe_sticky',
			array(
				'label'       => esc_html__( 'Sticky header', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'The header stays on top when the page scrolls. See it on the site (not in the editor).', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
			)
		);
		$this->add_control(
			'tbfe_sticky_on',
			array(
				'label'     => esc_html__( 'Sticky on', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'all',
				'options'   => $devices,
				'condition' => array( 'tbfe_sticky' => 'yes' ),
			)
		);
		$this->add_control(
			'tbfe_shrink',
			array(
				'label'       => esc_html__( 'Shrink on scroll', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'Less space above and below, smaller logo.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'condition'   => array( 'tbfe_sticky' => 'yes' ),
			)
		);
		$this->add_control(
			'tbfe_shrink_logo',
			array(
				'label'     => esc_html__( 'Logo height when shrunk', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 16, 'max' => 120 ) ),
				'default'   => array(
					'unit' => 'px',
					'size' => 36,
				),
				'condition' => array(
					'tbfe_sticky' => 'yes',
					'tbfe_shrink' => 'yes',
				),
			)
		);
		$this->add_control(
			'tbfe_hide_down',
			array(
				'label'       => esc_html__( 'Hide when scrolling down', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'It comes back as soon as the visitor scrolls up.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'condition'   => array( 'tbfe_sticky' => 'yes' ),
			)
		);
		$this->add_control(
			'tbfe_scrolled_bg',
			array(
				'label'     => esc_html__( 'Background when scrolled', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => array( 'active' => false ),
				'condition' => array( 'tbfe_sticky' => 'yes' ),
			)
		);
		$this->add_control(
			'tbfe_scrolled_shadow',
			array(
				'label'     => esc_html__( 'Shadow when scrolled', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'tbfe_sticky' => 'yes' ),
			)
		);
		$this->add_control(
			'tbfe_transparent',
			array(
				'label'       => esc_html__( 'Transparent header', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'The header lies over the top of the page, e.g. over a hero image. Give its sections no background.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'separator'   => 'before',
			)
		);
		$this->add_control(
			'tbfe_transparent_on',
			array(
				'label'     => esc_html__( 'Transparent on', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'all',
				'options'   => $devices,
				'condition' => array( 'tbfe_transparent' => 'yes' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Classes and inline style of the <header> element on the site.
	 *
	 * @param int $template_id Header template ID.
	 * @return array array( 'classes' => string[], 'style' => string, 'script' => bool )
	 */
	public static function frontend_attributes( $template_id ) {
		$settings = get_post_meta( $template_id, '_elementor_page_settings', true );
		$settings = is_array( $settings ) ? $settings : array();
		$on       = static function ( $key ) use ( $settings ) {
			return isset( $settings[ $key ] ) && 'yes' === $settings[ $key ];
		};
		$device   = static function ( $key ) use ( $settings ) {
			return isset( $settings[ $key ] ) && in_array( $settings[ $key ], self::DEVICES, true ) ? $settings[ $key ] : 'all';
		};

		$classes = array();
		$style   = array();
		if ( $on( 'tbfe_sticky' ) ) {
			$classes[] = 'tbfe-header--sticky';
			$classes[] = 'tbfe-sticky-' . $device( 'tbfe_sticky_on' );
			if ( $on( 'tbfe_shrink' ) ) {
				$classes[] = 'tbfe-header--shrink';
				$size      = isset( $settings['tbfe_shrink_logo']['size'] ) ? (int) $settings['tbfe_shrink_logo']['size'] : 36;
				$style[]   = '--tbfe-shrink-logo:' . max( 16, min( 120, $size ) ) . 'px';
			}
			if ( $on( 'tbfe_hide_down' ) ) {
				$classes[] = 'tbfe-header--hide';
			}
			if ( $on( 'tbfe_scrolled_shadow' ) ) {
				$classes[] = 'tbfe-header--shadow';
			}
			$color = isset( $settings['tbfe_scrolled_bg'] ) ? self::color( $settings['tbfe_scrolled_bg'] ) : '';
			if ( $color ) {
				$style[] = '--tbfe-scrolled-bg:' . $color;
			}
		}
		if ( $on( 'tbfe_transparent' ) ) {
			$classes[] = 'tbfe-header--transparent';
			$classes[] = 'tbfe-transparent-' . $device( 'tbfe_transparent_on' );
		}
		return array(
			'classes' => $classes,
			'style'   => implode( ';', $style ),
			'script'  => (bool) $classes,
		);
	}

	/**
	 * A CSS color (hex, rgb[a], hsl[a]) or ''.
	 */
	private static function color( $value ) {
		$value = trim( (string) $value );
		return preg_match( '/^(#[0-9a-fA-F]{3,8}|(rgb|hsl)a?\([0-9.,%\s\/]+\))$/', $value ) ? $value : '';
	}
}
