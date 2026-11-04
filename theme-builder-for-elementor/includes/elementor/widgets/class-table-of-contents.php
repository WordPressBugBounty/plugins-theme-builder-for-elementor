<?php
/**
 * Table of Contents: links to the headings of the post, built in the browser from the page content.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Table_Of_Contents extends Base {

	const UNIVERSAL = true;

	public function get_name() {
		return 'tbfe-toc';
	}

	public function get_title() {
		return esc_html__( 'Table of Contents', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-table-of-contents';
	}

	public function get_keywords() {
		return array( 'toc', 'table of contents', 'headings', 'anchor', 'contents' );
	}

	public function get_style_depends(): array {
		return array( 'tbfe-widgets', 'tbfe-toc' );
	}

	public function get_script_depends() {
		return array( 'tbfe-toc' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_toc', array( 'label' => esc_html__( 'Table of contents', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'title',
			array(
				'label'   => esc_html__( 'Title', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Contents', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'headings',
			array(
				'label'    => esc_html__( 'Headings', 'theme-builder-for-elementor' ),
				'type'     => Controls_Manager::SELECT2,
				'multiple' => true,
				'default'  => array( 'h2', 'h3' ),
				'options'  => array(
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
				),
			)
		);
		$this->add_control(
			'container',
			array(
				'label'       => esc_html__( 'Read the headings from', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '.tbfe-post-content',
				'description' => esc_html__( 'A CSS selector. Empty: the content of the post, or the main part of the page.', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'marker',
			array(
				'label'   => esc_html__( 'Marker', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'numbers',
				'options' => array(
					'numbers' => esc_html__( 'Numbers', 'theme-builder-for-elementor' ),
					'bullets' => esc_html__( 'Bullets', 'theme-builder-for-elementor' ),
					'none'    => esc_html__( 'None', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'collapsible',
			array(
				'label' => esc_html__( 'Can be collapsed', 'theme-builder-for-elementor' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);
		$this->add_control(
			'collapsed',
			array(
				'label'     => esc_html__( 'Collapsed at the start', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array( 'collapsible' => 'yes' ),
			)
		);
		$this->add_control(
			'minimum',
			array(
				'label'       => esc_html__( 'Show from', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'The table is hidden when the post has fewer headings.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 2,
				'min'         => 1,
			)
		);
		$this->add_control(
			'offset',
			array(
				'label'       => esc_html__( 'Space above the heading after a click (px)', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'Room for a sticky header.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 90,
				'min'         => 0,
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_box',
			array(
				'label' => esc_html__( 'Box', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'box_bg',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-toc' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'box_border',
				'selector' => '{{WRAPPER}} .tbfe-toc',
			)
		);
		$this->add_control(
			'box_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-toc' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'box_padding',
			array(
				'label'      => esc_html__( 'Padding', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-toc' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_texts',
			array(
				'label' => esc_html__( 'Title and links', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Title typography', 'theme-builder-for-elementor' ),
				'selector' => '{{WRAPPER}} .tbfe-toc__title',
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Title color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-toc__title, {{WRAPPER}} .tbfe-toc__toggle' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'link_typography',
				'label'     => esc_html__( 'Links typography', 'theme-builder-for-elementor' ),
				'selector'  => '{{WRAPPER}} .tbfe-toc__list',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'link_color',
			array(
				'label'     => esc_html__( 'Links color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-toc__list a, {{WRAPPER}} .tbfe-toc__list li::marker' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'link_active',
			array(
				'label'       => esc_html__( 'Current heading color', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'The heading the visitor is reading now, also on hover.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'selectors'   => array( '{{WRAPPER}} .tbfe-toc__list a.is-active, {{WRAPPER}} .tbfe-toc__list a:hover, {{WRAPPER}} .tbfe-toc__list a:focus' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'indent',
			array(
				'label'      => esc_html__( 'Indent of sub-headings', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-toc__list ol, {{WRAPPER}} .tbfe-toc__list ul' => 'padding-left: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$levels   = array_values( array_intersect( (array) $settings['headings'], array( 'h2', 'h3', 'h4', 'h5', 'h6' ) ) );
		$levels   = $levels ? $levels : array( 'h2', 'h3' );
		$marker   = in_array( $settings['marker'], array( 'numbers', 'bullets', 'none' ), true ) ? $settings['marker'] : 'numbers';
		$id       = 'tbfe-toc-' . $this->get_id();
		$collapse = ! empty( $settings['collapsible'] );
		$closed   = $collapse && ! empty( $settings['collapsed'] );

		printf(
			'<nav class="tbfe-toc tbfe-toc--%1$s%2$s" aria-labelledby="%3$s-title" data-headings="%4$s" data-container="%5$s" data-minimum="%6$d" data-offset="%7$d" data-editor="%8$s">',
			esc_attr( $marker ),
			$closed ? ' is-collapsed' : '',
			esc_attr( $id ),
			esc_attr( implode( ',', $levels ) ),
			esc_attr( (string) $settings['container'] ),
			absint( max( 1, (int) $settings['minimum'] ) ),
			absint( $settings['offset'] ),
			$this->is_editor() ? '1' : ''
		);
		echo '<div class="tbfe-toc__head">';
		printf( '<span class="tbfe-toc__title" id="%s-title">%s</span>', esc_attr( $id ), esc_html( $settings['title'] ) );
		if ( $collapse ) {
			printf(
				'<button type="button" class="tbfe-toc__toggle" aria-expanded="%1$s" aria-controls="%2$s-list"><span class="screen-reader-text">%3$s</span></button>',
				$closed ? 'false' : 'true',
				esc_attr( $id ),
				esc_html__( 'Show or hide the contents', 'theme-builder-for-elementor' )
			);
		}
		echo '</div>';
		printf( '<div class="tbfe-toc__list" id="%s-list"></div>', esc_attr( $id ) );
		echo '</nav>';
	}
}
