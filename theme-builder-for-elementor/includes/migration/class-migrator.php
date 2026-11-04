<?php
/**
 * Migration of the data of version 1.
 *
 * Version 1 kept the templates in 7 post types (elementor-header, …) and assigned them in 4 options
 * (tbfe_settings_page/post/archive/404) and in post meta (tbfe_elementor_header_page, …).
 *
 * The migration only changes the post type of the templates, so their IDs, Elementor data, CSS, revisions and
 * shortcodes stay. The options become display conditions, the post meta becomes the per-post choice. The data of
 * version 1 is not deleted: revert() brings the post types back, so version 1 works again after a downgrade.
 *
 * @package TBFE
 */

namespace TBFE\Migration;

use TBFE\Conditions\Resolver;
use TBFE\Templates\Post_Type;
use TBFE\Templates\Template;
use TBFE\Templates\Types;

defined( 'ABSPATH' ) || exit;

class Migrator {

	const DB_VERSION = '2.0.0';
	const OPTION     = 'tbfe_db_version';
	const REPORT     = 'tbfe_migration_report';
	const LEGACY     = '_tbfe_legacy_post_type';
	const PAUSED     = 'tbfe_migration_paused';

	/** Post types of version 1 => template types. */
	const POST_TYPES = array(
		'elementor-header'  => 'header',
		'elementor-footer'  => 'footer',
		'elementor-post'    => 'single',
		'elementor-page'    => 'single',
		'elementor-archive' => 'archive',
		'elementor-sidebar' => 'section',
		'elementor-404'     => 'error-404',
	);

	public static function init() {
		// Early on every request, so the site keeps its templates right after the update (also before an admin visit).
		add_action( 'init', array( __CLASS__, 'maybe_migrate' ), 6 );
	}

	public static function maybe_migrate() {
		if ( version_compare( (string) get_option( self::OPTION, '0' ), self::DB_VERSION, '>=' ) ) {
			return;
		}
		// One request at a time; not after the data was returned to version 1 (Tools › Revert).
		if ( get_transient( 'tbfe_migrating' ) || get_option( self::PAUSED ) ) {
			return;
		}
		set_transient( 'tbfe_migrating', 1, 5 * MINUTE_IN_SECONDS );
		self::migrate();
		delete_transient( 'tbfe_migrating' );
	}

	/**
	 * Is there anything of version 1 on this site?
	 */
	public static function has_legacy_data() {
		global $wpdb;
		$types = array_keys( self::POST_TYPES );
		$in    = implode( ', ', array_fill( 0, count( $types ), '%s' ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- $in holds only %s placeholders.
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ($in)", $types ) );
		return $count > 0 || false !== get_option( 'tbfe_settings_page' ) || false !== get_option( 'tbfe_settings_post' ) || false !== get_option( 'tbfe_settings_archive' ) || false !== get_option( 'tbfe_settings_404' );
	}

	public static function migrate() {
		global $wpdb;

		delete_option( self::PAUSED );
		$report = array(
			'date'       => gmdate( 'c' ),
			'from'       => (string) get_option( self::OPTION, '1.x' ),
			'templates'  => 0,
			'conditions' => 0,
			'overrides'  => 0,
		);

		if ( ! self::has_legacy_data() ) {
			update_option( self::OPTION, self::DB_VERSION );
			return $report;
		}

		Post_Type::register();

		// 1. Templates: change the post type, keep everything else.
		foreach ( self::POST_TYPES as $old => $type ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s", $old ) );
			foreach ( $ids as $id ) {
				$id = (int) $id;
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->update( $wpdb->posts, array( 'post_type' => Post_Type::NAME ), array( 'ID' => $id ) );
				update_post_meta( $id, self::LEGACY, $old );
				update_post_meta( $id, Template::META_TYPE, $type );
				update_post_meta( $id, '_elementor_template_type', Types::document_type( $type ) );
				delete_post_meta( $id, '_wp_page_template' );
				if ( 'archive' === $type ) {
					// Version 1 repeated the archive template for each post.
					update_post_meta(
						$id,
						Template::META_SETTINGS,
						array(
							'mode'    => 'loop',
							'width'   => 'boxed',
							'sidebar' => 0,
						)
					);
				}
				clean_post_cache( $id );
				++$report['templates'];
			}
		}

		// 2. Options → display conditions.
		$rules = array();
		$add   = static function ( $id, $name ) use ( &$rules ) {
			$id = absint( $id );
			if ( $id && Template::is_template( $id ) ) {
				$rules[ $id ][ $name ] = array(
					'type' => 'include',
					'name' => $name,
					'ids'  => array(),
				);
			}
		};

		$page = self::option( 'tbfe_settings_page' );
		$add( self::value( $page, 'tbfe_elementor_header_page' ), 'single_page' );
		$add( self::value( $page, 'tbfe_elementor_page' ), 'single_page' );
		$add( self::value( $page, 'tbfe_elementor_footer_page' ), 'single_page' );

		$post = self::option( 'tbfe_settings_post' );
		$add( self::value( $post, 'tbfe_elementor_header_post' ), 'single_post' );
		$add( self::value( $post, 'tbfe_elementor_post' ), 'single_post' );
		$add( self::value( $post, 'tbfe_elementor_footer_post' ), 'single_post' );

		$archive = self::option( 'tbfe_settings_archive' );
		$add( self::value( $archive, 'tbfe_elementor_header_archive' ), 'archive' );
		$add( self::value( $archive, 'tbfe_elementor_archive' ), 'archive' );
		// Version 1 used the footer of the pages on archives.
		$add( self::value( $archive, 'tbfe_elementor_footer_archive' ), 'archive' );
		$archive_id = absint( self::value( $archive, 'tbfe_elementor_archive' ) );
		if ( $archive_id && Template::is_template( $archive_id ) ) {
			Template::set_settings(
				$archive_id,
				array(
					'mode'    => 'loop',
					'width'   => 'fluid' === self::value( $archive, 'tbfe_elementor_layout_archive' ) ? 'full' : 'boxed',
					'sidebar' => absint( self::value( $archive, 'tbfe_elementor_sidebar_archive' ) ),
				)
			);
		}

		$error = self::option( 'tbfe_settings_404' );
		$add( self::value( $error, 'tbfe_elementor_header_404' ), 'not_found' );
		$add( self::value( $error, 'tbfe_elementor_404' ), 'entire_site' );
		$add( self::value( $error, 'tbfe_elementor_footer_404' ), 'not_found' );

		foreach ( $rules as $id => $list ) {
			$merged = array_merge( Template::conditions( $id ), array_values( $list ) );
			$saved  = Template::set_conditions( $id, $merged );
			$report['conditions'] += count( $saved );
		}

		// 3. Post meta → per-post choice.
		$map = array(
			'tbfe_elementor_header_page' => 'header',
			'tbfe_elementor_footer_page' => 'footer',
			'tbfe_elementor_page'        => 'single',
			'tbfe_elementor_header_post' => 'header',
			'tbfe_elementor_footer_post' => 'footer',
			'tbfe_elementor_post'        => 'single',
		);
		$keys = array_keys( $map );
		$in   = implode( ', ', array_fill( 0, count( $keys ), '%s' ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- $in holds only %s placeholders.
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ($in) AND meta_value <> ''", $keys ) );
		$by_post = array();
		foreach ( $rows as $row ) {
			$template = absint( $row->meta_value );
			if ( $template && Template::is_template( $template ) ) {
				$by_post[ (int) $row->post_id ][ $map[ $row->meta_key ] ] = $template;
			}
		}
		foreach ( $by_post as $post_id => $overrides ) {
			$current = get_post_meta( $post_id, Resolver::META_OVERRIDES, true );
			update_post_meta( $post_id, Resolver::META_OVERRIDES, array_merge( is_array( $current ) ? $current : array(), $overrides ) );
			++$report['overrides'];
		}

		// 4. Elementor may edit the new post type; version 1 widget areas stay registered.
		update_option( 'tbfe_legacy_site', 1 );
		Post_Type::flush_cache();

		update_option( self::REPORT, $report, false );
		update_option( self::OPTION, self::DB_VERSION );
		\TBFE\Conditions\Conditions::reset();
		return $report;
	}

	/**
	 * Brings back the post types of version 1 (before a downgrade to 1.x).
	 */
	public static function revert() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s", self::LEGACY ) );
		$count = 0;
		foreach ( $rows as $row ) {
			if ( ! isset( self::POST_TYPES[ $row->meta_value ] ) ) {
				continue;
			}
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update(
				$wpdb->posts,
				array( 'post_type' => $row->meta_value ),
				array(
					'ID'        => (int) $row->post_id,
					'post_type' => Post_Type::NAME,
				)
			);
			clean_post_cache( (int) $row->post_id );
			++$count;
		}
		delete_option( self::OPTION );
		update_option( self::PAUSED, 1 );
		Post_Type::flush_cache();
		return $count;
	}

	private static function option( $name ) {
		$value = get_option( $name );
		return is_array( $value ) ? $value : array();
	}

	private static function value( array $option, $key ) {
		return isset( $option[ $key ] ) ? $option[ $key ] : '';
	}
}
