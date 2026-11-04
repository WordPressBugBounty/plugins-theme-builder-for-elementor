<?php
/**
 * Base of the post meta widgets (date, author, categories, tags, comments): icon, label and value.
 *
 * The control IDs are the ones of version 1 (text, icon, icon_align, icon_indent, align, text_typography,
 * typography, title_color, title_color_hover), so saved layouts keep their settings.
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

abstract class Meta_Base extends Base {

	/** Default label. */
	const LABEL = '';

	/** Default icon (Font Awesome 5 solid). */
	const ICON = '';

	/** Control ID of the label color (differs between the widgets of version 1). */
	const LABEL_COLOR_ID = 'color_before_text';

	/** The value is a list of links (terms) with a separator and item styles. */
	const IS_LIST = false;

	/**
	 * The value HTML (escaped), or '' when there is nothing to show.
	 */
	abstract protected function value_html( \WP_Post $post, array $settings );

	/**
	 * Extra content controls of the widget.
	 */
	protected function register_value_controls() {}

	protected function register_controls() {
		$this->start_controls_section(
			'section_meta',
			array( 'label' => esc_html( $this->get_title() ) )
		);

		$this->register_value_controls();

		if ( static::IS_LIST ) {
			$this->add_control(
				'separator',
				array(
					'label'   => esc_html__( 'Separator', 'theme-builder-for-elementor' ),
					'type'    => Controls_Manager::TEXT,
					'default' => ' / ',
				)
			);
		}

		$this->add_control(
			'text',
			array(
				'label'   => esc_html__( 'Label', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => static::LABEL,
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'   => esc_html__( 'Icon', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => static::ICON,
					'library' => static::ICON ? 'fa-solid' : '',
				),
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
				'default'   => array(
					'unit' => 'px',
					'size' => 6,
				),
				'selectors' => array( '{{WRAPPER}} .tbfe-meta' => '--tbfe-icon-gap: {{SIZE}}{{UNIT}};' ),
				'condition' => array( 'icon[value]!' => '' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Layout', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_align_control( 'align', '{{WRAPPER}} .tbfe-meta', false, 'justify-content' );
		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Icon color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-meta__icon' => 'color: {{VALUE}}; fill: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Icon size', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-meta__icon' => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'text_style',
			array(
				'label' => esc_html__( 'Label', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .tbfe-meta__label',
			)
		);
		$this->add_control(
			static::LABEL_COLOR_ID,
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-meta__label' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'cats_style',
			array(
				'label' => esc_html__( 'Value', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .tbfe-meta__value, {{WRAPPER}} .tbfe-meta__value a',
			)
		);
		$this->start_controls_tabs( 'value_tabs' );
		$this->start_controls_tab( 'value_normal', array( 'label' => esc_html__( 'Normal', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-meta__value, {{WRAPPER}} .tbfe-meta__value a' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'value_hover', array( 'label' => esc_html__( 'Hover', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'title_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-meta__value a:hover, {{WRAPPER}} .tbfe-meta__value a:focus' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();

		if ( static::IS_LIST ) {
			$this->add_control(
				'separator_color',
				array(
					'label'     => esc_html__( 'Separator color', 'theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'separator' => 'before',
					'selectors' => array( '{{WRAPPER}} .tbfe-meta__sep' => 'color: {{VALUE}};' ),
				)
			);
			$this->add_control(
				'item_background',
				array(
					'label'     => esc_html__( 'Item background', 'theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( '{{WRAPPER}} .tbfe-meta__value a' => 'background-color: {{VALUE}};' ),
				)
			);
			$this->add_group_control(
				Group_Control_Border::get_type(),
				array(
					'name'     => 'border',
					'selector' => '{{WRAPPER}} .tbfe-meta__value a',
				)
			);
			$this->add_control(
				'border_radius',
				array(
					'label'      => esc_html__( 'Border radius', 'theme-builder-for-elementor' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'selectors'  => array( '{{WRAPPER}} .tbfe-meta__value a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
				)
			);
			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				array(
					'name'     => 'button_box_shadow',
					'selector' => '{{WRAPPER}} .tbfe-meta__value a',
				)
			);
			$this->add_responsive_control(
				'text_padding',
				array(
					'label'      => esc_html__( 'Padding', 'theme-builder-for-elementor' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', '%' ),
					'selectors'  => array( '{{WRAPPER}} .tbfe-meta__value a' => 'display: inline-block; padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
				)
			);
			$this->add_responsive_control(
				'text_margin',
				array(
					'label'      => esc_html__( 'Margin', 'theme-builder-for-elementor' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', 'em', '%' ),
					'selectors'  => array( '{{WRAPPER}} .tbfe-meta__value a' => 'display: inline-block; margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
				)
			);
		}
		$this->end_controls_section();
	}

	protected function render() {
		$post = $this->post();
		if ( ! $post ) {
			$this->editor_notice( __( 'No post to show.', 'theme-builder-for-elementor' ) );
			return;
		}
		$settings = $this->get_settings_for_display();
		$value    = $this->value_html( $post, $settings );
		if ( '' === $value ) {
			$this->editor_notice( __( 'This post has nothing to show here.', 'theme-builder-for-elementor' ) );
			return;
		}

		$icon_after = isset( $settings['icon_align'] ) && 'right' === $settings['icon_align'];
		$has_icon   = ! empty( $settings['icon']['value'] );

		echo '<div class="tbfe-meta' . ( $icon_after ? ' tbfe-meta--icon-after' : '' ) . '">';
		if ( $has_icon ) {
			echo '<span class="tbfe-meta__icon" aria-hidden="true">';
			Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) );
			echo '</span>';
		}
		if ( isset( $settings['text'] ) && '' !== $settings['text'] ) {
			echo '<span class="tbfe-meta__label">' . esc_html( $settings['text'] ) . '</span>';
		}
		echo '<span class="tbfe-meta__value">' . \TBFE\Plugin::kses( $value ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with Plugin::kses().
		echo '</div>';
	}

	/**
	 * Term links joined with the separator.
	 */
	protected function terms_html( \WP_Post $post, $taxonomy, array $settings ) {
		$terms = get_the_terms( $post, $taxonomy );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return '';
		}
		$sep   = isset( $settings['separator'] ) ? $settings['separator'] : ' / ';
		$links = array();
		foreach ( $terms as $term ) {
			$url     = get_term_link( $term );
			$links[] = is_wp_error( $url ) ? esc_html( $term->name ) : sprintf( '<a href="%s" rel="tag">%s</a>', esc_url( $url ), esc_html( $term->name ) );
		}
		return implode( '<span class="tbfe-meta__sep">' . esc_html( $sep ) . '</span>', $links );
	}
}
