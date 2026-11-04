<?php
/**
 * Advanced Heading: a heading of three parts (before, middle, after). The middle part can be animated: highlighted,
 * underlined, circled, struck through, typed, rotating words or a moving gradient. Each part has its own colors,
 * background and typography.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Advanced_Heading extends Base {

	const UNIVERSAL = true;

	/** Animations of the middle part. */
	const ANIMATIONS = array( 'none', 'highlight', 'underline', 'circle', 'strike', 'typing', 'rotate', 'gradient' );

	public function get_name() {
		return 'tbfe-advanced-heading';
	}

	public function get_title() {
		return esc_html__( 'Advanced Heading', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-animated-headline';
	}

	public function get_keywords() {
		return array( 'heading', 'title', 'animated', 'headline', 'typing', 'highlight', 'rotate' );
	}

	public function get_style_depends(): array {
		return array( 'tbfe-widgets', 'tbfe-advanced-heading' );
	}

	public function get_script_depends() {
		return array( 'tbfe-advanced-heading' );
	}

	protected function register_controls() {
		// The control of the animation is "ah_animation": "animation" would switch on the entrance animation of Elementor.
		$this->start_controls_section( 'section_text', array( 'label' => esc_html__( 'Heading', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'before',
			array(
				'label'       => esc_html__( 'Before', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'We build', 'theme-builder-for-elementor' ),
				'dynamic'     => array( 'active' => true ),
				'label_block' => true,
			)
		);
		$this->add_control(
			'middle',
			array(
				'label'       => esc_html__( 'Middle (animated)', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'default'     => esc_html__( 'beautiful', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'For typing and rotating words write one word or phrase per line.', 'theme-builder-for-elementor' ),
				'dynamic'     => array( 'active' => true ),
			)
		);
		$this->add_control(
			'after',
			array(
				'label'       => esc_html__( 'After', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'websites', 'theme-builder-for-elementor' ),
				'dynamic'     => array( 'active' => true ),
				'label_block' => true,
			)
		);
		$this->add_control(
			'link',
			array(
				'label'   => esc_html__( 'Link', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);
		$this->add_tag_control( 'html_tag', 'h2' );
		$this->add_control(
			'stacked',
			array(
				'label'       => esc_html__( 'Parts below each other', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'Each part on its own line, e.g. a small kicker above the heading.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'section_animation', array( 'label' => esc_html__( 'Animation of the middle part', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'ah_animation',
			array(
				'label'   => esc_html__( 'Animation', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'highlight',
				'options' => array(
					'none'      => esc_html__( 'None', 'theme-builder-for-elementor' ),
					'highlight' => esc_html__( 'Marker highlight', 'theme-builder-for-elementor' ),
					'underline' => esc_html__( 'Underline', 'theme-builder-for-elementor' ),
					'circle'    => esc_html__( 'Hand-drawn circle', 'theme-builder-for-elementor' ),
					'strike'    => esc_html__( 'Strike through', 'theme-builder-for-elementor' ),
					'typing'    => esc_html__( 'Typing', 'theme-builder-for-elementor' ),
					'rotate'    => esc_html__( 'Rotating words', 'theme-builder-for-elementor' ),
					'gradient'  => esc_html__( 'Moving gradient', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'shape_color',
			array(
				'label'     => esc_html__( 'Marker or line color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-ah' => '--tbfe-ah-shape: {{VALUE}};' ),
				'condition' => array( 'ah_animation' => array( 'highlight', 'underline', 'circle', 'strike' ) ),
			)
		);
		$this->add_control(
			'shape_width',
			array(
				'label'     => esc_html__( 'Line width', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array( 'px' => array( 'min' => 1, 'max' => 20 ) ),
				'selectors' => array( '{{WRAPPER}} .tbfe-ah' => '--tbfe-ah-stroke: {{SIZE}};' ),
				'condition' => array( 'ah_animation' => array( 'underline', 'circle', 'strike' ) ),
			)
		);
		$this->add_control(
			'shape_front',
			array(
				'label'     => esc_html__( 'Line over the text', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array( 'ah_animation' => array( 'highlight', 'underline', 'circle' ) ),
			)
		);
		$this->add_control(
			'gradient_a',
			array(
				'label'     => esc_html__( 'Gradient color 1', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f7b6c',
				'selectors' => array( '{{WRAPPER}} .tbfe-ah' => '--tbfe-ah-g1: {{VALUE}};' ),
				'condition' => array( 'ah_animation' => 'gradient' ),
			)
		);
		$this->add_control(
			'gradient_b',
			array(
				'label'     => esc_html__( 'Gradient color 2', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f2a93b',
				'selectors' => array( '{{WRAPPER}} .tbfe-ah' => '--tbfe-ah-g2: {{VALUE}};' ),
				'condition' => array( 'ah_animation' => 'gradient' ),
			)
		);
		$this->add_control(
			'duration',
			array(
				'label'     => esc_html__( 'Duration (ms)', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 1200,
				'min'       => 100,
				'step'      => 100,
				'selectors' => array( '{{WRAPPER}} .tbfe-ah' => '--tbfe-ah-duration: {{VALUE}}ms;' ),
				'condition' => array( 'ah_animation!' => 'none' ),
			)
		);
		$this->add_control(
			'pause',
			array(
				'label'     => esc_html__( 'Pause between words (ms)', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 2000,
				'min'       => 300,
				'step'      => 100,
				'condition' => array( 'ah_animation' => array( 'typing', 'rotate' ) ),
			)
		);
		$this->add_control(
			'loop',
			array(
				'label'       => esc_html__( 'Repeat', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'Off: the animation runs once, when the heading comes into view.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => 'yes',
				'condition'   => array( 'ah_animation' => array( 'highlight', 'underline', 'circle', 'strike', 'typing', 'rotate' ) ),
			)
		);
		$this->add_control(
			'caret_color',
			array(
				'label'     => esc_html__( 'Cursor color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-ah--typing .tbfe-ah__text::after' => 'background-color: {{VALUE}};' ),
				'condition' => array( 'ah_animation' => 'typing' ),
			)
		);
		$this->end_controls_section();

		// Style: the whole heading.
		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Heading', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_responsive_control(
			'align',
			array(
				'label'                => esc_html__( 'Alignment', 'theme-builder-for-elementor' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'theme-builder-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'theme-builder-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'theme-builder-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				// Parts below each other are flex items: they follow the same alignment.
				'selectors_dictionary' => array(
					'left'   => 'left; --tbfe-ah-items: flex-start',
					'center' => 'center; --tbfe-ah-items: center',
					'right'  => 'right; --tbfe-ah-items: flex-end',
				),
				'selectors'            => array( '{{WRAPPER}} .tbfe-ah' => 'text-align: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .tbfe-ah',
			)
		);
		$this->add_control(
			'color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-ah' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'shadow',
				'selector' => '{{WRAPPER}} .tbfe-ah',
			)
		);
		$this->add_responsive_control(
			'parts_gap',
			array(
				'label'      => esc_html__( 'Space between the parts', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array( 'max' => 60 ),
					'em' => array(
						'max'  => 2,
						'step' => 0.05,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .tbfe-ah' => '--tbfe-ah-gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();

		$this->register_part_style( 'before', esc_html__( 'Before', 'theme-builder-for-elementor' ) );
		$this->register_part_style( 'middle', esc_html__( 'Middle', 'theme-builder-for-elementor' ) );
		$this->register_part_style( 'after', esc_html__( 'After', 'theme-builder-for-elementor' ) );
	}

	/**
	 * Style section of one part: typography, color, background, padding, radius.
	 */
	private function register_part_style( $part, $label ) {
		$selector = '{{WRAPPER}} .tbfe-ah__' . $part;
		$this->start_controls_section(
			'section_' . $part . '_style',
			array(
				'label' => $label,
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => $part . '_typography',
				'selector' => $selector,
			)
		);
		$this->add_control(
			$part . '_color',
			array(
				'label'       => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array( $selector => 'color: {{VALUE}};' ),
				'description' => 'middle' === $part ? esc_html__( 'With the moving gradient its colors are used.', 'theme-builder-for-elementor' ) : '',
			)
		);
		$this->add_control(
			$part . '_bg',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( $selector => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			$part . '_padding',
			array(
				'label'      => esc_html__( 'Padding', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			$part . '_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( $selector => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings  = $this->get_settings_for_display();
		$animation = in_array( $settings['ah_animation'], self::ANIMATIONS, true ) ? $settings['ah_animation'] : 'none';
		$tag       = self::safe_tag( $settings['html_tag'], 'h2' );
		$words     = array_values( array_filter( array_map( 'trim', preg_split( '/\R/', (string) $settings['middle'] ) ), 'strlen' ) );
		$cycling   = in_array( $animation, array( 'typing', 'rotate' ), true );
		$middle    = $cycling ? ( $words ? $words[0] : '' ) : implode( ' ', $words );

		$classes = array( 'tbfe-ah', 'tbfe-ah--' . $animation );
		if ( ! empty( $settings['stacked'] ) ) {
			$classes[] = 'tbfe-ah--stacked';
		}
		if ( ! empty( $settings['shape_front'] ) ) {
			$classes[] = 'tbfe-ah--front';
		}
		$data = array(
			'animation' => $animation,
			'loop'      => ! empty( $settings['loop'] ) ? '1' : '',
			'pause'     => max( 300, (int) $settings['pause'] ),
		);

		$parts = array();
		if ( '' !== (string) $settings['before'] ) {
			$parts[] = '<span class="tbfe-ah__part tbfe-ah__before">' . esc_html( $settings['before'] ) . '</span>';
		}
		if ( '' !== $middle ) {
			$shape = '';
			if ( 'circle' === $animation ) {
				$shape = '<svg class="tbfe-ah__shape" viewBox="0 0 500 150" preserveAspectRatio="none" aria-hidden="true"><path pathLength="100" d="M325 18C228-7 25 3 16 68c-8 61 142 76 255 75 108-2 209-21 215-70 6-52-113-62-227-66"/></svg>';
			}
			$words_attr = $cycling && count( $words ) > 1 ? ' data-words="' . esc_attr( wp_json_encode( $words ) ) . '"' : '';
			// For screen readers all the words of a cycling animation, the visible text is only decoration.
			$label   = $cycling && count( $words ) > 1 ? '<span class="screen-reader-text">' . esc_html( implode( ', ', $words ) ) . '</span>' : '';
			$hidden  = $label ? ' aria-hidden="true"' : '';
			$parts[] = '<span class="tbfe-ah__part tbfe-ah__middle"' . $words_attr . '>' . $label . '<span class="tbfe-ah__text"' . $hidden . '>' . esc_html( $middle ) . '</span>' . $shape . '</span>';
		}
		if ( '' !== (string) $settings['after'] ) {
			$parts[] = '<span class="tbfe-ah__part tbfe-ah__after">' . esc_html( $settings['after'] ) . '</span>';
		}
		if ( ! $parts ) {
			return;
		}
		$inner = implode( ' ', $parts );
		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_link_attributes( 'link', $settings['link'] );
			$inner = '<a ' . $this->get_render_attribute_string( 'link' ) . '>' . $inner . '</a>';
		}
		printf(
			'<%1$s class="%2$s" data-tbfe-ah="%3$s">%4$s</%1$s>',
			$tag, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted tag.
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( wp_json_encode( $data ) ),
			$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts.
		);
	}
}
