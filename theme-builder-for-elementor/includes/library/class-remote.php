<?php
/**
 * The demo library on demos.blocks-wp.com (TBFE Demo Library plugin there): the list and the files.
 *
 * - The list is loaded once a day. The user may refresh it by hand, at most every 6 hours. A failed attempt is not
 *   repeated for an hour; the last good copy stays in use, so the library keeps working without a connection.
 * - A file (template or page) is downloaded when it is imported and compared with the sha256 of the list; a damaged
 *   or changed file is refused. Files are kept for an hour (the pages of one website are often imported together).
 *
 * External service, see "External services" in readme.txt. The requests carry the plugin version and the site
 * address in the user agent, as WordPress does for updates.
 *
 * @package TBFE
 */

namespace TBFE\Library;

use TBFE\Templates\Types;

defined( 'ABSPATH' ) || exit;

class Remote {

	const OPTION  = 'tbfe_library_remote';
	const SCHEMA  = 'tbfe-library/v1';
	const AUTO    = DAY_IN_SECONDS;
	const MANUAL  = 6 * HOUR_IN_SECONDS;
	const RETRY   = HOUR_IN_SECONDS;
	const MAX_FILE = 4194304; // 4 MB.

	/**
	 * Base URL of the library (constant TBFE_LIBRARY_URL or filter tbfe/library_url, e.g. for a local copy).
	 */
	public static function base() {
		return untrailingslashit( (string) apply_filters( 'tbfe/library_url', TBFE_LIBRARY_URL ) );
	}

	private static function get( $url, $timeout, array $headers = array() ) {
		return wp_safe_remote_get(
			$url,
			array(
				'timeout'    => $timeout,
				'user-agent' => 'ThemeBuilderForElementor/' . TBFE_VERSION, // No address of the site.
				'headers'    => array_merge( array( 'Accept' => 'application/json' ), $headers ),
			)
		);
	}

	/**
	 * A template type the library may list: a type of this site, or a PRO type (listed locked without PRO).
	 */
	private static function known_type( $type ) {
		return Types::exists( $type ) || isset( \TBFE\Pro::teaser_types()[ $type ] );
	}

	/**
	 * Stored state: index (last good list), loaded (time of the last good list), tried (last attempt).
	 */
	private static function state() {
		$state = get_site_option( self::OPTION );
		return array_merge(
			array(
				'index'  => null,
				'loaded' => 0,
				'tried'  => 0,
			),
			is_array( $state ) ? $state : array()
		);
	}

	/**
	 * The stored list without loading it (null before the first load).
	 *
	 * @return array|null
	 */
	public static function cached() {
		return self::state()['index'];
	}

	/**
	 * When the user may refresh the list again (0 = now).
	 */
	public static function next_manual() {
		$state = self::state();
		// Only a successful load counts: after a failure the user may try again at once.
		$next = (int) $state['loaded'] + self::MANUAL;
		return $next > time() ? $next : 0;
	}

	/**
	 * The list: the stored copy, loaded again when it is older than a day.
	 *
	 * @param bool $manual The user asked to refresh (allowed every 6 hours).
	 * @return array|\WP_Error
	 */
	public static function index( $manual = false ) {
		$state = self::state();
		$stale = time() - (int) $state['loaded'] > self::AUTO && time() - (int) $state['tried'] > self::RETRY;

		if ( $manual && self::next_manual() ) {
			return new \WP_Error(
				'tbfe_library_wait',
				/* translators: %s: time */
				sprintf( __( 'The library was refreshed recently. You can refresh it again at %s.', 'theme-builder-for-elementor' ), wp_date( get_option( 'time_format' ), self::next_manual() ) ),
				array( 'status' => 429 )
			);
		}
		if ( $manual || $stale || ! $state['index'] ) {
			if ( ! $manual && ! $stale && $state['tried'] && time() - (int) $state['tried'] < self::RETRY ) {
				// No copy yet and the last attempt failed a moment ago.
				return new \WP_Error( 'tbfe_library', __( 'The library is not available right now. Please try again later.', 'theme-builder-for-elementor' ) );
			}
			$loaded = self::load();
			if ( ! is_wp_error( $loaded ) ) {
				return $loaded;
			}
			if ( ! $state['index'] ) {
				return $loaded;
			}
		}
		return $state['index'];
	}

	/**
	 * Downloads the list and stores it.
	 *
	 * @return array|\WP_Error
	 */
	private static function load() {
		$state          = self::state();
		$state['tried'] = time();
		update_site_option( self::OPTION, $state );

		$response = self::get( self::base() . '/list', 10 );
		$data     = ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) ? json_decode( (string) wp_remote_retrieve_body( $response ), true ) : null;
		if ( ! is_array( $data ) || self::SCHEMA !== ( isset( $data['schema'] ) ? $data['schema'] : '' ) || empty( $data['hashes'] ) ) {
			return new \WP_Error( 'tbfe_library', __( 'The library is not available right now. Please try again later.', 'theme-builder-for-elementor' ) );
		}

		$state['index']  = self::sanitize( $data );
		$state['loaded'] = time();
		update_site_option( self::OPTION, $state );
		Previews::schedule_prune();
		return $state['index'];
	}

	private static function text( $item, $key ) {
		return sanitize_text_field( isset( $item[ $key ] ) ? (string) $item[ $key ] : '' );
	}

	private static function url( $item, $key ) {
		return esc_url_raw( isset( $item[ $key ] ) ? (string) $item[ $key ] : '' );
	}

	private static function file_path( $value ) {
		$value = (string) $value;
		return 1 === preg_match( '#^(templates|pages)/[0-9]{1,10}\.json$#', $value ) ? $value : '';
	}

	/**
	 * Preview data: version, size, placeholder and the file names of the widths.
	 */
	private static function image( $image ) {
		if ( ! is_array( $image ) || empty( $image['key'] ) || empty( $image['files'] ) ) {
			return null;
		}
		$files = array();
		foreach ( (array) $image['files'] as $width => $name ) {
			if ( 1 === preg_match( '/^[a-z0-9-]{1,40}\.[a-f0-9]{8}-[0-9]{2,4}\.webp$/', (string) $name ) ) {
				$files[ (int) $width ] = (string) $name;
			}
		}
		if ( ! $files ) {
			return null;
		}
		$lqip = isset( $image['lqip'] ) ? (string) $image['lqip'] : '';
		return array(
			'key'   => sanitize_key( $image['key'] ),
			'w'     => absint( isset( $image['w'] ) ? $image['w'] : 0 ),
			'h'     => absint( isset( $image['h'] ) ? $image['h'] : 0 ),
			'lqip'  => 0 === strpos( $lqip, 'data:image/webp;base64,' ) && strlen( $lqip ) < 4000 ? $lqip : '',
			'files' => $files,
		);
	}

	/**
	 * Mega menu (Theme Builder PRO) that the menu item of a website page opens: demo IDs of the templates and the width.
	 *
	 * @return array{template:int,width:string,custom:int,mobile:int}|null
	 */
	private static function mega( $mega ) {
		if ( ! is_array( $mega ) || empty( $mega['template'] ) ) {
			return null;
		}
		$width = isset( $mega['width'] ) ? (string) $mega['width'] : 'container';
		return array(
			'template' => absint( $mega['template'] ),
			'width'    => in_array( $width, array( 'container', 'full', 'content', 'custom' ), true ) ? $width : 'container',
			'custom'   => max( 200, min( 2400, absint( isset( $mega['custom'] ) ? $mega['custom'] : 800 ) ) ),
			'mobile'   => absint( isset( $mega['mobile'] ) ? $mega['mobile'] : 0 ),
		);
	}

	/**
	 * Only known fields with clean values are kept.
	 */
	private static function sanitize( array $data ) {
		$items = array();
		foreach ( isset( $data['items'] ) ? (array) $data['items'] : array() as $item ) {
			$file = self::file_path( isset( $item['file'] ) ? $item['file'] : '' );
			if ( empty( $item['id'] ) || ! $file || empty( $item['type'] ) || ! self::known_type( $item['type'] ) ) {
				continue;
			}
			$items[] = array(
				'id'          => absint( $item['id'] ),
				'type'        => sanitize_key( $item['type'] ),
				'title'       => self::text( $item, 'title' ),
				'description' => self::text( $item, 'description' ),
				'added'       => self::text( $item, 'added' ),
				'category'    => self::text( $item, 'category' ),
				'tags'        => array_map( 'sanitize_text_field', isset( $item['tags'] ) ? (array) $item['tags'] : array() ),
				'preview'     => self::url( $item, 'preview' ),
				'file'        => $file,
				'image'       => self::image( isset( $item['image'] ) ? $item['image'] : null ),
				// PRO demo: imported only with an active PRO license (a PRO type is always PRO).
				'pro'         => ! empty( $item['pro'] ) || isset( \TBFE\Pro::teaser_types()[ $item['type'] ] ),
				// Needs WooCommerce: a shop template, or WooCommerce widgets.
				'woocommerce' => ! empty( $item['woocommerce'] ) || isset( \TBFE\Pro::woo_types()[ $item['type'] ] ),
				'widgets'     => array_values( array_filter( array_map( 'sanitize_key', isset( $item['widgets'] ) ? (array) $item['widgets'] : array() ) ) ),
			);
		}

		$sites = array();
		foreach ( isset( $data['sites'] ) ? (array) $data['sites'] : array() as $site ) {
			$pro   = ! empty( $site['pro'] );
			$pages = array();
			foreach ( isset( $site['pages'] ) ? (array) $site['pages'] : array() as $page ) {
				$file = self::file_path( isset( $page['file'] ) ? $page['file'] : '' );
				if ( empty( $page['id'] ) || ! $file ) {
					continue;
				}
				$pages[] = array(
					'id'      => absint( $page['id'] ),
					'title'   => self::text( $page, 'title' ),
					'slug'    => sanitize_title( isset( $page['slug'] ) ? $page['slug'] : '' ),
					'home'    => ! empty( $page['home'] ),
					'preview' => self::url( $page, 'preview' ),
					'file'    => $file,
					'image'   => self::image( isset( $page['image'] ) ? $page['image'] : null ),
					'pro'         => $pro,
					'woocommerce' => ! empty( $page['woocommerce'] ),
					'widgets'     => array_values( array_filter( array_map( 'sanitize_key', isset( $page['widgets'] ) ? (array) $page['widgets'] : array() ) ) ),
					'mega'        => self::mega( isset( $page['mega'] ) ? $page['mega'] : null ),
				);
			}
			$templates = array();
			foreach ( isset( $site['templates'] ) ? (array) $site['templates'] : array() as $template ) {
				if ( ! empty( $template['id'] ) && ! empty( $template['type'] ) && self::known_type( $template['type'] ) ) {
					$templates[] = array(
						'id'    => absint( $template['id'] ),
						'type'  => sanitize_key( $template['type'] ),
						'title' => self::text( $template, 'title' ),
					);
				}
			}
			if ( empty( $site['id'] ) || ! $pages ) {
				continue;
			}
			$sites[] = array(
				'id'          => absint( $site['id'] ),
				'title'       => self::text( $site, 'title' ),
				'description' => self::text( $site, 'description' ),
				'added'       => self::text( $site, 'added' ),
				'category'    => self::text( $site, 'category' ),
				'tags'        => array_map( 'sanitize_text_field', isset( $site['tags'] ) ? (array) $site['tags'] : array() ),
				'preview'     => self::url( $site, 'preview' ),
				'pages'       => $pages,
				'templates'   => $templates,
				'image'       => self::image( isset( $site['image'] ) ? $site['image'] : null ),
				'pro'         => $pro,
				'woocommerce' => ! empty( $site['woocommerce'] ),
			);
		}

		$hashes = array();
		foreach ( (array) $data['hashes'] as $rel => $hash ) {
			if ( self::file_path( $rel ) && 1 === preg_match( '/^[a-f0-9]{64}$/', (string) $hash ) ) {
				$hashes[ $rel ] = (string) $hash;
			}
		}

		return array(
			'version'      => self::text( $data, 'version' ),
			'published'    => self::text( $data, 'published' ),
			'home'         => self::url( $data, 'home' ),
			'previews_url' => trailingslashit( self::url( $data, 'previews_url' ) ),
			'items'        => $items,
			'sites'        => $sites,
			'hashes'       => $hashes,
		);
	}

	/**
	 * One file of the library, checked against the list.
	 *
	 * @param string $rel templates/<id>.json or pages/<id>.json.
	 * @return array|\WP_Error Decoded JSON.
	 */
	public static function file( $rel ) {
		$rel = self::file_path( $rel );
		if ( ! $rel ) {
			return new \WP_Error( 'tbfe_library', __( 'Unknown demo file.', 'theme-builder-for-elementor' ) );
		}
		$cache = 'tbfe_library_' . md5( $rel );
		$body  = get_site_transient( $cache );

		if ( ! is_string( $body ) || '' === $body ) {
			/**
			 * Filters the headers of a file request (PRO sends its license there; the library serves PRO demos only
			 * to sites with an active license).
			 *
			 * @param array  $headers Headers.
			 * @param string $rel     File.
			 */
			$headers  = (array) apply_filters( 'tbfe/library_headers', array(), $rel );
			$response = self::get( add_query_arg( 'path', rawurlencode( $rel ), self::base() . '/file' ), 25, $headers );
			if ( ! is_wp_error( $response ) && in_array( (int) wp_remote_retrieve_response_code( $response ), array( 401, 403 ), true ) ) {
				return \TBFE\Pro::is_active()
					? new \WP_Error( 'tbfe_library_license', __( 'The library did not accept your PRO license. Check the license on the License page and try again.', 'theme-builder-for-elementor' ), array( 'status' => 403 ) )
					: \TBFE\Pro::locked_error();
			}
			if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
				return new \WP_Error( 'tbfe_library', __( 'The demo could not be downloaded from demos.blocks-wp.com. Please try again later.', 'theme-builder-for-elementor' ) );
			}
			$body = (string) wp_remote_retrieve_body( $response );
			if ( strlen( $body ) > self::MAX_FILE ) {
				return new \WP_Error( 'tbfe_library', __( 'The demo file is too large.', 'theme-builder-for-elementor' ) );
			}
		}

		$index = self::index();
		$hash  = is_array( $index ) && isset( $index['hashes'][ $rel ] ) ? $index['hashes'][ $rel ] : '';
		if ( '' === $hash || ! hash_equals( $hash, hash( 'sha256', $body ) ) ) {
			delete_site_transient( $cache );
			return new \WP_Error( 'tbfe_library_checksum', __( 'The downloaded demo does not match the library list (it may have been updated). Refresh the library and try again.', 'theme-builder-for-elementor' ), array( 'status' => 502 ) );
		}
		set_site_transient( $cache, $body, HOUR_IN_SECONDS );

		$data = json_decode( $body, true, 128 );
		return is_array( $data ) ? $data : new \WP_Error( 'tbfe_library', __( 'This demo cannot be imported.', 'theme-builder-for-elementor' ) );
	}

	/**
	 * Info for the administration.
	 */
	public static function status() {
		$state = self::state();
		return array(
			'loaded'      => $state['loaded'] ? gmdate( 'c', (int) $state['loaded'] ) : '',
			'next_manual' => self::next_manual() ? gmdate( 'c', self::next_manual() ) : '',
		);
	}
}
