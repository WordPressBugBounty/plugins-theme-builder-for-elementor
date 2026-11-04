<?php
/**
 * Dynamic tag: how long it takes to read the post.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Tags;

use Elementor\Controls_Manager;
use TBFE\Elementor\Context;


defined( 'ABSPATH' ) || exit;

class Reading_Time extends Base_Tag {

	public function get_name() {
		return 'tbfe-reading-time';
	}

	public function get_title() {
		return esc_html__( 'Reading Time', 'theme-builder-for-elementor' );
	}

	protected function register_controls() {
		$this->add_control(
			'format',
			array(
				'label'       => esc_html__( 'Text', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				/* translators: %s: minutes */
				'default'     => esc_html__( '%s min read', 'theme-builder-for-elementor' ),
				/* translators: Help of the field above; keep %s, it stands for the value. */
				'description' => esc_html__( '%s is replaced with the minutes.', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'wpm',
			array(
				'label'   => esc_html__( 'Words per minute', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 200,
				'min'     => 50,
			)
		);
	}

	public function render() {
		$post = $this->post();
		if ( ! $post ) {
			return;
		}
		$minutes = Context::reading_time( $post, (int) $this->get_settings( 'wpm' ) );
		echo esc_html( str_replace( '%s', number_format_i18n( $minutes ), (string) $this->get_settings( 'format' ) ) );
	}
}
