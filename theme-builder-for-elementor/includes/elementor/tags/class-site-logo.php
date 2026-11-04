<?php
/**
 * Dynamic tag: logo of the site (Customizer or site editor).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;


defined( 'ABSPATH' ) || exit;

class Site_Logo extends Base_Data_Tag {

	public function get_name() {
		return 'tbfe-site-logo';
	}

	public function get_title() {
		return esc_html__( 'Site Logo', 'theme-builder-for-elementor' );
	}

	public function get_categories() {
		return array( Module::IMAGE_CATEGORY );
	}

	protected function register_controls() {
		$this->add_control(
			'fallback',
			array(
				'label' => esc_html__( 'Fallback image', 'theme-builder-for-elementor' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
	}

	protected function get_value( array $options = array() ) {
		$id = (int) get_theme_mod( 'custom_logo' );
		if ( $id ) {
			return array(
				'id'  => $id,
				'url' => wp_get_attachment_image_url( $id, 'full' ),
			);
		}
		$fallback = $this->get_settings( 'fallback' );
		return is_array( $fallback ) && ! empty( $fallback['url'] ) ? $fallback : array(
			'id'  => '',
			'url' => '',
		);
	}
}
