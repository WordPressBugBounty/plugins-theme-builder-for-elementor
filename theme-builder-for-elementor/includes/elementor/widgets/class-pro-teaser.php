<?php
/**
 * A locked PRO widget, listed in the Elementor panel while Theme Builder PRO is not installed. One class for all of
 * them: the name, title and icon come in the arguments (Elementor passes them on to every copy of the widget).
 *
 * Dropped on the page it shows what the widget does and "Get Theme Builder PRO" in the editor; on the site it prints
 * nothing.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use TBFE\Elementor\Integration;
use TBFE\Pro;

defined( 'ABSPATH' ) || exit;

class Pro_Teaser extends \Elementor\Widget_Base {

	/**
	 * Name, title, icon or description of this placeholder. The widget type has them in its arguments; a copy on the
	 * page (Elementor gives it only its data) finds them by its widget type.
	 */
	private function arg( $key ) {
		$args = $this->get_default_args();
		if ( empty( $args['name'] ) ) {
			$name = (string) $this->get_data( 'widgetType' );
			foreach ( Pro::teaser_widgets() as $widget ) {
				if ( $widget['name'] === $name ) {
					$args = $widget;
					break;
				}
			}
		}
		return isset( $args[ $key ] ) && is_string( $args[ $key ] ) ? $args[ $key ] : '';
	}

	public function get_name() {
		return $this->arg( 'name' );
	}

	public function get_title() {
		return $this->arg( 'title' );
	}

	public function get_icon() {
		return $this->arg( 'icon' ) . ' tbfe-pro-locked';
	}

	public function get_categories() {
		return array( Integration::CATEGORY_PRO );
	}

	public function get_keywords() {
		return array( 'pro', 'woocommerce', 'shop', 'product', 'tbfe' );
	}

	public function get_style_depends(): array {
		return array( 'tbfe-widgets' );
	}

	public function is_reload_preview_required() {
		return false;
	}

	protected function register_controls() {
		$action = Pro::action();
		$this->start_controls_section( 'section_pro', array( 'label' => esc_html__( 'PRO widget', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'pro_notice',
			array(
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => sprintf(
					'<div class="tbfe-pro-panel"><strong>%1$s</strong><p>%2$s</p><p>%3$s</p>%4$s</div>',
					esc_html( $this->get_title() ),
					esc_html( $this->arg( 'description' ) ),
					esc_html( $action ? $action['note'] : '' ),
					$action ? sprintf( '<a class="elementor-button tbfe-pro-panel__button" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>', esc_url( $action['url'] ), esc_html( $action['label'] ) ) : ''
				),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$editor = \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode();
		if ( ! $editor ) {
			return;
		}
		$action = Pro::action();
		printf(
			'<div class="tbfe-pro-teaser"><span class="tbfe-pro-teaser__badge">PRO</span><strong>%1$s</strong><p>%2$s</p>%3$s<small>%4$s</small></div>',
			esc_html( $this->get_title() ),
			esc_html( $this->arg( 'description' ) ),
			$action ? sprintf( '<a class="tbfe-pro-teaser__button" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>', esc_url( $action['url'] ), esc_html( $action['label'] ) ) : '',
			esc_html__( 'This placeholder is not shown on your site.', 'theme-builder-for-elementor' )
		);
	}
}
