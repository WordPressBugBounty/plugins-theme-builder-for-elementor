<?php
/**
 * Dynamic tag: title of the current archive.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Controls_Manager;
use TBFE\Elementor\Context;


defined( 'ABSPATH' ) || exit;

class Archive_Title extends Base_Tag {

	public function get_name() {
		return 'tbfe-archive-title';
	}

	public function get_title() {
		return esc_html__( 'Archive Title', 'theme-builder-for-elementor' );
	}

	protected function register_controls() {
		$this->add_control(
			'prefix',
			array(
				'label' => esc_html__( 'Show prefix', 'theme-builder-for-elementor' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);
	}

	public function render() {
		list( $title ) = Context::archive_texts( 'yes' === $this->get_settings( 'prefix' ) );
		echo esc_html( $title );
	}
}
