<?php
/**
 * Dynamic tag: categories, tags or other terms of the post.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Controls_Manager;


defined( 'ABSPATH' ) || exit;

class Post_Terms extends Base_Tag {

	public function get_name() {
		return 'tbfe-post-terms';
	}

	public function get_title() {
		return esc_html__( 'Post Terms', 'theme-builder-for-elementor' );
	}

	protected function register_controls() {
		$options = array();
		foreach ( get_taxonomies( array( 'public' => true ), 'objects' ) as $taxonomy ) {
			$options[ $taxonomy->name ] = $taxonomy->label;
		}
		$this->add_control(
			'taxonomy',
			array(
				'label'   => esc_html__( 'Taxonomy', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'category',
				'options' => $options,
			)
		);
		$this->add_control(
			'separator',
			array(
				'label'   => esc_html__( 'Separator', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => ', ',
			)
		);
	}

	public function render() {
		$post     = $this->post();
		$taxonomy = (string) $this->get_settings( 'taxonomy' );
		if ( ! $post || ! taxonomy_exists( $taxonomy ) ) {
			return;
		}
		$terms = get_the_terms( $post, $taxonomy );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return;
		}
		echo esc_html( implode( (string) $this->get_settings( 'separator' ), wp_list_pluck( $terms, 'name' ) ) );
	}
}
