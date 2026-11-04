<?php
/**
 * Share Buttons: share the post (or the current page) on social networks, by e-mail or by copying the link.
 *
 * The buttons are plain links: nothing is loaded from the networks until the visitor clicks.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

class Share_Buttons extends Base {

	const UNIVERSAL = true;

	public function get_name() {
		return 'tbfe-share-buttons';
	}

	public function get_title() {
		return esc_html__( 'Share Buttons', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-share';
	}

	public function get_keywords() {
		return array( 'share', 'social', 'facebook', 'twitter', 'linkedin', 'whatsapp' );
	}

	public function get_style_depends(): array {
		return array( 'tbfe-widgets', 'tbfe-share-buttons' );
	}

	public function get_script_depends() {
		return array( 'tbfe-share-buttons' );
	}

	/**
	 * Networks: label, Font Awesome icon, brand color, share URL ({url}, {title}, {image}).
	 */
	private static function networks() {
		return array(
			'facebook'  => array( 'Facebook', 'fab fa-facebook-f', '#1877f2', 'https://www.facebook.com/sharer/sharer.php?u={url}' ),
			'x'         => array( 'X', 'fab fa-x-twitter', '#000000', 'https://x.com/intent/post?url={url}&text={title}' ),
			'linkedin'  => array( 'LinkedIn', 'fab fa-linkedin-in', '#0a66c2', 'https://www.linkedin.com/sharing/share-offsite/?url={url}' ),
			'whatsapp'  => array( 'WhatsApp', 'fab fa-whatsapp', '#25d366', 'https://wa.me/?text={title}%20{url}' ),
			'telegram'  => array( 'Telegram', 'fab fa-telegram-plane', '#26a5e4', 'https://t.me/share/url?url={url}&text={title}' ),
			'pinterest' => array( 'Pinterest', 'fab fa-pinterest-p', '#e60023', 'https://pinterest.com/pin/create/button/?url={url}&media={image}&description={title}' ),
			'reddit'    => array( 'Reddit', 'fab fa-reddit-alien', '#ff4500', 'https://www.reddit.com/submit?url={url}&title={title}' ),
			'threads'   => array( 'Threads', 'fab fa-threads', '#000000', 'https://www.threads.net/intent/post?text={title}%20{url}' ),
			'email'     => array( __( 'E-mail', 'theme-builder-for-elementor' ), 'fas fa-envelope', '#5f6b7a', 'mailto:?subject={title}&body={url}' ),
			'copy'      => array( __( 'Copy link', 'theme-builder-for-elementor' ), 'fas fa-link', '#5f6b7a', '' ),
		);
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_buttons', array( 'label' => esc_html__( 'Share buttons', 'theme-builder-for-elementor' ) ) );

		$options = array();
		foreach ( self::networks() as $key => $network ) {
			$options[ $key ] = $network[0];
		}
		$repeater = new Repeater();
		$repeater->add_control(
			'network',
			array(
				'label'   => esc_html__( 'Network', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'facebook',
				'options' => $options,
			)
		);
		$repeater->add_control(
			'label',
			array(
				'label'       => esc_html__( 'Label', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Name of the network', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'buttons',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ network }}}',
				'default'     => array(
					array( 'network' => 'facebook' ),
					array( 'network' => 'x' ),
					array( 'network' => 'linkedin' ),
					array( 'network' => 'whatsapp' ),
					array( 'network' => 'copy' ),
				),
			)
		);
		$this->add_control(
			'view',
			array(
				'label'     => esc_html__( 'View', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'icon-text',
				'options'   => array(
					'icon-text' => esc_html__( 'Icon and label', 'theme-builder-for-elementor' ),
					'icon'      => esc_html__( 'Icon', 'theme-builder-for-elementor' ),
					'text'      => esc_html__( 'Label', 'theme-builder-for-elementor' ),
				),
				'separator' => 'before',
			)
		);
		$this->add_control(
			'shape',
			array(
				'label'   => esc_html__( 'Shape', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'rounded',
				'options' => array(
					'square'  => esc_html__( 'Square', 'theme-builder-for-elementor' ),
					'rounded' => esc_html__( 'Rounded', 'theme-builder-for-elementor' ),
					'pill'    => esc_html__( 'Pill', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'skin',
			array(
				'label'   => esc_html__( 'Colors', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'brand',
				'options' => array(
					'brand'   => esc_html__( 'Colors of the networks', 'theme-builder-for-elementor' ),
					'outline' => esc_html__( 'Outline in the network colors', 'theme-builder-for-elementor' ),
					'custom'  => esc_html__( 'Own colors', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'share_title',
			array(
				'label'       => esc_html__( 'Text before the buttons', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Share:', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'copied_text',
			array(
				'label'     => esc_html__( 'Text after copying', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Link copied', 'theme-builder-for-elementor' ),
				'separator' => 'before',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Buttons', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_align_control( 'align', '{{WRAPPER}} .tbfe-share', false, 'justify-content' );
		$this->add_responsive_control(
			'gap',
			array(
				'label'      => esc_html__( 'Space between', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 40 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-share' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'size',
			array(
				'label'      => esc_html__( 'Button height', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 24, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-share__button' => '--tbfe-share-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Icon size', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 8, 'max' => 40 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-share__icon' => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .tbfe-share__label',
			)
		);
		$this->start_controls_tabs( 'colors', array( 'condition' => array( 'skin' => 'custom' ) ) );
		$this->start_controls_tab( 'colors_normal', array( 'label' => esc_html__( 'Normal', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'bg',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-share--custom .tbfe-share__button' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Text and icon', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-share--custom .tbfe-share__button' => 'color: {{VALUE}}; border-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->start_controls_tab( 'colors_hover', array( 'label' => esc_html__( 'Hover', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'bg_hover',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-share--custom .tbfe-share__button:hover, {{WRAPPER}} .tbfe-share--custom .tbfe-share__button:focus-visible' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'text_color_hover',
			array(
				'label'     => esc_html__( 'Text and icon', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-share--custom .tbfe-share__button:hover, {{WRAPPER}} .tbfe-share--custom .tbfe-share__button:focus-visible' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text before the buttons', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .tbfe-share__title' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * URL, title and image to share: the post on singular pages and in post cards, else the current page.
	 */
	private function target() {
		$post = $this->post();
		if ( $post && ( is_singular() || in_the_loop() || $this->is_editor() ) ) {
			$image = get_the_post_thumbnail_url( $post, 'large' );
			return array( get_permalink( $post ), get_the_title( $post ), $image ? $image : '' );
		}
		global $wp;
		$url = home_url( add_query_arg( array(), isset( $wp->request ) ? $wp->request : '' ) );
		return array( trailingslashit( $url ), wp_get_document_title(), '' );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$networks = self::networks();
		list( $url, $title, $image ) = $this->target();

		$view    = in_array( $settings['view'], array( 'icon-text', 'icon', 'text' ), true ) ? $settings['view'] : 'icon-text';
		$classes = array(
			'tbfe-share',
			'tbfe-share--' . ( in_array( $settings['skin'], array( 'brand', 'outline', 'custom' ), true ) ? $settings['skin'] : 'brand' ),
			'tbfe-share--' . ( in_array( $settings['shape'], array( 'square', 'rounded', 'pill' ), true ) ? $settings['shape'] : 'rounded' ),
			'tbfe-share--view-' . $view,
		);

		echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';
		if ( ! empty( $settings['share_title'] ) ) {
			echo '<span class="tbfe-share__title">' . esc_html( $settings['share_title'] ) . '</span>';
		}
		foreach ( (array) $settings['buttons'] as $button ) {
			if ( ! isset( $networks[ $button['network'] ] ) ) {
				continue;
			}
			list( $name, $icon, $color, $pattern ) = $networks[ $button['network'] ];
			$label = '' !== (string) $button['label'] ? (string) $button['label'] : $name;

			$inner = '';
			if ( 'text' !== $view ) {
				ob_start();
				Icons_Manager::render_icon(
					array(
						'value'   => $icon,
						'library' => 0 === strpos( $icon, 'fab' ) ? 'fa-brands' : 'fa-solid',
					),
					array( 'aria-hidden' => 'true' )
				);
				$inner .= '<span class="tbfe-share__icon" aria-hidden="true">' . ob_get_clean() . '</span>';
			}
			$inner .= '<span class="tbfe-share__label' . ( 'icon' === $view ? ' screen-reader-text' : '' ) . '">' . esc_html( $label ) . '</span>';
			$style  = '--tbfe-share-color:' . $color;

			if ( 'copy' === $button['network'] ) {
				printf(
					'<button type="button" class="tbfe-share__button tbfe-share__button--copy" style="%1$s" data-url="%2$s" data-copied="%3$s">%4$s</button>',
					esc_attr( $style ),
					esc_url( $url ),
					esc_attr( $settings['copied_text'] ),
					$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon from Elementor, label escaped.
				);
				continue;
			}
			$href = strtr(
				$pattern,
				array(
					'{url}'   => rawurlencode( $url ),
					'{title}' => rawurlencode( wp_strip_all_tags( $title ) ),
					'{image}' => rawurlencode( $image ),
				)
			);
			printf(
				'<a class="tbfe-share__button tbfe-share__button--%1$s" style="%2$s" href="%3$s"%4$s>%5$s</a>',
				esc_attr( $button['network'] ),
				esc_attr( $style ),
				esc_url( $href, array( 'http', 'https', 'mailto' ) ),
				'email' === $button['network'] ? '' : ' target="_blank" rel="noopener nofollow"',
				$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon from Elementor, label escaped.
			);
		}
		echo '</div>';
	}
}
