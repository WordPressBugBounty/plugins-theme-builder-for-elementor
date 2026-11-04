<?php
/**
 * Comments of the post: the list and the form (the comments template of the theme or of WordPress).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Post_Comments extends Base {

	public function get_name() {
		return 'tbfe-post-comments';
	}

	public function get_title() {
		return esc_html__( 'Post Comments', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-comments';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_comments', array( 'label' => esc_html__( 'Comments', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'info',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Shows the comments and the comment form of the theme. The look comes from the theme; you can adjust texts and colors below.', 'theme-builder-for-elementor' ),
				'content_classes' => 'elementor-descriptor',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Comments', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'heading_typography',
				'label'    => esc_html__( 'Headings', 'theme-builder-for-elementor' ),
				'selector' => '{{WRAPPER}} .comments-title, {{WRAPPER}} .comment-reply-title',
			)
		);
		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Text color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-comments' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'link_color',
			array(
				'label'     => esc_html__( 'Link color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-comments a' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'button_bg',
			array(
				'label'     => esc_html__( 'Button background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-comments input[type="submit"]' => 'background-color: {{VALUE}}; border-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'button_color',
			array(
				'label'     => esc_html__( 'Button text', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-comments input[type="submit"]' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		if ( $this->is_editor() ) {
			echo '<div class="tbfe-comments tbfe-notice">' . esc_html__( 'The comments of the post are shown here.', 'theme-builder-for-elementor' ) . '</div>';
			return;
		}
		if ( ! is_singular() || ( ! comments_open() && ! get_comments_number() ) ) {
			return;
		}
		echo '<div class="tbfe-comments">';
		comments_template();
		echo '</div>';
	}
}
