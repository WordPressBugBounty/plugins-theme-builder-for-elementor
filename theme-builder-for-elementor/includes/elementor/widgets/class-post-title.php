<?php
/**
 * Post title (blog-title of version 1).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Post_Title extends Base {

	public function get_name() {
		return 'blog-title';
	}

	public function get_title() {
		return esc_html__( 'Post Title', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-post-title';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_title', array( 'label' => esc_html__( 'Title', 'theme-builder-for-elementor' ) ) );
		$this->add_tag_control( 'html_tag', 'h2' );
		$this->add_control(
			'link',
			array(
				'label'   => esc_html__( 'Link to the post', 'theme-builder-for-elementor' ),
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
		$this->add_align_control( 'align', '{{WRAPPER}} .tbfe-post-title', true );
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .tbfe-post-title, {{WRAPPER}} .tbfe-post-title a',
			)
		);
		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'text_shadow',
				'selector' => '{{WRAPPER}} .tbfe-post-title',
			)
		);
		$this->start_controls_tabs( 'title_tabs' );
		$this->start_controls_tab( 'title_normal', array( 'label' => esc_html__( 'Normal', 'theme-builder-for-elementor' ) ) );
		$this->add_responsive_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-post-title, {{WRAPPER}} .tbfe-post-title a' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'title_hover', array( 'label' => esc_html__( 'Hover', 'theme-builder-for-elementor' ) ) );
		$this->add_responsive_control(
			'title_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-post-title a:hover, {{WRAPPER}} .tbfe-post-title a:focus' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	protected function render() {
		$post = $this->post();
		if ( ! $post ) {
			$this->editor_notice( __( 'No post to show.', 'theme-builder-for-elementor' ) );
			return;
		}
		$settings = $this->get_settings_for_display();
		$tag      = self::safe_tag( isset( $settings['html_tag'] ) ? $settings['html_tag'] : 'h2', 'h2' );
		$title    = esc_html( get_the_title( $post ) );

		if ( ! empty( $settings['link'] ) ) {
			$title = sprintf( '<a href="%s" rel="bookmark">%s</a>', esc_url( get_permalink( $post ) ), $title );
		}
		printf( '<%1$s class="tbfe-post-title">%2$s</%1$s>', $tag, $title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- tag from a whitelist, title escaped.
	}
}
