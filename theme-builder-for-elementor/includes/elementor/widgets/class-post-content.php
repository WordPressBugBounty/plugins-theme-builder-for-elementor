<?php
/**
 * Post content or excerpt (blog-content of version 1).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Post_Content extends Base {

	/** @var int[] Posts being rendered (stops a post from showing itself). */
	private static $stack = array();

	public function get_name() {
		return 'blog-content';
	}

	public function get_title() {
		return esc_html__( 'Post Content', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-post-content';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_editor', array( 'label' => esc_html__( 'Content', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'content_type',
			array(
				'label'   => esc_html__( 'Show', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'excerpt',
				'options' => array(
					'excerpt' => esc_html__( 'Excerpt', 'theme-builder-for-elementor' ),
					'full'    => esc_html__( 'Full content', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'limit',
			array(
				'label'     => esc_html__( 'Words', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 5,
						'max' => 300,
					),
				),
				'default'   => array(
					'unit' => 'px',
					'size' => 35,
				),
				'condition' => array( 'content_type' => 'excerpt' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Text', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_align_control( 'align', '{{WRAPPER}} .tbfe-post-content', true );
		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-post-content' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .tbfe-post-content',
			)
		);
		$this->add_control(
			'footer_title_color',
			array(
				'label'     => esc_html__( 'Link color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-post-content a' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'footer_title_color_hover',
			array(
				'label'     => esc_html__( 'Link hover color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-post-content a:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$post = $this->post();
		if ( ! $post ) {
			$this->editor_notice( __( 'No post to show.', 'theme-builder-for-elementor' ) );
			return;
		}
		if ( in_array( $post->ID, self::$stack, true ) ) {
			return;
		}
		$settings = $this->get_settings_for_display();

		echo '<div class="tbfe-post-content">';
		if ( post_password_required( $post ) ) {
			echo \TBFE\Plugin::kses( get_the_password_form( $post ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with Plugin::kses().
		} elseif ( isset( $settings['content_type'] ) && 'excerpt' !== $settings['content_type'] ) {
			self::$stack[] = $post->ID;
			echo $this->content( $post ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered post content.
			array_pop( self::$stack );
		} else {
			$words   = isset( $settings['limit']['size'] ) ? max( 1, (int) $settings['limit']['size'] ) : 35;
			$excerpt = has_excerpt( $post ) ? $post->post_excerpt : strip_shortcodes( $post->post_content );
			echo '<p>' . esc_html( wp_trim_words( wp_strip_all_tags( excerpt_remove_blocks( $excerpt ) ), $words ) ) . '</p>';
		}
		echo '</div>';
	}

	/**
	 * Full content: Elementor content of a post built with Elementor, otherwise the_content.
	 */
	private function content( \WP_Post $post ) {
		$elementor = \Elementor\Plugin::$instance;
		$document  = $elementor->documents->get( $post->ID );
		if ( $document && $document->is_built_with_elementor() ) {
			return $elementor->frontend->get_builder_content_for_display( $post->ID, true );
		}
		if ( get_the_ID() === $post->ID && in_the_loop() ) {
			ob_start();
			the_content();
			return ob_get_clean();
		}
		$GLOBALS['post'] = $post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restored below.
		setup_postdata( $post );
		$html = apply_filters( 'the_content', $post->post_content ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- the core filter of the post content.
		wp_reset_postdata();
		return $html;
	}
}
