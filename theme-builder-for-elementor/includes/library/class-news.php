<?php
/**
 * New demos: when the library list brings templates or websites the site has not seen yet, the admin sees them in a
 * notice with small previews, and the library marks new items.
 *
 * - The site remembers the demos it has seen (option tbfe_library_seen). On the first run every demo counts as seen,
 *   so a fresh install shows no notice.
 * - The list is refreshed once a day by WP-Cron (the same daily load as the library), never while an admin page loads.
 * - The notice shows on the Dashboard, the Plugins screen and the Theme Builder screen (newest first, up to six
 *   previews). New PRO demos alone are shown only on the Theme Builder screen. Dismissing it, or opening the new
 *   demos, marks them as seen.
 * - "New" in the library: demos added in the last 30 days ("added" date of the list).
 *
 * @package TBFE
 */

namespace TBFE\Library;

use TBFE\Admin\Admin;
use TBFE\Templates\Post_Type;

defined( 'ABSPATH' ) || exit;

class News {

	const OPTION   = 'tbfe_library_seen';
	const REFRESH  = 'tbfe_library_refresh';
	const NEW_DAYS = 30;
	const SHOWN    = 6;

	public static function init() {
		add_action( self::REFRESH, array( __CLASS__, 'refresh' ) );
		if ( ! is_admin() ) {
			return;
		}
		add_action( 'admin_init', array( __CLASS__, 'first_run' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
		add_action( 'wp_ajax_tbfe_library_seen', array( __CLASS__, 'ajax_seen' ) );
		add_action( 'load-toplevel_page_' . Admin::SLUG, array( __CLASS__, 'opened' ) );
	}

	/**
	 * The daily load of the list (only when the stored copy is older than a day).
	 */
	public static function refresh() {
		Remote::index();
	}

	/**
	 * Whether a demo (template or website) was added in the last 30 days.
	 *
	 * @param array $item Item of the list with "added".
	 * @return bool
	 */
	public static function is_new( $item ) {
		$added = ! empty( $item['added'] ) ? strtotime( $item['added'] ) : false;
		return $added && $added > time() - self::NEW_DAYS * DAY_IN_SECONDS && $added <= time() + DAY_IN_SECONDS;
	}

	/**
	 * Every demo of the stored list, by key (t<id> template, s<id> website).
	 *
	 * @return array[]|null Null when there is no list yet.
	 */
	private static function all() {
		$index = Remote::cached();
		if ( ! $index ) {
			return null;
		}
		$all = array();
		foreach ( $index['items'] as $item ) {
			$all[ 't' . $item['id'] ] = array_merge( $item, array( 'site' => false ) );
		}
		foreach ( $index['sites'] as $site ) {
			$all[ 's' . $site['id'] ] = array_merge( $site, array( 'site' => true ) );
		}
		return $all;
	}

	/**
	 * Keys of the demos the site has seen; null before the first run.
	 *
	 * @return string[]|null
	 */
	private static function seen() {
		$seen = get_option( self::OPTION, null );
		return is_array( $seen ) ? $seen : null;
	}

	/**
	 * Marks every demo of the stored list as seen.
	 */
	public static function mark_seen() {
		$all = self::all();
		if ( null !== $all ) {
			update_option( self::OPTION, array_keys( $all ), false );
		}
	}

	/**
	 * On the first admin page with a list, everything there is counts as seen. The daily refresh is scheduled here.
	 */
	public static function first_run() {
		if ( ! wp_next_scheduled( self::REFRESH ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::REFRESH );
		}
		if ( null === self::seen() && current_user_can( Post_Type::CAPABILITY ) ) {
			self::mark_seen();
		}
	}

	/**
	 * Demos not seen yet, newest first.
	 *
	 * @return array[]
	 */
	public static function unseen() {
		$seen = self::seen();
		$all  = self::all();
		if ( null === $seen || ! $all ) {
			return array();
		}
		$list = array_values( array_diff_key( $all, array_flip( $seen ) ) );
		usort(
			$list,
			static function ( $a, $b ) {
				return strcmp( (string) $b['added'], (string) $a['added'] );
			}
		);
		return $list;
	}

	/**
	 * Opening the Theme Builder screen with the new demos (?tbfe_new=1) marks them as seen.
	 */
	public static function opened() {
		if ( isset( $_GET['tbfe_new'] ) && current_user_can( Post_Type::CAPABILITY ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only marks the notice as read.
			self::mark_seen();
		}
	}

	/**
	 * The notice.
	 */
	public static function notice() {
		if ( ! current_user_can( Post_Type::CAPABILITY ) ) {
			return;
		}
		$screen = get_current_screen();
		$own    = $screen && 'toplevel_page_' . Admin::SLUG === $screen->id;
		if ( ! $screen || ( ! $own && ! in_array( $screen->id, array( 'dashboard', 'plugins' ), true ) ) ) {
			return;
		}
		$new = self::unseen();
		if ( ! $own ) {
			// Outside the plugin's screen only free demos are news (no advertising for PRO on the Dashboard).
			$new = array_values(
				array_filter(
					$new,
					static function ( $demo ) {
						return empty( $demo['pro'] );
					}
				)
			);
		}
		if ( ! $new ) {
			return;
		}
		$sites = in_array( true, wp_list_pluck( $new, 'site' ), true );
		$url   = admin_url( 'admin.php?page=' . Admin::SLUG . '&tbfe_new=1' ) . ( $sites ? '#/websites' : '#/library' );
		$base  = Remote::cached()['previews_url'];
		$more  = count( $new ) - self::SHOWN;
		Admin::extras(); // Styles of the notice, closing it marks the demos as seen.
		?>
		<div class="notice notice-info is-dismissible tbfe-news" data-nonce="<?php echo esc_attr( wp_create_nonce( 'tbfe_library_seen' ) ); ?>">
			<p class="tbfe-news__title">
				<strong>
					<?php
					/* translators: %d: number of new demos. */
					echo esc_html( sprintf( _n( '%d new demo in the Theme Builder library', '%d new demos in the Theme Builder library', count( $new ), 'theme-builder-for-elementor' ), count( $new ) ) );
					?>
				</strong>
				– <?php esc_html_e( 'websites and templates you can import in one click.', 'theme-builder-for-elementor' ); ?>
			</p>
			<ul class="tbfe-news__list">
				<?php foreach ( array_slice( $new, 0, self::SHOWN ) as $demo ) : ?>
					<?php $image = Previews::data( $demo['image'], $base ); ?>
					<li>
						<a href="<?php echo esc_url( $url ); ?>">
							<span class="tbfe-news__img">
								<?php if ( $image ) : ?>
									<img src="<?php echo esc_url( $image['src'] ); ?>" srcset="<?php echo esc_attr( $image['srcset'] ); ?>" sizes="150px" alt="" loading="lazy" decoding="async" width="150" height="112">
								<?php endif; ?>
								<span class="tbfe-news__kind"><?php echo $demo['site'] ? esc_html__( 'Website', 'theme-builder-for-elementor' ) : esc_html__( 'Template', 'theme-builder-for-elementor' ); ?></span>
								<?php if ( ! empty( $demo['pro'] ) ) : ?>
									<span class="tbfe-news__pro"><?php esc_html_e( 'PRO', 'theme-builder-for-elementor' ); ?></span>
								<?php endif; ?>
							</span>
							<span class="tbfe-news__name"><?php echo esc_html( $demo['title'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
				<?php if ( $more > 0 ) : ?>
					<li class="tbfe-news__more">
						<a href="<?php echo esc_url( $url ); ?>">
							<?php
							/* translators: %d: number of further new demos. */
							echo esc_html( sprintf( __( '+%d more', 'theme-builder-for-elementor' ), $more ) );
							?>
						</a>
					</li>
				<?php endif; ?>
			</ul>
			<p><a class="button button-primary" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'See the new demos', 'theme-builder-for-elementor' ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * The notice was closed.
	 */
	public static function ajax_seen() {
		check_ajax_referer( 'tbfe_library_seen' );
		if ( ! current_user_can( Post_Type::CAPABILITY ) ) {
			wp_send_json_error( null, 403 );
		}
		self::mark_seen();
		wp_send_json_success();
	}
}
