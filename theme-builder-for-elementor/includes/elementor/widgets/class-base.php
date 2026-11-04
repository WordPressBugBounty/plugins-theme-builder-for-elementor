<?php
/**
 * Base of the plugin's widgets.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use TBFE\Elementor\Context;
use TBFE\Elementor\Integration;

defined( 'ABSPATH' ) || exit;

abstract class Base extends Widget_Base {

	/**
	 * Universal element for any page (category "Theme Builder Elements"), not a part of the site or post data
	 * (category "Theme Builder").
	 */
	const UNIVERSAL = false;

	public function get_categories() {
		return array( static::UNIVERSAL ? Integration::CATEGORY_ELEMENTS : Integration::CATEGORY );
	}

	public function get_style_depends(): array {
		return array( 'tbfe-widgets' );
	}

	public function get_keywords() {
		return array( 'theme builder', 'tbfe' );
	}

	/**
	 * The post the widget shows (current post, or the preview post in the editor).
	 *
	 * @return \WP_Post|null
	 */
	protected function post() {
		return Context::post();
	}

	protected function is_editor() {
		return \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode();
	}

	/**
	 * Message in the editor when the widget has nothing to show.
	 */
	protected function editor_notice( $text ) {
		if ( $this->is_editor() ) {
			echo '<div class="tbfe-notice">' . esc_html( $text ) . '</div>';
		}
	}

	/**
	 * Alignment control (left, center, right, justify) on a selector.
	 */
	protected function add_align_control( $id, $selector, $justify = false, $property = 'text-align' ) {
		$options = array(
			'left'   => array(
				'title' => esc_html__( 'Left', 'theme-builder-for-elementor' ),
				'icon'  => 'eicon-text-align-left',
			),
			'center' => array(
				'title' => esc_html__( 'Center', 'theme-builder-for-elementor' ),
				'icon'  => 'eicon-text-align-center',
			),
			'right'  => array(
				'title' => esc_html__( 'Right', 'theme-builder-for-elementor' ),
				'icon'  => 'eicon-text-align-right',
			),
		);
		if ( $justify ) {
			$options['justify'] = array(
				'title' => esc_html__( 'Justified', 'theme-builder-for-elementor' ),
				'icon'  => 'eicon-text-align-justify',
			);
		}
		$this->add_responsive_control(
			$id,
			array(
				'label'     => esc_html__( 'Alignment', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => $options,
				'selectors' => array( $selector => $property . ': {{VALUE}};' ),
			)
		);
	}

	/**
	 * HTML tag control.
	 */
	protected function add_tag_control( $id, $default = 'h2', $label = '' ) {
		$this->add_control(
			$id,
			array(
				'label'   => $label ? $label : esc_html__( 'HTML tag', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'p'    => 'p',
					'span' => 'span',
				),
				'default' => $default,
			)
		);
	}

	/**
	 * A tag from a select control, limited to safe values.
	 */
	protected static function safe_tag( $tag, $default = 'div' ) {
		$allowed = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'span', 'nav', 'section' );
		return in_array( $tag, $allowed, true ) ? $tag : $default;
	}
}
