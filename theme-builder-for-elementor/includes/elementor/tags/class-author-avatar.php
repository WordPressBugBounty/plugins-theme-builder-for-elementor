<?php
/**
 * Dynamic tag: profile picture of the author.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Modules\DynamicTags\Module;
use Elementor\Controls_Manager;
use TBFE\Elementor\Context;


defined( 'ABSPATH' ) || exit;

class Author_Avatar extends Base_Data_Tag {

	public function get_name() {
		return 'tbfe-author-avatar';
	}

	public function get_title() {
		return esc_html__( 'Author Avatar', 'theme-builder-for-elementor' );
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
		$author = Context::author_id();
		$url    = $author ? get_avatar_url( $author, array( 'size' => 300 ) ) : '';
		if ( $url ) {
			return array(
				'id'  => '',
				'url' => $url,
			);
		}
		$fallback = $this->get_settings( 'fallback' );
		return is_array( $fallback ) && ! empty( $fallback['url'] ) ? $fallback : array(
			'id'  => '',
			'url' => '',
		);
	}
}
