<?php
/**
 * Slider: every slide is a Section template, so a slide is designed in Elementor like any section. Slide or fade,
 * one or more slides in view (per device), autoplay, arrows, dots, swipe and keyboard.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

class Slider extends Base {

	const UNIVERSAL = true;

	public function get_name() {
		return 'tbfe-slider';
	}

	public function get_title() {
		return esc_html__( 'Slider', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-slides';
	}

	public function get_keywords() {
		return array( 'slider', 'slides', 'carousel', 'hero', 'section' );
	}

	public function get_style_depends(): array {
		return array( 'tbfe-widgets', 'tbfe-slider' );
	}

	public function get_script_depends() {
		return array( 'tbfe-slider' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_slides', array( 'label' => esc_html__( 'Slides', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'slides_notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Every slide is a Section template. Create the slides in Theme Builder › Templates › Sections, then choose them here.', 'theme-builder-for-elementor' ),
				'content_classes' => 'elementor-descriptor',
			)
		);
		$repeater = new Repeater();
		$repeater->add_control(
			'template_id',
			array(
				'label'   => esc_html__( 'Section', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => Template::section_options(),
			)
		);
		$repeater->add_control(
			'label',
			array(
				'label'       => esc_html__( 'Name of the slide', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'For screen readers and the list here.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Slide', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'slides',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ label }}}',
				'default'     => array(
					array( 'label' => esc_html__( 'Slide 1', 'theme-builder-for-elementor' ) ),
					array( 'label' => esc_html__( 'Slide 2', 'theme-builder-for-elementor' ) ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'section_options', array( 'label' => esc_html__( 'Slider', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'effect',
			array(
				'label'   => esc_html__( 'Effect', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'slide',
				'options' => array(
					'slide' => esc_html__( 'Slide', 'theme-builder-for-elementor' ),
					'fade'  => esc_html__( 'Fade', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_responsive_control(
			'per_view',
			array(
				'label'          => esc_html__( 'Slides in view', 'theme-builder-for-elementor' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '1',
				'tablet_default' => '1',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				),
				'selectors'      => array( '{{WRAPPER}} .tbfe-slider' => '--tbfe-per-view: {{VALUE}};' ),
				'condition'      => array( 'effect' => 'slide' ),
			)
		);
		$this->add_responsive_control(
			'gap',
			array(
				'label'      => esc_html__( 'Space between slides', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-slider' => '--tbfe-slider-gap: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'effect' => 'slide' ),
			)
		);
		$this->add_control(
			'speed',
			array(
				'label'   => esc_html__( 'Transition (ms)', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 600,
				'min'     => 100,
				'step'    => 50,
			)
		);
		$this->add_control(
			'autoplay',
			array(
				'label'     => esc_html__( 'Autoplay', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'delay',
			array(
				'label'     => esc_html__( 'Time per slide (ms)', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 5000,
				'min'       => 1000,
				'step'      => 500,
				'condition' => array( 'autoplay' => 'yes' ),
			)
		);
		$this->add_control(
			'pause',
			array(
				'label'       => esc_html__( 'Pause on hover', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'The slider also stops while it has the keyboard focus. In the editor it does not play.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'condition'   => array( 'autoplay' => 'yes' ),
			)
		);
		$this->add_control(
			'loop',
			array(
				'label'   => esc_html__( 'Endless', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);
		$this->add_control(
			'arrows',
			array(
				'label'     => esc_html__( 'Arrows', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'dots',
			array(
				'label'   => esc_html__( 'Dots', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);
		$this->add_control(
			'equal',
			array(
				'label'       => esc_html__( 'Same height', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'All slides get the height of the highest one.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
			)
		);
		$this->end_controls_section();

		// Style: arrows.
		$this->start_controls_section(
			'section_arrows_style',
			array(
				'label'     => esc_html__( 'Arrows', 'theme-builder-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'arrows' => 'yes' ),
			)
		);
		$this->add_control(
			'arrows_position',
			array(
				'label'   => esc_html__( 'Position', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'inside',
				'options' => array(
					'inside'  => esc_html__( 'On the slides', 'theme-builder-for-elementor' ),
					'outside' => esc_html__( 'Next to the slides', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_responsive_control(
			'arrows_size',
			array(
				'label'      => esc_html__( 'Size', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 24, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-slider' => '--tbfe-arrow-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'arrows_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-slider__arrow' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->start_controls_tabs( 'arrows_colors' );
		$this->start_controls_tab( 'arrows_normal', array( 'label' => esc_html__( 'Normal', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'arrows_bg',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-slider__arrow' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'arrows_color',
			array(
				'label'     => esc_html__( 'Icon', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-slider__arrow' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'arrows_hover', array( 'label' => esc_html__( 'Hover', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'arrows_bg_hover',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-slider__arrow:hover, {{WRAPPER}} .tbfe-slider__arrow:focus-visible' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'arrows_color_hover',
			array(
				'label'     => esc_html__( 'Icon', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-slider__arrow:hover, {{WRAPPER}} .tbfe-slider__arrow:focus-visible' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

		// Style: dots.
		$this->start_controls_section(
			'section_dots_style',
			array(
				'label'     => esc_html__( 'Dots', 'theme-builder-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'dots' => 'yes' ),
			)
		);
		$this->add_control(
			'dots_position',
			array(
				'label'   => esc_html__( 'Position', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'below',
				'options' => array(
					'below'  => esc_html__( 'Below the slides', 'theme-builder-for-elementor' ),
					'inside' => esc_html__( 'On the slides', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_responsive_control(
			'dots_size',
			array(
				'label'      => esc_html__( 'Size', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 4, 'max' => 24 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-slider' => '--tbfe-dot-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'dots_color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-slider__dot' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'dots_active',
			array(
				'label'     => esc_html__( 'Current slide', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-slider__dot.is-active' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$slides   = array();
		foreach ( (array) $settings['slides'] as $i => $slide ) {
			ob_start();
			$error = Template::render_section( isset( $slide['template_id'] ) ? $slide['template_id'] : 0 );
			$html  = ob_get_clean();
			if ( '' === $error ) {
				$slides[] = array(
					'label' => '' !== (string) $slide['label'] ? (string) $slide['label'] : sprintf( /* translators: %d: number of the slide */ __( 'Slide %d', 'theme-builder-for-elementor' ), $i + 1 ),
					'html'  => $html,
				);
			}
		}
		if ( ! $slides ) {
			$this->editor_notice( __( 'Choose a Section template for every slide.', 'theme-builder-for-elementor' ) );
			return;
		}

		$effect  = 'fade' === $settings['effect'] ? 'fade' : 'slide';
		$options = array(
			'effect'   => $effect,
			'speed'    => max( 100, (int) $settings['speed'] ),
			'autoplay' => ! empty( $settings['autoplay'] ) && ! $this->is_editor() ? max( 1000, (int) $settings['delay'] ) : 0,
			'pause'    => ! empty( $settings['pause'] ),
			'loop'     => ! empty( $settings['loop'] ),
		);
		$classes = array(
			'tbfe-slider',
			'tbfe-slider--' . $effect,
			'tbfe-slider--arrows-' . ( 'outside' === $settings['arrows_position'] ? 'outside' : 'inside' ),
			'tbfe-slider--dots-' . ( 'inside' === $settings['dots_position'] ? 'inside' : 'below' ),
		);
		if ( ! empty( $settings['equal'] ) ) {
			$classes[] = 'tbfe-slider--equal';
		}
		$count = count( $slides );

		printf(
			'<div class="%1$s" data-tbfe-slider="%2$s" role="region" aria-roledescription="%3$s" aria-label="%4$s" style="--tbfe-slider-speed:%5$dms">',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( wp_json_encode( $options ) ),
			esc_attr__( 'carousel', 'theme-builder-for-elementor' ),
			esc_attr__( 'Slider', 'theme-builder-for-elementor' ),
			(int) $options['speed']
		);
		echo '<div class="tbfe-slider__viewport"><div class="tbfe-slider__track" aria-live="' . esc_attr( $options['autoplay'] ? 'off' : 'polite' ) . '">';
		foreach ( $slides as $i => $slide ) {
			printf(
				'<div class="tbfe-slider__slide%1$s" role="group" aria-roledescription="%2$s" aria-label="%3$s">%4$s</div>',
				0 === $i ? ' is-active' : '',
				esc_attr__( 'slide', 'theme-builder-for-elementor' ),
				/* translators: 1: name of the slide, 2: number, 3: number of slides */
				esc_attr( sprintf( __( '%1$s (%2$d of %3$d)', 'theme-builder-for-elementor' ), $slide['label'], $i + 1, $count ) ),
				$slide['html'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor builds and escapes the template markup.
			);
		}
		echo '</div></div>';

		if ( $count > 1 && ! empty( $settings['arrows'] ) ) {
			$arrow = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			printf( '<button type="button" class="tbfe-slider__arrow tbfe-slider__arrow--prev" aria-label="%s">%s</button>', esc_attr__( 'Previous slide', 'theme-builder-for-elementor' ), $arrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
			printf( '<button type="button" class="tbfe-slider__arrow tbfe-slider__arrow--next" aria-label="%s">%s</button>', esc_attr__( 'Next slide', 'theme-builder-for-elementor' ), $arrow ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
		}
		if ( $count > 1 && ! empty( $settings['dots'] ) ) {
			/* translators: %d: number of the slide */
			printf( '<div class="tbfe-slider__dots" data-label="%s"></div>', esc_attr__( 'Go to slide %d', 'theme-builder-for-elementor' ) );
		}
		if ( $options['autoplay'] ) {
			// WCAG 2.2.2: a moving slider can be paused.
			printf(
				'<button type="button" class="tbfe-slider__play" aria-label="%1$s" data-play="%2$s" data-pause="%1$s"><span aria-hidden="true"></span></button>',
				esc_attr__( 'Pause the slider', 'theme-builder-for-elementor' ),
				esc_attr__( 'Play the slider', 'theme-builder-for-elementor' )
			);
		}
		echo '</div>';
	}
}
