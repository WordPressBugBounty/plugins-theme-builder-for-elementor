<?php
/**
 * Dynamic tags for the free Elementor: post, author, archive and site data in any widget (Heading, Button, Image…).
 *
 * The free Elementor has the dynamic tags system but no tags; Elementor Pro adds its own next to these.
 *
 * @package TBFE
 */

namespace TBFE\Elementor;

defined( 'ABSPATH' ) || exit;

class Dynamic_Tags {

	const GROUP = 'tbfe';

	public static function init() {
		add_action( 'elementor/dynamic_tags/register', array( __CLASS__, 'register' ) );
	}

	/**
	 * Tag classes.
	 *
	 * @return string[]
	 */
	public static function tags() {
		$tags = array(
			// Text.
			Tags\Post_Title::class,
			Tags\Post_Excerpt::class,
			Tags\Post_Date::class,
			Tags\Post_Terms::class,
			Tags\Post_Comments::class,
			Tags\Reading_Time::class,
			Tags\Author_Name::class,
			Tags\Author_Bio::class,
			Tags\Archive_Title::class,
			Tags\Archive_Description::class,
			Tags\Site_Title::class,
			Tags\Site_Tagline::class,
			Tags\Search_Query::class,
			Tags\Custom_Field::class,
			// Links.
			Tags\Post_URL::class,
			Tags\Comments_URL::class,
			Tags\Author_URL::class,
			Tags\Archive_URL::class,
			Tags\Site_URL::class,
			// Images.
			Tags\Featured_Image::class,
			Tags\Author_Avatar::class,
			Tags\Site_Logo::class,
		);
		/**
		 * Filters the dynamic tag classes of the plugin.
		 *
		 * @param string[] $tags Class names.
		 */
		return (array) apply_filters( 'tbfe/dynamic_tags', $tags );
	}

	/**
	 * @param \Elementor\Core\DynamicTags\Manager $manager Dynamic tags manager.
	 */
	public static function register( $manager ) {
		$manager->register_group( self::GROUP, array( 'title' => esc_html__( 'Theme Builder', 'theme-builder-for-elementor' ) ) );
		foreach ( self::tags() as $class ) {
			if ( class_exists( $class ) ) {
				$manager->register( new $class() );
			}
		}
	}
}
