<?php
/**
 * The post the widgets show.
 *
 * On the site it is the current post of the loop. In the Elementor editor of a template (and its preview) it is the
 * post chosen in the template's "Preview" settings, or the newest post, so the widgets show real content.
 *
 * @package TBFE
 */

namespace TBFE\Elementor;

use TBFE\Templates\Post_Type;
use TBFE\Templates\Template;

defined( 'ABSPATH' ) || exit;

class Context {

	/**
	 * Is a template rendered alone (editor, preview)?
	 */
	public static function is_template_preview() {
		$id = get_the_ID();
		return $id && Post_Type::NAME === get_post_type( $id );
	}

	/**
	 * @return \WP_Post|null
	 */
	public static function post() {
		$post = get_post();
		if ( $post && Post_Type::NAME !== $post->post_type ) {
			return $post;
		}
		return self::preview_post( $post ? $post->ID : 0 );
	}

	/**
	 * The preview post of a template.
	 *
	 * @param int $template_id Template ID.
	 * @return \WP_Post|null
	 */
	public static function preview_post( $template_id ) {
		static $cache = array();
		if ( isset( $cache[ $template_id ] ) ) {
			return $cache[ $template_id ];
		}

		$preview = 0;
		if ( $template_id && class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::$instance->documents->get( $template_id );
			if ( $document ) {
				$preview = absint( $document->get_settings( 'tbfe_preview_id' ) );
			}
		}
		$post = $preview ? get_post( $preview ) : null;
		if ( ! $post || 'publish' !== $post->post_status ) {
			$type   = $template_id ? Template::type( $template_id ) : '';
			$recent = get_posts(
				array(
					'post_type'      => 'single' === $type ? array( 'post', 'page' ) : 'post',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'no_found_rows'  => true,
				)
			);
			$post   = $recent ? $recent[0] : null;
		}
		$cache[ $template_id ] = $post;
		return $post;
	}

	/**
	 * Minutes to read the post (at least 1).
	 *
	 * @param \WP_Post $post Post.
	 * @param int      $wpm  Words per minute.
	 */
	public static function reading_time( \WP_Post $post, $wpm = 200 ) {
		$text  = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
		$words = preg_match_all( '/[\p{L}\p{N}]+/u', $text );
		return max( 1, (int) ceil( (int) $words / max( 50, (int) $wpm ) ) );
	}

	/**
	 * Title and description of the current archive (category, tag, author, date, search, blog).
	 *
	 * @param bool $prefix With the prefix of WordPress ("Category:").
	 * @return string[] array( title, description HTML )
	 */
	public static function archive_texts( $prefix = false ) {
		if ( self::is_template_preview() ) {
			return array( __( 'Archive title', 'theme-builder-for-elementor' ), __( 'The description of the category, tag or author is shown here.', 'theme-builder-for-elementor' ) );
		}
		if ( is_search() ) {
			/* translators: %s: search query */
			return array( sprintf( __( 'Search results for “%s”', 'theme-builder-for-elementor' ), get_search_query() ), '' );
		}
		if ( is_home() ) {
			$page = (int) get_option( 'page_for_posts' );
			return array( $page ? get_the_title( $page ) : __( 'Blog', 'theme-builder-for-elementor' ), '' );
		}
		if ( $prefix ) {
			$title = get_the_archive_title();
		} else {
			add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
			$title = get_the_archive_title();
			remove_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
		}
		return array( wp_strip_all_tags( $title ), get_the_archive_description() );
	}

	/**
	 * URL of the current archive (the blog page outside archives).
	 */
	public static function archive_url() {
		if ( is_category() || is_tag() || is_tax() ) {
			$url = get_term_link( get_queried_object() );
			return is_wp_error( $url ) ? '' : $url;
		}
		if ( is_author() ) {
			return get_author_posts_url( get_queried_object_id() );
		}
		if ( is_post_type_archive() ) {
			return (string) get_post_type_archive_link( get_query_var( 'post_type' ) );
		}
		if ( is_search() ) {
			return get_search_link();
		}
		$blog = (int) get_option( 'page_for_posts' );
		return $blog ? get_permalink( $blog ) : home_url( '/' );
	}

	/**
	 * Author shown by author widgets: the author of an author archive, else the author of the post.
	 */
	public static function author_id() {
		if ( is_author() && ! self::is_template_preview() ) {
			return (int) get_queried_object_id();
		}
		$post = self::post();
		return $post ? (int) $post->post_author : 0;
	}

	/**
	 * Placeholder text in the editor when there is no post at all.
	 */
	public static function placeholder( $text ) {
		return '<span class="tbfe-placeholder">' . esc_html( $text ) . '</span>';
	}
}
