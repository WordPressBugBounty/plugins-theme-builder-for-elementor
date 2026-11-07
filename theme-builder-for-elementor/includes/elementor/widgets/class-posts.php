<?php
/**
 * List of posts: the posts of the current archive or a custom query, as built-in cards or with a Post Card template.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use TBFE\Conditions\Conditions;
use TBFE\Elementor\Context;
use TBFE\Render\Renderer;
use TBFE\Templates\Post_Type;

defined( 'ABSPATH' ) || exit;

class Posts extends Base {

	public function get_name() {
		return 'tbfe-posts';
	}

	public function get_title() {
		return esc_html__( 'Posts', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_keywords() {
		return array( 'posts', 'blog', 'archive', 'loop', 'grid', 'list' );
	}

	private static function card_templates() {
		$options = array( '' => esc_html__( 'Built-in card', 'theme-builder-for-elementor' ) );
		$posts   = get_posts(
			array(
				'post_type'      => Post_Type::NAME,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'meta_key'       => '_tbfe_type', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => 'loop-item', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		foreach ( $posts as $post ) {
			$options[ $post->ID ] = get_the_title( $post );
		}
		return $options;
	}

	protected function register_controls() {
		// Query.
		$this->start_controls_section( 'section_query', array( 'label' => esc_html__( 'Query', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'source',
			array(
				'label'       => esc_html__( 'Posts', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'current',
				'options'     => array(
					'current' => esc_html__( 'Current query (archive, blog, search)', 'theme-builder-for-elementor' ),
					'related' => esc_html__( 'Related to the current post', 'theme-builder-for-elementor' ),
					'custom'  => esc_html__( 'Custom', 'theme-builder-for-elementor' ),
				),
				'description' => esc_html__( 'In an Archive template use the current query, so the list follows the category, tag or search.', 'theme-builder-for-elementor' ),
			)
		);
		$types = array();
		foreach ( Conditions::post_types() as $name => $obj ) {
			$types[ $name ] = $obj->labels->name;
		}
		$this->add_control(
			'post_type',
			array(
				'label'     => esc_html__( 'Post type', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'post',
				'options'   => $types,
				'condition' => array( 'source' => 'custom' ),
			)
		);
		$this->add_control(
			'posts_per_page',
			array(
				'label'     => esc_html__( 'Number of posts', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 6,
				'min'       => 1,
				'max'       => 100,
				'condition' => array( 'source' => array( 'custom', 'related' ) ),
			)
		);
		$this->add_control(
			'terms',
			array(
				'label'       => esc_html__( 'Category or tag IDs', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => '12, 15',
				'description' => esc_html__( 'Only posts with these terms (comma separated IDs).', 'theme-builder-for-elementor' ),
				'condition'   => array( 'source' => 'custom' ),
			)
		);
		$this->add_control(
			'orderby',
			array(
				'label'     => esc_html__( 'Order by', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => array(
					'date'          => esc_html__( 'Date', 'theme-builder-for-elementor' ),
					'modified'      => esc_html__( 'Last modified', 'theme-builder-for-elementor' ),
					'title'         => esc_html__( 'Title', 'theme-builder-for-elementor' ),
					'comment_count' => esc_html__( 'Comments', 'theme-builder-for-elementor' ),
					'menu_order'    => esc_html__( 'Menu order', 'theme-builder-for-elementor' ),
					'rand'          => esc_html__( 'Random', 'theme-builder-for-elementor' ),
				),
				'condition' => array( 'source' => array( 'custom', 'related' ) ),
			)
		);
		$this->add_control(
			'order',
			array(
				'label'     => esc_html__( 'Order', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'DESC',
				'options'   => array(
					'DESC' => esc_html__( 'Descending', 'theme-builder-for-elementor' ),
					'ASC'  => esc_html__( 'Ascending', 'theme-builder-for-elementor' ),
				),
				'condition' => array( 'source' => array( 'custom', 'related' ) ),
			)
		);
		$this->add_control(
			'offset',
			array(
				'label'     => esc_html__( 'Skip posts', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 0,
				'min'       => 0,
				'condition' => array( 'source' => array( 'custom', 'related' ) ),
			)
		);
		$this->add_control(
			'related_by',
			array(
				'label'       => esc_html__( 'Related by', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'category',
				'options'     => array(
					'category' => esc_html__( 'Same category', 'theme-builder-for-elementor' ),
					'post_tag' => esc_html__( 'Same tag', 'theme-builder-for-elementor' ),
					'both'     => esc_html__( 'Same category or tag', 'theme-builder-for-elementor' ),
				),
				'description' => esc_html__( 'Without related posts the newest posts are shown.', 'theme-builder-for-elementor' ),
				'condition'   => array( 'source' => 'related' ),
			)
		);
		$this->add_control(
			'exclude_current',
			array(
				'label'     => esc_html__( 'Exclude the current post', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array( 'source' => 'custom' ),
			)
		);
		$this->add_control(
			'nothing_found',
			array(
				'label'   => esc_html__( 'Text when nothing is found', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Nothing found.', 'theme-builder-for-elementor' ),
			)
		);
		$this->end_controls_section();

		// Layout.
		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'Layout', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'card_template',
			array(
				'label'       => esc_html__( 'Card', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => self::card_templates(),
				'description' => esc_html__( 'Design your own card as a Post Card template in Theme Builder.', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_responsive_control(
			'columns',
			array(
				'label'          => esc_html__( 'Columns', 'theme-builder-for-elementor' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array_combine( range( 1, 6 ), range( 1, 6 ) ),
				'selectors'      => array( '{{WRAPPER}} .tbfe-posts' => '--tbfe-columns: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'column_gap',
			array(
				'label'      => esc_html__( 'Column gap', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 30,
				),
				'selectors'  => array( '{{WRAPPER}} .tbfe-posts' => 'column-gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'row_gap',
			array(
				'label'      => esc_html__( 'Row gap', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 30,
				),
				'selectors'  => array( '{{WRAPPER}} .tbfe-posts' => 'row-gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$card = array( 'card_template' => '' );
		$this->add_control(
			'show_image',
			array(
				'label'     => esc_html__( 'Image', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'separator' => 'before',
				'condition' => $card,
			)
		);
		$this->add_control(
			'image_size',
			array(
				'label'     => esc_html__( 'Image size', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'medium_large',
				'options'   => array_combine( array_merge( get_intermediate_image_sizes(), array( 'full' ) ), array_merge( get_intermediate_image_sizes(), array( 'full' ) ) ),
				'condition' => array_merge( $card, array( 'show_image' => 'yes' ) ),
			)
		);
		$this->add_responsive_control(
			'image_ratio',
			array(
				'label'     => esc_html__( 'Image ratio', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '16 / 10',
				'options'   => array(
					'auto'   => esc_html__( 'Original', 'theme-builder-for-elementor' ),
					'1 / 1'  => '1:1',
					'4 / 3'  => '4:3',
					'16 / 10' => '16:10',
					'16 / 9' => '16:9',
					'3 / 4'  => '3:4',
				),
				'selectors' => array( '{{WRAPPER}} .tbfe-card__image img' => 'aspect-ratio: {{VALUE}};' ),
				'condition' => array_merge( $card, array( 'show_image' => 'yes' ) ),
			)
		);
		$this->add_tag_control( 'title_tag', 'h3', esc_html__( 'Title tag', 'theme-builder-for-elementor' ) );
		$this->update_control( 'title_tag', array( 'condition' => $card ) );
		$this->add_control(
			'show_meta',
			array(
				'label'     => esc_html__( 'Date and author', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => $card,
			)
		);
		$this->add_control(
			'show_excerpt',
			array(
				'label'     => esc_html__( 'Excerpt', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => $card,
			)
		);
		$this->add_control(
			'excerpt_length',
			array(
				'label'     => esc_html__( 'Excerpt words', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 20,
				'min'       => 5,
				'max'       => 100,
				'condition' => array_merge( $card, array( 'show_excerpt' => 'yes' ) ),
			)
		);
		$this->add_control(
			'read_more',
			array(
				'label'     => esc_html__( 'Read more text', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Read more', 'theme-builder-for-elementor' ),
				'condition' => $card,
			)
		);
		$this->end_controls_section();

		// Pagination.
		$this->start_controls_section( 'section_pagination', array( 'label' => esc_html__( 'Pagination', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'pagination',
			array(
				'label'   => esc_html__( 'Pagination', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'numbers',
				'options' => array(
					''          => esc_html__( 'None', 'theme-builder-for-elementor' ),
					'numbers'   => esc_html__( 'Numbers', 'theme-builder-for-elementor' ),
					'prev_next' => esc_html__( 'Previous / Next', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_align_control( 'pagination_align', '{{WRAPPER}} .tbfe-pagination .nav-links', false, 'justify-content' );
		$this->end_controls_section();

		// Style: card.
		$this->start_controls_section(
			'section_card_style',
			array(
				'label'     => esc_html__( 'Card', 'theme-builder-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => $card,
			)
		);
		$this->add_control(
			'card_bg',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-card' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .tbfe-card',
			)
		);
		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .tbfe-card',
			)
		);
		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => esc_html__( 'Content padding', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-card__body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'card_title_typography',
				'label'     => esc_html__( 'Title typography', 'theme-builder-for-elementor' ),
				'selector'  => '{{WRAPPER}} .tbfe-card__title',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'card_title_color',
			array(
				'label'     => esc_html__( 'Title color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-card__title a' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'card_title_hover',
			array(
				'label'     => esc_html__( 'Title hover color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-card__title a:hover' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'card_meta_color',
			array(
				'label'     => esc_html__( 'Meta color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-card__meta' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'card_text_color',
			array(
				'label'     => esc_html__( 'Excerpt color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-card__excerpt' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'card_link_color',
			array(
				'label'     => esc_html__( 'Read more color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-card__more' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();

		// Style: pagination.
		$this->start_controls_section(
			'section_pagination_style',
			array(
				'label'     => esc_html__( 'Pagination', 'theme-builder-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'pagination!' => '' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'pagination_typography',
				'selector' => '{{WRAPPER}} .tbfe-pagination .page-numbers',
			)
		);
		$this->add_control(
			'pagination_color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-pagination .page-numbers' => 'color: {{VALUE}}; border-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'pagination_active',
			array(
				'label'     => esc_html__( 'Active color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-pagination .page-numbers.current' => 'background-color: {{VALUE}}; border-color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'pagination_spacing',
			array(
				'label'      => esc_html__( 'Space above', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-pagination' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * The query of the widget.
	 *
	 * @return \WP_Query
	 */
	private function query( array $settings ) {
		global $wp_query;

		$source  = isset( $settings['source'] ) ? $settings['source'] : 'current';
		$current = ! in_array( $source, array( 'custom', 'related' ), true );
		if ( $current && ! Context::is_template_preview() && ( is_archive() || is_home() || is_search() ) ) {
			return $wp_query;
		}

		$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$args  = array(
			'post_type'           => $current ? 'post' : ( isset( $settings['post_type'] ) && post_type_exists( $settings['post_type'] ) ? $settings['post_type'] : 'post' ),
			'post_status'         => 'publish',
			'posts_per_page'      => $current ? (int) get_option( 'posts_per_page' ) : max( 1, min( 100, (int) $settings['posts_per_page'] ) ),
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
		);
		if ( ! $current ) {
			$allowed_orderby = array( 'date', 'modified', 'title', 'comment_count', 'menu_order', 'rand' );
			$args['orderby'] = in_array( $settings['orderby'], $allowed_orderby, true ) ? $settings['orderby'] : 'date';
			$args['order']   = 'ASC' === $settings['order'] ? 'ASC' : 'DESC';
			$offset          = max( 0, (int) $settings['offset'] );
			if ( $offset ) {
				$args['offset'] = $offset + ( $paged - 1 ) * $args['posts_per_page'];
			}
			$terms = array_filter( array_map( 'absint', explode( ',', (string) $settings['terms'] ) ) );
			if ( $terms ) {
				$by_tax = array();
				foreach ( $terms as $term_id ) {
					$term = get_term( $term_id );
					if ( $term && ! is_wp_error( $term ) ) {
						$by_tax[ $term->taxonomy ][] = $term_id;
					}
				}
				$args['tax_query'] = array( 'relation' => 'OR' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				foreach ( $by_tax as $tax => $ids ) {
					$args['tax_query'][] = array(
						'taxonomy' => $tax,
						'terms'    => $ids,
					);
				}
			}
			if ( ! empty( $settings['exclude_current'] ) && is_singular() ) {
				$args['post__not_in'] = array( get_queried_object_id() ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
			}
			if ( 'related' === $source ) {
				$args = $this->related_args( $args, $settings );
			}
		}
		return new \WP_Query( apply_filters( 'tbfe/posts_widget_query', $args, $settings, $this ) );
	}

	/**
	 * Posts with the same category and/or tag as the current post (of its post type, without it).
	 */
	private function related_args( array $args, array $settings ) {
		$post = Context::post();
		if ( ! $post ) {
			return $args;
		}
		$args['post_type']    = $post->post_type;
		$args['post__not_in'] = array( $post->ID ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
		unset( $args['tax_query'] );

		$by         = isset( $settings['related_by'] ) ? $settings['related_by'] : 'category';
		$taxonomies = 'both' === $by ? array( 'category', 'post_tag' ) : array( 'post_tag' === $by ? 'post_tag' : 'category' );
		$tax_query  = array( 'relation' => 'OR' );
		foreach ( $taxonomies as $taxonomy ) {
			$ids = wp_get_post_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
			if ( $ids && ! is_wp_error( $ids ) ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'terms'    => $ids,
				);
			}
		}
		if ( count( $tax_query ) > 1 ) {
			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}
		return $args;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$query    = $this->query( $settings );
		$template = isset( $settings['card_template'] ) ? absint( $settings['card_template'] ) : 0;
		$template = ( $template && 'publish' === get_post_status( $template ) && Post_Type::NAME === get_post_type( $template ) ) ? $template : 0;
		$is_main  = $query === $GLOBALS['wp_query'];

		if ( ! $query->have_posts() ) {
			echo '<p class="tbfe-posts__empty">' . esc_html( $settings['nothing_found'] ) . '</p>';
			return;
		}

		echo '<div class="tbfe-posts' . ( $template ? ' tbfe-posts--template' : '' ) . '">';
		while ( $query->have_posts() ) {
			$query->the_post();
			echo '<article class="' . esc_attr( implode( ' ', Renderer::post_class( 'tbfe-posts__item' ) ) ) . '">';
			if ( $template ) {
				Renderer::render_template( $template );
			} else {
				$this->render_card( $settings );
			}
			echo '</article>';
		}
		echo '</div>';

		$this->render_pagination( $settings, $query );

		if ( $is_main ) {
			$query->rewind_posts();
		}
		wp_reset_postdata();
	}

	private function render_card( array $settings ) {
		$tag = self::safe_tag( isset( $settings['title_tag'] ) ? $settings['title_tag'] : 'h3', 'h3' );
		echo '<div class="tbfe-card">';
		if ( ! empty( $settings['show_image'] ) && has_post_thumbnail() ) {
			printf(
				'<a class="tbfe-card__image" href="%s" aria-hidden="true" tabindex="-1">%s</a>',
				esc_url( get_permalink() ),
				get_the_post_thumbnail( null, sanitize_key( $settings['image_size'] ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core markup.
			);
		}
		echo '<div class="tbfe-card__body">';
		if ( ! empty( $settings['show_meta'] ) ) {
			printf(
				'<div class="tbfe-card__meta"><time datetime="%s">%s</time> · <span>%s</span></div>',
				esc_attr( get_the_date( 'c' ) ),
				esc_html( get_the_date() ),
				esc_html( get_the_author() )
			);
		}
		printf( '<%1$s class="tbfe-card__title"><a href="%2$s">%3$s</a></%1$s>', $tag, esc_url( get_permalink() ), esc_html( get_the_title() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted tag.
		if ( ! empty( $settings['show_excerpt'] ) ) {
			$words = max( 5, (int) $settings['excerpt_length'] );
			echo '<p class="tbfe-card__excerpt">' . esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), $words ) ) . '</p>';
		}
		if ( ! empty( $settings['read_more'] ) ) {
			printf(
				'<a class="tbfe-card__more" href="%s" aria-label="%s">%s</a>',
				esc_url( get_permalink() ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'Read more about %s', 'theme-builder-for-elementor' ), get_the_title() ) ),
				esc_html( $settings['read_more'] )
			);
		}
		echo '</div></div>';
	}

	private function render_pagination( array $settings, \WP_Query $query ) {
		if ( empty( $settings['pagination'] ) || $query->max_num_pages < 2 ) {
			return;
		}
		$current = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		if ( 'prev_next' === $settings['pagination'] ) {
			$links = paginate_links(
				array(
					'total'     => $query->max_num_pages,
					'current'   => $current,
					'type'      => 'array',
					'prev_text' => esc_html__( '← Newer', 'theme-builder-for-elementor' ),
					'next_text' => esc_html__( 'Older →', 'theme-builder-for-elementor' ),
				)
			);
			$links = array_filter(
				(array) $links,
				static function ( $link ) {
					return false !== strpos( $link, 'prev' ) || false !== strpos( $link, 'next' );
				}
			);
		} else {
			$links = paginate_links(
				array(
					'total'     => $query->max_num_pages,
					'current'   => $current,
					'type'      => 'array',
					'mid_size'  => 2,
					'prev_text' => '<span aria-hidden="true">&larr;</span><span class="screen-reader-text">' . esc_html__( 'Previous', 'theme-builder-for-elementor' ) . '</span>',
					'next_text' => '<span aria-hidden="true">&rarr;</span><span class="screen-reader-text">' . esc_html__( 'Next', 'theme-builder-for-elementor' ) . '</span>',
				)
			);
		}
		if ( ! $links ) {
			return;
		}
		echo '<nav class="tbfe-pagination" aria-label="' . esc_attr__( 'Posts', 'theme-builder-for-elementor' ) . '"><div class="nav-links">' . wp_kses_post( implode( '', $links ) ) . '</div></nav>';
	}
}
