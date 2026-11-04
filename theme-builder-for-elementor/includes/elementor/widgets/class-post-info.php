<?php
/**
 * Post Info: one line (or a list) with the date, author, terms, comments, reading time and own texts of the post.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use TBFE\Elementor\Context;

defined( 'ABSPATH' ) || exit;

class Post_Info extends Base {

	public function get_name() {
		return 'tbfe-post-info';
	}

	public function get_title() {
		return esc_html__( 'Post Info', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-post-info';
	}

	public function get_keywords() {
		return array( 'meta', 'date', 'author', 'reading time', 'comments', 'info' );
	}

	/**
	 * Item types.
	 */
	private static function types() {
		return array(
			'date'         => esc_html__( 'Date', 'theme-builder-for-elementor' ),
			'modified'     => esc_html__( 'Last modified', 'theme-builder-for-elementor' ),
			'author'       => esc_html__( 'Author', 'theme-builder-for-elementor' ),
			'categories'   => esc_html__( 'Categories', 'theme-builder-for-elementor' ),
			'tags'         => esc_html__( 'Tags', 'theme-builder-for-elementor' ),
			'comments'     => esc_html__( 'Comments', 'theme-builder-for-elementor' ),
			'reading_time' => esc_html__( 'Reading time', 'theme-builder-for-elementor' ),
			'custom'       => esc_html__( 'Own text', 'theme-builder-for-elementor' ),
		);
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_items', array( 'label' => esc_html__( 'Items', 'theme-builder-for-elementor' ) ) );

		$repeater = new Repeater();
		$repeater->add_control(
			'type',
			array(
				'label'   => esc_html__( 'Show', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => self::types(),
			)
		);
		$repeater->add_control(
			'before',
			array(
				'label' => esc_html__( 'Text before', 'theme-builder-for-elementor' ),
				'type'  => Controls_Manager::TEXT,
			)
		);
		$repeater->add_control(
			'date_format',
			array(
				'label'       => esc_html__( 'Date format', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => get_option( 'date_format' ),
				'condition'   => array( 'type' => array( 'date', 'modified' ) ),
			)
		);
		$repeater->add_control(
			'avatar',
			array(
				'label'     => esc_html__( 'Show avatar', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array( 'type' => 'author' ),
			)
		);
		$repeater->add_control(
			'reading_format',
			array(
				'label'       => esc_html__( 'Text', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				/* translators: %s: minutes */
				'default'     => esc_html__( '%s min read', 'theme-builder-for-elementor' ),
				/* translators: Help of the field above; keep %s, it stands for the value. */
				'description' => esc_html__( '%s is replaced with the minutes.', 'theme-builder-for-elementor' ),
				'condition'   => array( 'type' => 'reading_time' ),
			)
		);
		$repeater->add_control(
			'comments_none',
			array(
				'label'     => esc_html__( 'No comments', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'No comments', 'theme-builder-for-elementor' ),
				'condition' => array( 'type' => 'comments' ),
			)
		);
		$repeater->add_control(
			'comments_one',
			array(
				'label'     => esc_html__( 'One comment', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( '1 comment', 'theme-builder-for-elementor' ),
				'condition' => array( 'type' => 'comments' ),
			)
		);
		$repeater->add_control(
			'comments_many',
			array(
				'label'     => esc_html__( 'More comments', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				/* translators: %s: number of comments */
				'default'   => esc_html__( '%s comments', 'theme-builder-for-elementor' ),
				'condition' => array( 'type' => 'comments' ),
			)
		);
		$repeater->add_control(
			'text',
			array(
				'label'     => esc_html__( 'Text', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'type' => 'custom' ),
			)
		);
		$repeater->add_control(
			'link',
			array(
				'label'     => esc_html__( 'Link', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'type!' => array( 'reading_time', 'custom' ) ),
			)
		);
		$repeater->add_control(
			'icon',
			array(
				'label' => esc_html__( 'Icon', 'theme-builder-for-elementor' ),
				'type'  => Controls_Manager::ICONS,
			)
		);

		$this->add_control(
			'items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ type }}}',
				'default'     => array(
					array(
						'type' => 'author',
						'icon' => array(
							'value'   => 'fas fa-user',
							'library' => 'fa-solid',
						),
					),
					array(
						'type' => 'date',
						'icon' => array(
							'value'   => 'fas fa-calendar',
							'library' => 'fa-solid',
						),
					),
					array(
						'type' => 'reading_time',
						'icon' => array(
							'value'   => 'fas fa-clock',
							'library' => 'fa-solid',
						),
					),
				),
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'     => esc_html__( 'Layout', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'inline',
				'options'   => array(
					'inline' => esc_html__( 'In a row', 'theme-builder-for-elementor' ),
					'list'   => esc_html__( 'Below each other', 'theme-builder-for-elementor' ),
				),
				'separator' => 'before',
			)
		);
		$this->add_control(
			'separator',
			array(
				'label'     => esc_html__( 'Separator', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '·',
				'condition' => array( 'layout' => 'inline' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Items', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_align_control( 'align', '{{WRAPPER}} .tbfe-post-info', false, 'justify-content' );
		$this->add_responsive_control(
			'gap',
			array(
				'label'      => esc_html__( 'Space between', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'range'      => array( 'px' => array( 'max' => 60 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-post-info' => '--tbfe-info-gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .tbfe-post-info__item',
			)
		);
		$this->add_control(
			'color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-post-info__item, {{WRAPPER}} .tbfe-post-info__item a' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'color_hover',
			array(
				'label'     => esc_html__( 'Link hover color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-post-info__item a:hover, {{WRAPPER}} .tbfe-post-info__item a:focus' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'before_color',
			array(
				'label'     => esc_html__( 'Text before color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-post-info__before' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'separator_color',
			array(
				'label'     => esc_html__( 'Separator color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-post-info__item + .tbfe-post-info__item::before' => 'color: {{VALUE}};' ),
				'condition' => array( 'layout' => 'inline' ),
			)
		);
		$this->add_control(
			'icon_color',
			array(
				'label'     => esc_html__( 'Icon color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .tbfe-post-info__icon' => 'color: {{VALUE}}; fill: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'icon_size',
			array(
				'label'      => esc_html__( 'Icon size', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-post-info__icon' => 'font-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'avatar_size',
			array(
				'label'      => esc_html__( 'Avatar size', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 16, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-post-info__avatar' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Value HTML of one item (escaped), '' when the post has nothing for it.
	 */
	private function item_html( array $item, \WP_Post $post ) {
		$link = ! empty( $item['link'] );
		switch ( $item['type'] ) {
			case 'date':
			case 'modified':
				$format   = ! empty( $item['date_format'] ) ? $item['date_format'] : get_option( 'date_format' );
				$modified = 'modified' === $item['type'];
				$html     = sprintf(
					'<time datetime="%s">%s</time>',
					esc_attr( $modified ? get_the_modified_date( 'c', $post ) : get_the_date( 'c', $post ) ),
					esc_html( $modified ? get_the_modified_date( $format, $post ) : get_the_date( $format, $post ) )
				);
				return $link ? sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $post ) ), $html ) : $html;

			case 'author':
				$author = (int) $post->post_author;
				$html   = esc_html( get_the_author_meta( 'display_name', $author ) );
				if ( ! empty( $item['avatar'] ) ) {
					$html = get_avatar( $author, 48, '', '', array( 'class' => 'tbfe-post-info__avatar' ) ) . $html;
				}
				return $link ? sprintf( '<a href="%s">%s</a>', esc_url( get_author_posts_url( $author ) ), $html ) : $html;

			case 'categories':
			case 'tags':
				$terms = get_the_terms( $post, 'tags' === $item['type'] ? 'post_tag' : 'category' );
				if ( ! $terms || is_wp_error( $terms ) ) {
					return '';
				}
				$out = array();
				foreach ( $terms as $term ) {
					$url   = get_term_link( $term );
					$out[] = $link && ! is_wp_error( $url ) ? sprintf( '<a href="%s" rel="tag">%s</a>', esc_url( $url ), esc_html( $term->name ) ) : esc_html( $term->name );
				}
				return implode( ', ', $out );

			case 'comments':
				if ( ! comments_open( $post ) && ! get_comments_number( $post ) ) {
					return '';
				}
				$count = (int) get_comments_number( $post );
				$key   = 0 === $count ? 'comments_none' : ( 1 === $count ? 'comments_one' : 'comments_many' );
				$html  = esc_html( str_replace( '%s', number_format_i18n( $count ), (string) $item[ $key ] ) );
				return $link ? sprintf( '<a href="%s">%s</a>', esc_url( get_comments_link( $post ) ), $html ) : $html;

			case 'reading_time':
				$minutes = Context::reading_time( $post );
				return esc_html( str_replace( '%s', number_format_i18n( $minutes ), (string) $item['reading_format'] ) );

			case 'custom':
				return esc_html( (string) $item['text'] );
		}
		return '';
	}

	protected function render() {
		$post = $this->post();
		if ( ! $post ) {
			$this->editor_notice( __( 'No post to show.', 'theme-builder-for-elementor' ) );
			return;
		}
		$settings = $this->get_settings_for_display();
		$items    = array();
		foreach ( (array) $settings['items'] as $item ) {
			$value = $this->item_html( $item, $post );
			if ( '' === $value ) {
				continue;
			}
			$icon = '';
			if ( ! empty( $item['icon']['value'] ) ) {
				ob_start();
				Icons_Manager::render_icon( $item['icon'], array( 'aria-hidden' => 'true' ) );
				$icon = '<span class="tbfe-post-info__icon" aria-hidden="true">' . ob_get_clean() . '</span>';
			}
			$before  = ! empty( $item['before'] ) ? '<span class="tbfe-post-info__before">' . esc_html( $item['before'] ) . '</span>' : '';
			$items[] = '<li class="tbfe-post-info__item tbfe-post-info__item--' . esc_attr( $item['type'] ) . '">' . $icon . $before . '<span class="tbfe-post-info__value">' . $value . '</span></li>';
		}
		if ( ! $items ) {
			$this->editor_notice( __( 'This post has nothing to show here.', 'theme-builder-for-elementor' ) );
			return;
		}
		$list = 'list' === $settings['layout'];
		printf(
			'<ul class="tbfe-post-info%1$s" style="--tbfe-info-sep:%2$s">%3$s</ul>',
			$list ? ' tbfe-post-info--list' : '',
			esc_attr( wp_json_encode( (string) $settings['separator'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ),
			implode( '', $items ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts above.
		);
	}
}
