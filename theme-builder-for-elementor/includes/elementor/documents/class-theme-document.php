<?php
/**
 * Base Elementor document of the templates.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Documents;

use Elementor\Controls_Manager;
use Elementor\Core\Base\Document;
use TBFE\Elementor\Integration;
use TBFE\Templates\Post_Type;
use TBFE\Templates\Types;

defined( 'ABSPATH' ) || exit;

abstract class Theme_Document extends Document {

	/** Template type of the plugin (header, footer, ...). */
	const TBFE_TYPE = '';

	/** The document can preview a chosen post (widgets show its data). */
	const HAS_PREVIEW_POST = false;

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['support_kit']         = true;
		$properties['support_page_layout'] = false;
		$properties['show_in_finder']      = true;
		$properties['show_on_admin_bar']   = true;
		$properties['cpt']                 = array( Post_Type::NAME );
		$properties['tbfe_type']           = static::TBFE_TYPE;

		return $properties;
	}

	public static function get_type() {
		return Types::document_type( static::TBFE_TYPE );
	}

	public static function get_title() {
		$type = Types::get( static::TBFE_TYPE );
		return $type ? $type['label'] : esc_html__( 'Template', 'theme-builder-for-elementor' );
	}

	public static function get_plural_title() {
		$type = Types::get( static::TBFE_TYPE );
		return $type ? $type['plural'] : static::get_title();
	}

	/**
	 * Back to the plugin's administration instead of the post list.
	 */
	public function get_exit_to_dashboard_url() {
		return admin_url( 'admin.php?page=tbfe#/templates/' . static::TBFE_TYPE );
	}

	/**
	 * In a template the widgets of the plugin are on top of the panel: first the parts of the site and the post data,
	 * then the universal elements.
	 */
	protected static function get_editor_panel_categories() {
		$categories = parent::get_editor_panel_categories();
		$first      = array();
		foreach ( array( Integration::CATEGORY, Integration::CATEGORY_ELEMENTS ) as $name ) {
			if ( isset( $categories[ $name ] ) ) {
				$first[ $name ] = $categories[ $name ];
				unset( $categories[ $name ] );
			}
		}
		return $first + $categories;
	}

	public function get_css_wrapper_selector() {
		return '.elementor-' . $this->get_main_id();
	}

	protected function register_controls() {
		parent::register_controls();

		if ( ! static::HAS_PREVIEW_POST ) {
			return;
		}

		$this->start_controls_section(
			'tbfe_preview_section',
			array(
				'label' => esc_html__( 'Preview', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_SETTINGS,
			)
		);
		$this->add_control(
			'tbfe_preview_id',
			array(
				'label'       => esc_html__( 'Preview with', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'The widgets show the data of this post. Save and reload the editor to apply.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => self::preview_options(),
				'default'     => '',
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Latest posts and pages for the preview choice.
	 */
	private static function preview_options() {
		$options = array( '' => esc_html__( 'Newest post', 'theme-builder-for-elementor' ) );
		$types   = array_keys( \TBFE\Conditions\Conditions::post_types() );
		$posts   = get_posts(
			array(
				'post_type'      => $types,
				'post_status'    => 'publish',
				'posts_per_page' => 40,
				'no_found_rows'  => true,
			)
		);
		foreach ( $posts as $post ) {
			$pt                    = get_post_type_object( $post->post_type );
			$options[ $post->ID ] = sprintf( '%s — %s', $pt ? $pt->labels->singular_name : $post->post_type, wp_html_excerpt( get_the_title( $post ), 60, '…' ) );
		}
		return $options;
	}
}
