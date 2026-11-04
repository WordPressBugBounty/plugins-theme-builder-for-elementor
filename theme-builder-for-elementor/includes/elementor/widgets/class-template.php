<?php
/**
 * Inserts a Section template (or another template of the plugin).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use TBFE\Render\Renderer;
use TBFE\Templates\Post_Type;

defined( 'ABSPATH' ) || exit;

class Template extends Base {

	const UNIVERSAL = true;

	/** @var int[] Templates being rendered (no template inside itself). */
	private static $stack = array();

	public function get_name() {
		return 'tbfe-template';
	}

	public function get_title() {
		return esc_html__( 'Section Template', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-document-file';
	}

	protected function register_controls() {
		$options = self::section_options();

		$this->start_controls_section( 'section_template', array( 'label' => esc_html__( 'Template', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'template_id',
			array(
				'label'       => esc_html__( 'Section', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $options,
				'description' => esc_html__( 'Create sections in Theme Builder › Sections.', 'theme-builder-for-elementor' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Published Section templates: ID => title (with an empty choice first).
	 */
	public static function section_options() {
		$options = array( '' => esc_html__( '— Select —', 'theme-builder-for-elementor' ) );
		$posts   = get_posts(
			array(
				'post_type'      => Post_Type::NAME,
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'meta_key'       => '_tbfe_type', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => 'section', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		foreach ( $posts as $post ) {
			$options[ $post->ID ] = get_the_title( $post );
		}
		return $options;
	}

	/**
	 * Prints a published template, never inside itself.
	 *
	 * @return string '' when printed, else the reason for the editor.
	 */
	public static function render_section( $id ) {
		$id = absint( $id );
		if ( ! $id || Post_Type::NAME !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
			return __( 'Choose a section template.', 'theme-builder-for-elementor' );
		}
		if ( in_array( $id, self::$stack, true ) || get_the_ID() === $id ) {
			return __( 'A template cannot contain itself.', 'theme-builder-for-elementor' );
		}
		self::$stack[] = $id;
		Renderer::render_template( $id );
		array_pop( self::$stack );
		return '';
	}

	protected function render() {
		$error = self::render_section( $this->get_settings_for_display( 'template_id' ) );
		if ( '' !== $error ) {
			$this->editor_notice( $error );
		}
	}
}
