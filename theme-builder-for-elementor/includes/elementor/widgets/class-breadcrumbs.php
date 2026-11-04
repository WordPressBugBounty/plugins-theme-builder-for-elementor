<?php
/**
 * Breadcrumbs. Uses Yoast SEO or Rank Math when they are active, otherwise builds its own trail.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use TBFE\Elementor\Context;

defined( 'ABSPATH' ) || exit;

class Breadcrumbs extends Base {

	public function get_name() {
		return 'tbfe-breadcrumbs';
	}

	public function get_title() {
		return esc_html__( 'Breadcrumbs', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-product-breadcrumbs';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_breadcrumbs', array( 'label' => esc_html__( 'Breadcrumbs', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'Source', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'auto',
				'options' => array(
					'auto'   => esc_html__( 'SEO plugin if active, else built-in', 'theme-builder-for-elementor' ),
					'plugin' => esc_html__( 'Built-in', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'home',
			array(
				'label'   => esc_html__( 'Home label', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Home', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'separator',
			array(
				'label'   => esc_html__( 'Separator', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '›',
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Breadcrumbs', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_align_control( 'align', '{{WRAPPER}} .tbfe-breadcrumbs' );
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .tbfe-breadcrumbs',
			)
		);
		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Text color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-breadcrumbs' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'link_color',
			array(
				'label'     => esc_html__( 'Link color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-breadcrumbs a' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'sep_color',
			array(
				'label'     => esc_html__( 'Separator color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-breadcrumbs__sep' => 'color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Built-in trail: array of [label, url] (the last item has no url).
	 */
	private function trail( $home ) {
		$items = array( array( $home, home_url( '/' ) ) );

		if ( Context::is_template_preview() ) {
			$post = Context::post();
			if ( $post ) {
				$items[] = array( get_the_title( $post ), '' );
			}
			return $items;
		}

		if ( is_front_page() ) {
			return array( array( $home, '' ) );
		}
		if ( is_home() ) {
			$items[] = array( get_the_title( (int) get_option( 'page_for_posts' ) ), '' );
		} elseif ( is_singular() ) {
			$post = get_queried_object();
			if ( 'post' === $post->post_type ) {
				$cats = get_the_category( $post->ID );
				if ( $cats ) {
					$cat = $cats[0];
					foreach ( array_reverse( get_ancestors( $cat->term_id, 'category' ) ) as $ancestor ) {
						$items[] = array( get_cat_name( $ancestor ), get_category_link( $ancestor ) );
					}
					$items[] = array( $cat->name, get_category_link( $cat ) );
				}
			} elseif ( 'page' !== $post->post_type ) {
				$pt = get_post_type_object( $post->post_type );
				if ( $pt && $pt->has_archive ) {
					$items[] = array( $pt->labels->name, get_post_type_archive_link( $post->post_type ) );
				}
			}
			foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor ) {
				$items[] = array( get_the_title( $ancestor ), get_permalink( $ancestor ) );
			}
			$items[] = array( get_the_title( $post ), '' );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$term = get_queried_object();
			foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy ) ) as $ancestor ) {
				$parent  = get_term( $ancestor, $term->taxonomy );
				$items[] = array( $parent->name, get_term_link( $parent ) );
			}
			$items[] = array( $term->name, '' );
		} elseif ( is_search() ) {
			/* translators: %s: search query */
			$items[] = array( sprintf( __( 'Search: %s', 'theme-builder-for-elementor' ), get_search_query() ), '' );
		} elseif ( is_404() ) {
			$items[] = array( __( 'Page not found', 'theme-builder-for-elementor' ), '' );
		} elseif ( is_archive() ) {
			add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
			$items[] = array( wp_strip_all_tags( get_the_archive_title() ), '' );
			remove_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
		}
		return $items;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( 'auto' === $settings['source'] && ! Context::is_template_preview() ) {
			if ( function_exists( 'yoast_breadcrumb' ) ) {
				yoast_breadcrumb( '<nav class="tbfe-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'theme-builder-for-elementor' ) . '">', '</nav>' );
				return;
			}
			if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
				echo '<nav class="tbfe-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'theme-builder-for-elementor' ) . '">';
				rank_math_the_breadcrumbs();
				echo '</nav>';
				return;
			}
		}

		$items = $this->trail( $settings['home'] );
		$sep   = '<span class="tbfe-breadcrumbs__sep" aria-hidden="true">' . esc_html( $settings['separator'] ) . '</span>';
		$parts = array();
		foreach ( $items as $item ) {
			$parts[] = $item[1] && ! is_wp_error( $item[1] )
				? sprintf( '<a href="%s">%s</a>', esc_url( $item[1] ), esc_html( $item[0] ) )
				: '<span aria-current="page">' . esc_html( $item[0] ) . '</span>';
		}
		echo '<nav class="tbfe-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'theme-builder-for-elementor' ) . '">' . wp_kses_post( implode( $sep, $parts ) ) . '</nav>';
	}
}
