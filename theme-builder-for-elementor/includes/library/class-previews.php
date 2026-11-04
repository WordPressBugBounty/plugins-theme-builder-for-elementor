<?php
/**
 * Preview images of the demo library.
 *
 * The images are on demos.blocks-wp.com in two widths with the version in the file name (cached for a long time).
 * When the administration has shown a preview, it asks this site to keep a copy in uploads/assets/tbfe-previews/ (one folder for the whole network); from
 * then on the site serves the copy and does not contact the demos site for it again. Copies that are no longer in
 * the list are removed once a week; the folder never grows over 50 MB.
 *
 * @package TBFE
 */

namespace TBFE\Library;

defined( 'ABSPATH' ) || exit;

class Previews {

	const FOLDER          = 'tbfe-previews';
	const MAX_PER_REQUEST = 30;
	const MAX_FOLDER_SIZE = 52428800;
	const MAX_IMAGE_SIZE  = 1048576;
	const PRUNE           = 'tbfe_prune_previews';

	public static function init() {
		add_action( self::PRUNE, array( __CLASS__, 'prune' ) );
	}

	public static function schedule_prune() {
		if ( ! wp_next_scheduled( self::PRUNE ) ) {
			wp_schedule_event( time() + WEEK_IN_SECONDS, 'weekly', self::PRUNE );
		}
	}

	/**
	 * The folder wp-content/uploads/assets/ of the network: the uploads of the main site, also in a multisite, so
	 * every site of the network uses the same folder. Filter tbfe/assets to move it.
	 *
	 * @return array array( 'dir' => path with a slash, 'url' => URL with a slash, 'error' => bool )
	 */
	public static function assets() {
		$switch = is_multisite() && ! is_main_site();
		if ( $switch ) {
			switch_to_blog( get_main_site_id() );
		}
		$uploads = wp_get_upload_dir();
		if ( $switch ) {
			restore_current_blog();
		}
		$assets = array(
			'dir'   => trailingslashit( $uploads['basedir'] ) . 'assets/',
			'url'   => trailingslashit( $uploads['baseurl'] ) . 'assets/',
			'error' => ! empty( $uploads['error'] ),
		);
		return (array) apply_filters( 'tbfe/assets', $assets );
	}

	/**
	 * Folder and URL of the kept copies: uploads/assets/tbfe-previews/ ('' when uploads are not available).
	 */
	private static function location() {
		$assets = self::assets();
		if ( ! empty( $assets['error'] ) ) {
			return array(
				'dir' => '',
				'url' => '',
			);
		}
		return array(
			'dir' => trailingslashit( $assets['dir'] ) . self::FOLDER . '/',
			'url' => trailingslashit( $assets['url'] ) . self::FOLDER . '/',
		);
	}

	/**
	 * Image data for the administration: src, srcset, lqip, ratio, key, remote (some width is not kept yet).
	 *
	 * @param array|null $image Image of the list.
	 * @param string     $base  URL of the previews on the demos site.
	 * @return array|null
	 */
	public static function data( $image, $base ) {
		if ( ! $image || empty( $image['files'] ) ) {
			return null;
		}
		$location = self::location();
		$srcset   = array();
		$remote   = false;
		$src      = '';
		ksort( $image['files'] );
		foreach ( $image['files'] as $width => $name ) {
			if ( '' !== $location['dir'] && is_file( $location['dir'] . $name ) ) {
				$url = $location['url'] . $name;
			} else {
				$url    = $base . $name;
				$remote = true;
			}
			$srcset[] = esc_url_raw( $url ) . ' ' . (int) $width . 'w';
			$src      = esc_url_raw( $url ); // The largest width.
		}
		return array(
			'src'    => $src,
			'srcset' => implode( ', ', $srcset ),
			'lqip'   => $image['lqip'],
			'ratio'  => $image['w'] && $image['h'] ? $image['w'] . ' / ' . $image['h'] : '',
			'key'    => $image['key'],
			'remote' => $remote,
		);
	}

	/**
	 * All images of the list, keyed by key.
	 */
	private static function images( array $index ) {
		$images = array();
		$add    = static function ( $image ) use ( &$images ) {
			if ( $image ) {
				$images[ $image['key'] ] = $image;
			}
		};
		foreach ( $index['items'] as $item ) {
			$add( $item['image'] );
		}
		foreach ( $index['sites'] as $site ) {
			$add( $site['image'] );
			foreach ( $site['pages'] as $page ) {
				$add( $page['image'] );
			}
		}
		return $images;
	}

	private static function folder_size( $dir ) {
		$size = 0;
		foreach ( (array) glob( $dir . '*.webp' ) as $file ) {
			$size += (int) filesize( $file );
		}
		return $size;
	}

	/**
	 * Keeps copies of the shown previews.
	 *
	 * @param string[] $keys Keys of the previews.
	 * @return string[] Kept keys.
	 */
	public static function keep( array $keys ) {
		$index    = Remote::index();
		$location = self::location();
		if ( is_wp_error( $index ) || '' === $location['dir'] || ! wp_mkdir_p( $location['dir'] ) || self::folder_size( $location['dir'] ) > self::MAX_FOLDER_SIZE ) {
			return array();
		}
		$images = self::images( $index );
		$kept   = array();
		$count  = 0;
		foreach ( array_unique( array_map( 'sanitize_key', $keys ) ) as $key ) {
			if ( $count >= self::MAX_PER_REQUEST || ! isset( $images[ $key ] ) ) {
				continue;
			}
			++$count;
			$ok = true;
			foreach ( $images[ $key ]['files'] as $name ) {
				$target = $location['dir'] . $name;
				if ( ! is_file( $target ) ) {
					$ok = self::download( $index['previews_url'] . $name, $target ) && $ok;
				}
			}
			if ( $ok ) {
				$kept[] = $key;
			}
		}
		self::schedule_prune();
		return $kept;
	}

	/**
	 * Downloads one preview: only a WebP image of a sane size is saved.
	 */
	private static function download( $url, $target ) {
		$response = wp_safe_remote_get(
			$url,
			array(
				'timeout'    => 15,
				'user-agent' => 'ThemeBuilderForElementor/' . TBFE_VERSION, // No address of the site.
			)
		);
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}
		$body = (string) wp_remote_retrieve_body( $response );
		if ( '' === $body || strlen( $body ) > self::MAX_IMAGE_SIZE || 'RIFF' !== substr( $body, 0, 4 ) || 'WEBP' !== substr( $body, 8, 4 ) ) {
			return false;
		}
		return false !== file_put_contents( $target, $body ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}

	/**
	 * Removes kept copies that are not in the list any more.
	 */
	public static function prune() {
		$location = self::location();
		$index    = Remote::index();
		if ( '' === $location['dir'] || ! is_dir( $location['dir'] ) || is_wp_error( $index ) ) {
			return;
		}
		$names = array();
		foreach ( self::images( $index ) as $image ) {
			$names = array_merge( $names, array_values( $image['files'] ) );
		}
		foreach ( (array) glob( $location['dir'] . '*.webp' ) as $file ) {
			if ( ! in_array( basename( $file ), $names, true ) ) {
				wp_delete_file( $file );
			}
		}
	}
}
