<?php
/**
 * Box about the author of the post: avatar, name, biography.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;

use TBFE\Elementor\Context;

defined( 'ABSPATH' ) || exit;

class Author_Box extends Base {

	public function get_name() {
		return 'tbfe-author-box';
	}

	public function get_title() {
		return esc_html__( 'Author Box', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_author', array( 'label' => esc_html__( 'Author box', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'show_avatar',
			array(
				'label'   => esc_html__( 'Avatar', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);
		$this->add_control(
			'avatar_size',
			array(
				'label'     => esc_html__( 'Avatar size', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 96,
				'min'       => 24,
				'max'       => 300,
				'condition' => array( 'show_avatar' => 'yes' ),
			)
		);
		$this->add_tag_control( 'name_tag', 'h4', esc_html__( 'Name tag', 'theme-builder-for-elementor' ) );
		$this->add_control(
			'show_bio',
			array(
				'label'   => esc_html__( 'Biography', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);
		$this->add_control(
			'link_text',
			array(
				'label'   => esc_html__( 'Link to posts', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'All posts', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'        => esc_html__( 'Layout', 'theme-builder-for-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'row',
				'options'      => array(
					'row'    => esc_html__( 'Avatar beside', 'theme-builder-for-elementor' ),
					'column' => esc_html__( 'Avatar above', 'theme-builder-for-elementor' ),
				),
				'prefix_class' => 'tbfe-author-box--',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
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
				'selectors' => array( '{{WRAPPER}} .tbfe-author-box' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'box_border',
				'selector' => '{{WRAPPER}} .tbfe-author-box',
			)
		);
		$this->add_responsive_control(
			'box_padding',
			array(
				'label'      => esc_html__( 'Padding', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-author-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'avatar_radius',
			array(
				'label'      => esc_html__( 'Avatar radius', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%', 'px' ),
				'default'    => array(
					'unit' => '%',
					'size' => 50,
				),
				'selectors'  => array( '{{WRAPPER}} .tbfe-author-box__avatar img' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'name_typography',
				'label'     => esc_html__( 'Name typography', 'theme-builder-for-elementor' ),
				'selector'  => '{{WRAPPER}} .tbfe-author-box__name',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'name_color',
			array(
				'label'     => esc_html__( 'Name color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-author-box__name' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'bio_typography',
				'label'    => esc_html__( 'Biography typography', 'theme-builder-for-elementor' ),
				'selector' => '{{WRAPPER}} .tbfe-author-box__bio',
			)
		);
		$this->add_control(
			'bio_color',
			array(
				'label'     => esc_html__( 'Biography color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-author-box__bio' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'link_color',
			array(
				'label'     => esc_html__( 'Link color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-author-box__link' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		// On an author archive the author of the archive, else the author of the post.
		$author = Context::author_id();
		if ( ! $author ) {
			$this->editor_notice( __( 'No post to show.', 'theme-builder-for-elementor' ) );
			return;
		}
		$settings = $this->get_settings_for_display();
		$url      = get_author_posts_url( $author );
		$tag      = self::safe_tag( $settings['name_tag'], 'h4' );

		echo '<div class="tbfe-author-box">';
		if ( ! empty( $settings['show_avatar'] ) ) {
			echo '<a class="tbfe-author-box__avatar" href="' . esc_url( $url ) . '" aria-hidden="true" tabindex="-1">' . \TBFE\Plugin::kses( get_avatar( $author, max( 24, (int) $settings['avatar_size'] ) ) ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with Plugin::kses().
		}
		echo '<div class="tbfe-author-box__body">';
		printf( '<%1$s class="tbfe-author-box__name">%2$s</%1$s>', $tag, esc_html( get_the_author_meta( 'display_name', $author ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted tag.
		$bio = get_the_author_meta( 'description', $author );
		if ( ! empty( $settings['show_bio'] ) && $bio ) {
			echo '<div class="tbfe-author-box__bio">' . wp_kses_post( wpautop( $bio ) ) . '</div>';
		}
		if ( ! empty( $settings['link_text'] ) ) {
			echo '<a class="tbfe-author-box__link" href="' . esc_url( $url ) . '">' . esc_html( $settings['link_text'] ) . '</a>';
		}
		echo '</div></div>';
	}
}
