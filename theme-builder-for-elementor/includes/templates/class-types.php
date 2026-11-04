<?php
/**
 * Registry of the template types. The PRO add-on adds its own types with the filter tbfe/template_types.
 *
 * @package TBFE
 */

namespace TBFE\Templates;

defined( 'ABSPATH' ) || exit;

class Types {

	/** @var array|null */
	private static $types = null;

	/**
	 * All template types.
	 *
	 * location:   where the template is placed (header, footer, single, archive, 404) or '' for templates used by widgets.
	 * conditions: which condition groups the template can use ('' = none, the template is used where it is placed).
	 *
	 * @return array
	 */
	public static function all() {
		if ( null !== self::$types ) {
			return self::$types;
		}

		$types = array(
			'header'    => array(
				'label'       => __( 'Header', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Headers', 'theme-builder-for-elementor' ),
				'description' => __( 'The top of the site: logo, menu, search.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The top of every page: logo, menu, search or a button. After the import choose where it is shown – usually the entire site. A page can pick another header in its Theme Builder box.', 'theme-builder-for-elementor' ),
				'location'    => 'header',
				'conditions'  => array( 'general', 'singular', 'archive' ),
			),
			'footer'    => array(
				'label'       => __( 'Footer', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Footers', 'theme-builder-for-elementor' ),
				'description' => __( 'The bottom of the site: links, contacts, copyright.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The bottom of every page: contacts, links, social icons and the copyright. After the import choose where it is shown – usually the entire site.', 'theme-builder-for-elementor' ),
				'location'    => 'footer',
				'conditions'  => array( 'general', 'singular', 'archive' ),
			),
			'single'    => array(
				'label'       => __( 'Single', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Single posts & pages', 'theme-builder-for-elementor' ),
				'description' => __( 'The layout of a post, page or custom post type.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The layout of one post or page: title, featured image, content, author, comments… The widgets fill in the data of the post that is being viewed. Use it for all posts, the posts of a category or chosen pages.', 'theme-builder-for-elementor' ),
				'location'    => 'single',
				'conditions'  => array( 'singular' ),
			),
			'archive'   => array(
				'label'       => __( 'Archive', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Archives', 'theme-builder-for-elementor' ),
				'description' => __( 'Blog, categories, tags, authors and search results.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The page that lists posts: the blog, categories, tags, author pages and search results. Its Posts widget shows the posts of the archive that is being viewed, with pagination.', 'theme-builder-for-elementor' ),
				'location'    => 'archive',
				'conditions'  => array( 'archive' ),
			),
			'search'    => array(
				'label'       => __( 'Search Results', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Search Results', 'theme-builder-for-elementor' ),
				'description' => __( 'The page with the results of a search.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The page with the results of a search on your site. Its Posts widget lists what was found, the Archive Title widget shows the searched words, and a search form lets the visitor try again. Without it, searches use your Archive template.', 'theme-builder-for-elementor' ),
				'location'    => 'search',
				'conditions'  => array( 'general' ), // On or off: entire site = used for every search.
			),
			'error-404' => array(
				'label'       => __( '404 Page', 'theme-builder-for-elementor' ),
				'plural'      => __( '404 Pages', 'theme-builder-for-elementor' ),
				'description' => __( 'The page shown when nothing was found.', 'theme-builder-for-elementor' ),
				'help'        => __( 'Shown when a page does not exist. Help the visitor on: a search form, a link to the home page or popular posts. Switch it on with “Use as the 404 page”.', 'theme-builder-for-elementor' ),
				'location'    => '404',
				'conditions'  => array( 'general' ), // On or off: entire site = used for every 404 page.
			),
			'loop-item' => array(
				'label'       => __( 'Post Card', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Post Cards', 'theme-builder-for-elementor' ),
				'description' => __( 'One item of a post list, used by the Posts widget.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The design of one post in a list of posts. It is not shown on its own: choose it as the Card in the Posts widget – in an archive, on the home page or in a sidebar – and every post of the list looks like this.', 'theme-builder-for-elementor' ),
				'location'    => '',
				'conditions'  => array(),
			),
			'section'   => array(
				'label'       => __( 'Section', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Sections', 'theme-builder-for-elementor' ),
				'description' => __( 'A reusable part: sidebar, banner, call to action.', 'theme-builder-for-elementor' ),
				'help'        => __( 'A reusable block: a call to action, an about box, a newsletter, a banner. It is not shown on its own: insert it with the Section Template widget or the shortcode [tbfe_template id="…"] into pages, templates or sidebars. Change it once and it changes everywhere.', 'theme-builder-for-elementor' ),
				'location'    => '',
				'conditions'  => array(),
			),
		);

		/**
		 * Filters the template types.
		 *
		 * @param array $types Types keyed by slug.
		 */
		self::$types = (array) apply_filters( 'tbfe/template_types', $types );
		return self::$types;
	}

	public static function exists( $type ) {
		return isset( self::all()[ $type ] );
	}

	public static function get( $type ) {
		$all = self::all();
		return isset( $all[ $type ] ) ? $all[ $type ] : null;
	}

	/**
	 * Types placed by conditions at one location.
	 */
	public static function for_location( $location ) {
		return array_keys( wp_list_filter( self::all(), array( 'location' => $location ) ) );
	}

	/**
	 * Elementor document type of a template type.
	 */
	public static function document_type( $type ) {
		return 'tbfe-' . $type;
	}
}
