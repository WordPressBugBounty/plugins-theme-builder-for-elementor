<?php
/**
 * Links to the previous and next post.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Post_Navigation extends Base {

	public function get_name() {
		return 'tbfe-post-navigation';
	}

	public function get_title() {
		return esc_html__( 'Post Navigation', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-post-navigation';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_nav', array( 'label' => esc_html__( 'Post navigation', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'prev_label',
			array(
				'label'   => esc_html__( 'Previous label', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Previous', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'next_label',
			array(
				'label'   => esc_html__( 'Next label', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Next', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'show_title',
			array(
				'label'   => esc_html__( 'Post titles', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);
		$this->add_control(
			'in_same_term',
			array(
				'label'   => esc_html__( 'Only in the same category', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Links', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'label_typography',
				'label'    => esc_html__( 'Label typography', 'theme-builder-for-elementor' ),
				'selector' => '{{WRAPPER}} .tbfe-post-nav__label',
			)
		);
		$this->add_control(
			'label_color',
			array(
				'label'     => esc_html__( 'Label color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-post-nav__label' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Title typography', 'theme-builder-for-elementor' ),
				'selector' => '{{WRAPPER}} .tbfe-post-nav__title',
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Title color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-post-nav__title' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'title_hover',
			array(
				'label'     => esc_html__( 'Title hover color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} a:hover .tbfe-post-nav__title' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'divider_color',
			array(
				'label'     => esc_html__( 'Divider color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-post-nav' => 'border-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	private function link( $post, $label, $class, $show_title ) {
		if ( ! $post ) {
			return '<span class="tbfe-post-nav__item ' . esc_attr( $class ) . '"></span>';
		}
		return sprintf(
			'<a class="tbfe-post-nav__item %1$s" href="%2$s"><span class="tbfe-post-nav__label">%3$s</span>%4$s</a>',
			esc_attr( $class ),
			esc_url( get_permalink( $post ) ),
			esc_html( $label ),
			$show_title ? '<span class="tbfe-post-nav__title">' . esc_html( get_the_title( $post ) ) . '</span>' : ''
		);
	}

	protected function render() {
		$post = $this->post();
		if ( ! $post ) {
			$this->editor_notice( __( 'No post to show.', 'theme-builder-for-elementor' ) );
			return;
		}
		$settings = $this->get_settings_for_display();
		$same     = ! empty( $settings['in_same_term'] );

		$original        = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
		$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- adjacent posts read the global post.
		$prev            = get_previous_post( $same );
		$next            = get_next_post( $same );
		$GLOBALS['post'] = $original; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		echo '<nav class="tbfe-post-nav" aria-label="' . esc_attr__( 'Posts', 'theme-builder-for-elementor' ) . '">';
		echo wp_kses_post( $this->link( $prev, $settings['prev_label'], 'tbfe-post-nav__prev', ! empty( $settings['show_title'] ) ) );
		echo wp_kses_post( $this->link( $next, $settings['next_label'], 'tbfe-post-nav__next', ! empty( $settings['show_title'] ) ) );
		echo '</nav>';
	}
}
