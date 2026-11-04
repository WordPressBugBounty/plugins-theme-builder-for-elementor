<?php
/**
 * Elementor integration: document types of the templates, widgets, widget category, editor assets.
 *
 * @package TBFE
 */

namespace TBFE\Elementor;

use TBFE\Templates\Types;

defined( 'ABSPATH' ) || exit;

class Integration {

	const CATEGORY = 'tbfe';

	/** Category of the universal widgets (headings, share buttons, table of contents…). */
	const CATEGORY_ELEMENTS = 'tbfe-elements';

	/** Category of the WooCommerce widgets of PRO (locked placeholders without PRO). */
	const CATEGORY_PRO = 'tbfe-pro';

	public static function init() {
		add_action( 'elementor/documents/register', array( __CLASS__, 'register_documents' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
		add_action( 'elementor/frontend/after_register_styles', array( __CLASS__, 'register_styles' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( __CLASS__, 'register_scripts' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'editor_styles' ) );
		Dynamic_Tags::init();
		Editor_Library::init();
	}

	/**
	 * One Elementor document type per template type: tbfe-header, tbfe-footer, ...
	 */
	public static function register_documents( $manager ) {
		$classes = array(
			'header'    => Documents\Header::class,
			'footer'    => Documents\Footer::class,
			'single'    => Documents\Single::class,
			'archive'   => Documents\Archive::class,
			'search'    => Documents\Search::class,
			'error-404' => Documents\Error_404::class,
			'loop-item' => Documents\Loop_Item::class,
			'section'   => Documents\Section::class,
		);
		/**
		 * Filters the Elementor document classes of the template types.
		 *
		 * @param array $classes Template type => class name (extends TBFE\Elementor\Documents\Theme_Document).
		 */
		$classes = (array) apply_filters( 'tbfe/document_classes', $classes );

		foreach ( $classes as $type => $class ) {
			if ( Types::exists( $type ) && class_exists( $class ) ) {
				$manager->register_document_type( Types::document_type( $type ), $class );
			}
		}
	}

	/**
	 * Widget classes: name => class. The names of version 1 (blog-*) stay, so the saved layouts keep working.
	 */
	public static function widgets() {
		$widgets = array(
			// Category "Theme Builder": the post (version 1 kept the names blog-*, so saved layouts keep working).
			Widgets\Post_Title::class,
			Widgets\Post_Info::class,
			Widgets\Post_Content::class,
			Widgets\Post_Image::class,
			Widgets\Post_Date::class,
			Widgets\Post_Author::class,
			Widgets\Post_Categories::class,
			Widgets\Post_Tags::class,
			Widgets\Post_Comments_Count::class,
			Widgets\Read_More::class,
			Widgets\Post_Navigation::class,
			Widgets\Author_Box::class,
			Widgets\Post_Comments::class,
			// Archives.
			Widgets\Archive_Title::class,
			Widgets\Posts::class,
			// Parts of the site.
			Widgets\Breadcrumbs::class,
			Widgets\Site_Logo::class,
			Widgets\Site_Title::class,
			Widgets\Nav_Menu::class,
			Widgets\Search_Form::class,
			Widgets\Copyright::class,
			// Category "Theme Builder Elements": universal elements for any page.
			Widgets\Advanced_Heading::class,
			Widgets\Share_Buttons::class,
			Widgets\Table_Of_Contents::class,
			Widgets\Reading_Progress::class,
			Widgets\Back_To_Top::class,
			Widgets\Slider::class,
			Widgets\Template::class,
			Widgets\Sidebar::class,
		);
		/**
		 * Filters the widget classes of the plugin.
		 *
		 * @param string[] $widgets Class names.
		 */
		return (array) apply_filters( 'tbfe/widgets', $widgets );
	}

	/** Option with the names of the widgets switched off in Settings › Widgets. */
	const DISABLED = 'tbfe_disabled_widgets';

	public static function disabled() {
		$disabled = get_option( self::DISABLED, array() );
		return is_array( $disabled ) ? array_values( array_filter( array_map( 'sanitize_key', $disabled ) ) ) : array();
	}

	public static function register_widgets( $manager ) {
		$disabled = self::disabled();
		foreach ( self::widgets() as $class ) {
			if ( class_exists( $class ) ) {
				$widget = new $class();
				if ( ! in_array( $widget->get_name(), $disabled, true ) ) {
					$manager->register( $widget );
				}
			}
		}
		// Without PRO its widgets are listed as locked placeholders.
		if ( ! \TBFE\Pro::installed() ) {
			foreach ( \TBFE\Pro::teaser_widgets() as $args ) {
				if ( ! in_array( $args['name'], $disabled, true ) ) {
					$manager->register( new Widgets\Pro_Teaser( array(), $args ) );
				}
			}
		}
	}

	/**
	 * All widgets for Settings › Widgets: name, title, icon, group (theme, pro), pro, locked, enabled.
	 */
	public static function widget_list() {
		$disabled = self::disabled();
		$list     = array();
		foreach ( self::widgets() as $class ) {
			if ( ! class_exists( $class ) ) {
				continue;
			}
			$widget = new $class();
			$pro    = in_array( self::CATEGORY_PRO, (array) $widget->get_categories(), true );
			$list[] = array(
				'name'    => $widget->get_name(),
				'title'   => $widget->get_title(),
				'icon'    => trim( str_replace( 'tbfe-pro-locked', '', $widget->get_icon() ) ),
				'group'   => $pro ? 'pro' : 'theme',
				'pro'     => $pro,
				'locked'  => $pro && ! \TBFE\Pro::is_active(),
				'enabled' => ! in_array( $widget->get_name(), $disabled, true ),
			);
		}
		if ( ! \TBFE\Pro::installed() ) {
			foreach ( \TBFE\Pro::teaser_widgets() as $teaser ) {
				$list[] = array(
					'name'    => $teaser['name'],
					'title'   => $teaser['title'],
					'icon'    => $teaser['icon'],
					'group'   => 'pro',
					'pro'     => true,
					'locked'  => true,
					'enabled' => ! in_array( $teaser['name'], $disabled, true ),
				);
			}
		}
		return $list;
	}

	public static function register_category( $manager ) {
		$manager->add_category(
			self::CATEGORY,
			array(
				'title' => esc_html__( 'Theme Builder', 'theme-builder-for-elementor' ),
				'icon'  => 'eicon-theme-builder',
			)
		);
		$manager->add_category(
			self::CATEGORY_ELEMENTS,
			array(
				'title' => esc_html__( 'Theme Builder Elements', 'theme-builder-for-elementor' ),
				'icon'  => 'eicon-apps',
			)
		);
		$manager->add_category(
			self::CATEGORY_PRO,
			array(
				'title' => esc_html__( 'Theme Builder PRO – WooCommerce', 'theme-builder-for-elementor' ),
				'icon'  => 'eicon-woocommerce',
			)
		);
	}

	public static function register_styles() {
		wp_register_style( 'tbfe-widgets', \TBFE\Assets::url( 'assets/css/widgets.css' ), array(), \TBFE\Assets::version( 'assets/css/widgets.css' ) );
		// The bigger widgets have their own files, loaded only with them.
		\TBFE\Assets::register_widget_styles();
	}

	public static function register_scripts() {
		wp_register_script( 'tbfe-nav-menu', \TBFE\Assets::url( 'assets/js/nav-menu.js' ), array(), \TBFE\Assets::version( 'assets/js/nav-menu.js' ), true );
		wp_register_script( 'tbfe-widgets', \TBFE\Assets::url( 'assets/js/widgets.js' ), array(), \TBFE\Assets::version( 'assets/js/widgets.js' ), true );
		\TBFE\Assets::register_widget_scripts();
	}

	public static function editor_styles() {
		wp_enqueue_style( 'tbfe-editor', \TBFE\Assets::url( 'assets/css/editor.css' ), array(), \TBFE\Assets::version( 'assets/css/editor.css' ) );
	}
}
