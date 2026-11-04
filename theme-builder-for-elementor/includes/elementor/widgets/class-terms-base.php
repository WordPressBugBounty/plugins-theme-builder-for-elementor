<?php
/**
 * Base of the term list widgets (categories, tags).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

abstract class Terms_Base extends Meta_Base {

	const IS_LIST  = true;
	const TAXONOMY = 'category';

	protected function register_value_controls() {
		$options = array();
		foreach ( get_taxonomies( array( 'public' => true, 'show_ui' => true ), 'objects' ) as $tax ) {
			$options[ $tax->name ] = $tax->labels->name;
		}
		$this->add_control(
			'taxonomy',
			array(
				'label'   => esc_html__( 'Taxonomy', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => static::TAXONOMY,
				'options' => $options,
			)
		);
	}

	protected function value_html( \WP_Post $post, array $settings ) {
		$taxonomy = ( ! empty( $settings['taxonomy'] ) && taxonomy_exists( $settings['taxonomy'] ) ) ? $settings['taxonomy'] : static::TAXONOMY;
		return $this->terms_html( $post, $taxonomy, $settings );
	}
}
