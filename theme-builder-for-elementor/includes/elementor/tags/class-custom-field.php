<?php
/**
 * Dynamic tag: value of a custom field of the post (also for links, numbers and colors).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module;


defined( 'ABSPATH' ) || exit;

class Custom_Field extends Base_Tag {

	public function get_name() {
		return 'tbfe-custom-field';
	}

	public function get_title() {
		return esc_html__( 'Custom Field', 'theme-builder-for-elementor' );
	}

	public function get_categories() {
		return array( Module::TEXT_CATEGORY, Module::URL_CATEGORY, Module::NUMBER_CATEGORY, Module::POST_META_CATEGORY, Module::COLOR_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control(
			'key',
			array(
				'label'       => esc_html__( 'Field name', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => 'price',
				'description' => esc_html__( 'The meta key of the field. Hidden fields (starting with _) are not shown.', 'theme-builder-for-elementor' ),
			)
		);
	}

	public function render() {
		$post = $this->post();
		$key  = sanitize_text_field( (string) $this->get_settings( 'key' ) );
		if ( ! $post || '' === $key || is_protected_meta( $key, 'post' ) ) {
			return;
		}
		$value = get_post_meta( $post->ID, $key, true );
		echo is_scalar( $value ) ? esc_html( (string) $value ) : '';
	}
}
