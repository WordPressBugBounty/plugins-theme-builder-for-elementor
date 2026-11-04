<?php
/**
 * Colors of the demos: the palette of a demo (its colors with a role), recoloring before the import and linking the
 * main colors to the global colors of Elementor (Site Settings › Global Colors).
 *
 * The demos use plain hex colors. A choice of the visitor is a map old color => new color; with "globals" the main
 * colors are also linked to the Elementor global colors primary, secondary, text and accent, so the whole site is
 * recolored later in one place.
 *
 * @package TBFE
 */

namespace TBFE\Library;

defined( 'ABSPATH' ) || exit;

class Colors {

	/** Elementor global colors (system colors of the kit). */
	const SLOTS = array( 'primary', 'secondary', 'text', 'accent' );

	/** @var array|null Colors chosen for the current request (see set()). */
	private static $current = null;

	// ------------------------------------------------------------------------------------------------- palette

	/**
	 * Colors used by Elementor content: '#rrggbb' => count.
	 *
	 * @param array $contents Lists of Elementor elements (one per demo).
	 */
	public static function count( array $contents ) {
		$counts = array();
		array_walk_recursive(
			$contents,
			static function ( $value ) use ( &$counts ) {
				if ( ! is_string( $value ) || false === strpos( $value, '#' ) ) {
					return;
				}
				if ( preg_match_all( '/#([0-9a-f]{8}|[0-9a-f]{6}|[0-9a-f]{3})\b/i', $value, $matches ) ) {
					foreach ( $matches[0] as $hex ) {
						$hex            = self::normalize( $hex );
						$counts[ $hex ] = ( isset( $counts[ $hex ] ) ? $counts[ $hex ] : 0 ) + 1;
					}
				}
			}
		);
		arsort( $counts );
		return $counts;
	}

	/**
	 * Palette of demos: the colors with a role and the Elementor global color they fit.
	 *
	 * Roles: primary, secondary, accent (colorful), heading (darkest), text, background (lightest), border,
	 * tint (a light or dark shade of a colorful color: "linked" to it, it follows when that color changes).
	 *
	 * @param array $contents Lists of Elementor elements.
	 * @return array[] array( 'color', 'count', 'role', 'slot', 'linked' )
	 */
	public static function palette( array $contents ) {
		$counts = array_slice( self::count( $contents ), 0, 16, true );
		$colors = array();
		foreach ( $counts as $hex => $count ) {
			list( $h, $s, $l ) = self::hsl( $hex );
			$colors[]          = array(
				'color'  => $hex,
				'count'  => $count,
				'h'      => $h,
				's'      => $s,
				'l'      => $l,
				'role'   => '',
				'slot'   => '',
				'linked' => '',
			);
		}

		// Colorful colors (not near white or black): primary, secondary, accent by use.
		$colorful = array();
		foreach ( $colors as $i => $c ) {
			if ( $c['s'] >= 0.3 && $c['l'] > 0.18 && $c['l'] < 0.85 ) {
				$colorful[] = $i;
			}
		}
		foreach ( array_slice( $colorful, 0, 3 ) as $n => $i ) {
			$colors[ $i ]['role'] = array( 'primary', 'secondary', 'accent' )[ $n ];
		}

		// Shades of a colorful color (a light tint or a deep dark one of the same hue) follow that color.
		foreach ( $colors as $i => $c ) {
			if ( '' !== $c['role'] ) {
				continue;
			}
			$base = self::nearest( $colors, $i, $colorful );
			if ( null !== $base && ( ( $c['l'] >= 0.85 && $c['s'] >= 0.15 ) || ( $c['l'] <= 0.25 && $c['s'] >= 0.4 ) ) ) {
				$colors[ $i ]['role']   = 'tint';
				$colors[ $i ]['linked'] = $colors[ $base ]['color'];
			}
		}

		// Neutrals: the darkest is the heading color, the most used mid one the text, the lightest the background.
		$heading = null;
		$text    = null;
		$light   = null;
		foreach ( $colors as $i => $c ) {
			if ( '' === $c['role'] && $c['l'] <= 0.3 && ( null === $heading || $c['l'] < $colors[ $heading ]['l'] ) ) {
				$heading = $i;
			}
		}
		foreach ( $colors as $i => $c ) {
			if ( '' !== $c['role'] || $i === $heading ) {
				continue;
			}
			if ( null === $text && $c['l'] > 0.2 && $c['l'] < 0.7 ) {
				$text = $i;
			} elseif ( null === $light && $c['l'] >= 0.94 && '#ffffff' !== $c['color'] ) {
				$light = $i;
			}
		}
		if ( null !== $heading ) {
			$colors[ $heading ]['role'] = 'heading';
		}
		if ( null !== $text ) {
			$colors[ $text ]['role'] = 'text';
		}
		if ( null !== $light ) {
			$colors[ $light ]['role'] = 'background';
		}
		foreach ( $colors as $i => $c ) {
			if ( '' === $c['role'] ) {
				$colors[ $i ]['role'] = '#ffffff' === $c['color'] ? 'white' : ( $c['l'] > 0.7 ? 'border' : 'other' );
			}
		}

		// The Elementor global colors: primary, secondary (the 2nd colorful one, else the heading color), text, accent.
		$slots = array(
			'primary'   => self::with_role( $colors, 'primary' ),
			'secondary' => null !== self::with_role( $colors, 'secondary' ) ? self::with_role( $colors, 'secondary' ) : self::with_role( $colors, 'heading' ),
			'text'      => self::with_role( $colors, 'text' ),
			'accent'    => self::with_role( $colors, 'accent' ),
		);
		foreach ( $slots as $slot => $i ) {
			if ( null !== $i && '' === $colors[ $i ]['slot'] ) {
				$colors[ $i ]['slot'] = $slot;
			}
		}

		foreach ( $colors as $i => $c ) {
			unset( $colors[ $i ]['h'], $colors[ $i ]['s'], $colors[ $i ]['l'] );
		}
		return array_values( $colors );
	}

	/**
	 * Palette of demos of the library (one template, or the chosen pages of a website).
	 *
	 * @param array $entries array( array( 'kind' => 'item'|'page', 'id' => int ) ).
	 * @return array|\WP_Error array( 'palette' => array, 'site' => slot => color )
	 */
	public static function for_entries( array $entries ) {
		$index = Remote::index();
		if ( is_wp_error( $index ) ) {
			return $index;
		}
		$pages = array();
		foreach ( $index['sites'] as $site ) {
			$pages = array_merge( $pages, $site['pages'] );
		}
		$files = array();
		foreach ( array_slice( $entries, 0, 30 ) as $entry ) {
			$id   = isset( $entry['id'] ) ? absint( $entry['id'] ) : 0;
			$list = isset( $entry['kind'] ) && 'page' === $entry['kind'] ? $pages : $index['items'];
			foreach ( $list as $demo ) {
				if ( (int) $demo['id'] === $id && ! empty( $demo['file'] ) ) {
					$files[] = $demo['file'];
				}
			}
		}
		$contents = array();
		foreach ( array_unique( $files ) as $file ) {
			$data = Remote::file( $file );
			if ( ! is_wp_error( $data ) && isset( $data['content'] ) && is_array( $data['content'] ) ) {
				$contents[] = $data['content'];
			}
		}
		return array(
			'palette' => self::palette( $contents ),
			'site'    => self::site_colors(),
		);
	}

	/**
	 * The global colors of the active Elementor kit: slot => '#rrggbb'.
	 */
	public static function site_colors() {
		$colors = array();
		$kit    = class_exists( '\Elementor\Plugin' ) ? \Elementor\Plugin::$instance->kits_manager->get_active_kit() : null;
		if ( $kit ) {
			foreach ( (array) $kit->get_settings( 'system_colors' ) as $color ) {
				if ( isset( $color['_id'], $color['color'] ) && in_array( $color['_id'], self::SLOTS, true ) ) {
					$colors[ $color['_id'] ] = self::normalize( $color['color'] );
				}
			}
		}
		return $colors;
	}

	// ------------------------------------------------------------------------------------------------- the choice

	/**
	 * Colors chosen for the imports of this request.
	 *
	 * @param mixed $input array( 'map' => array( old => new ), 'globals' => bool, 'links' => array( old => slot ) ),
	 *                     or 'site' (the colors of the site's Elementor kit, linked to them).
	 */
	public static function set( $input ) {
		self::$current = null;
		if ( 'site' === $input ) {
			self::$current = array( 'site' => true );
			return;
		}
		if ( ! is_array( $input ) ) {
			return;
		}
		$map   = array();
		$links = array();
		foreach ( (array) ( isset( $input['map'] ) ? $input['map'] : array() ) as $old => $new ) {
			$old = self::normalize( $old );
			$new = self::normalize( $new );
			if ( $old && $new && $old !== $new ) {
				$map[ $old ] = $new;
			}
		}
		foreach ( (array) ( isset( $input['links'] ) ? $input['links'] : array() ) as $old => $slot ) {
			$old = self::normalize( $old );
			if ( $old && in_array( $slot, self::SLOTS, true ) ) {
				$links[ $old ] = $slot;
			}
		}
		$globals = ! empty( $input['globals'] ) && $links;
		if ( $map || $globals ) {
			self::$current = array(
				'map'     => $map,
				'links'   => $globals ? $links : array(),
				'globals' => $globals,
			);
		}
	}

	/**
	 * With "globals": the Elementor global colors get the chosen colors (the whole site follows).
	 */
	public static function update_site_colors() {
		if ( empty( self::$current['globals'] ) || ! class_exists( '\Elementor\Plugin' ) ) {
			return;
		}
		$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
		// Elementor refuses to save the kit for a user who may not edit it (the import goes on without it).
		if ( ! $kit || ! current_user_can( 'edit_post', $kit->get_main_id() ) ) {
			return;
		}
		$new = array();
		foreach ( self::$current['links'] as $old => $slot ) {
			$new[ $slot ] = isset( self::$current['map'][ $old ] ) ? self::$current['map'][ $old ] : $old;
		}
		$colors = (array) $kit->get_settings( 'system_colors' );
		foreach ( $colors as $i => $color ) {
			if ( isset( $color['_id'], $new[ $color['_id'] ] ) ) {
				$colors[ $i ]['color'] = $new[ $color['_id'] ];
			}
		}
		try {
			$kit->update_settings( array( 'system_colors' => $colors ) );
		} catch ( \Exception $e ) {
			return;
		}
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}

	/**
	 * Elementor content in the chosen colors (unchanged without a choice).
	 */
	public static function content( array $elements ) {
		if ( ! self::$current ) {
			return $elements;
		}
		$choice = self::$current;
		if ( ! empty( $choice['site'] ) ) {
			$choice = self::site_choice( $elements );
		}
		return self::recolor( $elements, $choice['map'], $choice['links'] );
	}

	/**
	 * Page settings (backgrounds of the page) in the chosen colors.
	 */
	public static function settings( array $settings ) {
		if ( ! self::$current || ! empty( self::$current['site'] ) ) {
			return $settings;
		}
		return self::replace( $settings, self::$current['map'] );
	}

	/**
	 * "My site colors": the main colors of the demo become the global colors of the site, linked to them.
	 */
	private static function site_choice( array $elements ) {
		$site  = self::site_colors();
		$map   = array();
		$links = array();
		foreach ( self::palette( array( $elements ) ) as $c ) {
			if ( $c['slot'] && isset( $site[ $c['slot'] ] ) ) {
				$map[ $c['color'] ]   = $site[ $c['slot'] ];
				$links[ $c['color'] ] = $c['slot'];
			}
		}
		// Tints follow their colorful color.
		foreach ( self::palette( array( $elements ) ) as $c ) {
			if ( 'tint' === $c['role'] && isset( $map[ $c['linked'] ] ) ) {
				$map[ $c['color'] ] = self::follow( $c['color'], $c['linked'], $map[ $c['linked'] ] );
			}
		}
		return array(
			'map'   => $map,
			'links' => $links,
		);
	}

	// ------------------------------------------------------------------------------------------------- recoloring

	/**
	 * Replaces the colors in all settings; settings that are exactly a linked color get the Elementor global color.
	 */
	private static function recolor( array $elements, array $map, array $links ) {
		foreach ( $elements as $i => $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}
			if ( isset( $element['settings'] ) && is_array( $element['settings'] ) ) {
				$settings = $element['settings'];
				if ( $links ) {
					foreach ( $settings as $key => $value ) {
						if ( '__globals__' === $key || ! is_string( $value ) ) {
							continue;
						}
						$hex = self::normalize( $value );
						if ( $hex && isset( $links[ $hex ] ) && empty( $settings['__globals__'][ $key ] ) ) {
							$settings['__globals__'][ $key ] = 'globals/colors?id=' . $links[ $hex ];
						}
					}
				}
				$element['settings'] = self::replace( $settings, $map );
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$element['elements'] = self::recolor( $element['elements'], $map, $links );
			}
			$elements[ $i ] = $element;
		}
		return $elements;
	}

	/**
	 * Replaces the colors in every string of the settings: #rgb, #rrggbb, #rrggbbaa (the alpha stays) and rgb[a]().
	 */
	private static function replace( array $settings, array $map ) {
		if ( ! $map ) {
			return $settings;
		}
		array_walk_recursive(
			$settings,
			static function ( &$value, $key ) use ( $map ) {
				if ( ! is_string( $value ) || '' === $value || ( false === strpos( $value, '#' ) && false === stripos( $value, 'rgb' ) ) ) {
					return;
				}
				$value = preg_replace_callback(
					'/#([0-9a-f]{8}|[0-9a-f]{6}|[0-9a-f]{3})\b/i',
					static function ( $m ) use ( $map ) {
						$hex   = self::normalize( $m[0] );
						$alpha = 9 === strlen( $m[0] ) ? strtolower( substr( $m[0], 7 ) ) : '';
						return isset( $map[ $hex ] ) ? $map[ $hex ] . $alpha : $m[0];
					},
					$value
				);
				$value = preg_replace_callback(
					'/rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(,\s*[0-9.]+\s*)?\)/i',
					static function ( $m ) use ( $map ) {
						$hex = sprintf( '#%02x%02x%02x', min( 255, (int) $m[1] ), min( 255, (int) $m[2] ), min( 255, (int) $m[3] ) );
						if ( ! isset( $map[ $hex ] ) ) {
							return $m[0];
						}
						list( $r, $g, $b ) = sscanf( $map[ $hex ], '#%02x%02x%02x' );
						return isset( $m[4] ) && '' !== $m[4] ? sprintf( 'rgba(%d, %d, %d%s)', $r, $g, $b, $m[4] ) : sprintf( 'rgb(%d, %d, %d)', $r, $g, $b );
					},
					$value
				);
			}
		);
		return $settings;
	}

	// ------------------------------------------------------------------------------------------------- color math

	/**
	 * '#rrggbb' (lower case) or '' when it is not a hex color. #rgb is expanded, the alpha of #rrggbbaa dropped.
	 */
	public static function normalize( $color ) {
		$color = strtolower( trim( (string) $color ) );
		if ( preg_match( '/^#([0-9a-f]{3})$/', $color, $m ) ) {
			return '#' . $m[1][0] . $m[1][0] . $m[1][1] . $m[1][1] . $m[1][2] . $m[1][2];
		}
		if ( preg_match( '/^#([0-9a-f]{6})([0-9a-f]{2})?$/', $color, $m ) ) {
			return '#' . $m[1];
		}
		return '';
	}

	/**
	 * Hue (0–360), saturation and lightness (0–1).
	 */
	public static function hsl( $hex ) {
		list( $r, $g, $b ) = array_map(
			static function ( $v ) {
				return $v / 255;
			},
			sscanf( $hex, '#%02x%02x%02x' )
		);
		$max = max( $r, $g, $b );
		$min = min( $r, $g, $b );
		$l   = ( $max + $min ) / 2;
		$d   = $max - $min;
		if ( 0.0 === (float) $d ) {
			return array( 0, 0, $l );
		}
		$s = $l > 0.5 ? $d / ( 2 - $max - $min ) : $d / ( $max + $min );
		if ( $max === $r ) {
			$h = ( $g - $b ) / $d + ( $g < $b ? 6 : 0 );
		} elseif ( $max === $g ) {
			$h = ( $b - $r ) / $d + 2;
		} else {
			$h = ( $r - $g ) / $d + 4;
		}
		return array( $h * 60, $s, $l );
	}

	public static function hex( $h, $s, $l ) {
		$h /= 360;
		if ( 0.0 === (float) $s ) {
			$r = $l;
			$g = $l;
			$b = $l;
		} else {
			$q   = $l < 0.5 ? $l * ( 1 + $s ) : $l + $s - $l * $s;
			$p   = 2 * $l - $q;
			$hue = static function ( $t ) use ( $p, $q ) {
				$t = $t < 0 ? $t + 1 : ( $t > 1 ? $t - 1 : $t );
				if ( $t < 1 / 6 ) {
					return $p + ( $q - $p ) * 6 * $t;
				}
				if ( $t < 1 / 2 ) {
					return $q;
				}
				if ( $t < 2 / 3 ) {
					return $p + ( $q - $p ) * ( 2 / 3 - $t ) * 6;
				}
				return $p;
			};
			$r   = $hue( $h + 1 / 3 );
			$g   = $hue( $h );
			$b   = $hue( $h - 1 / 3 );
		}
		return sprintf( '#%02x%02x%02x', round( $r * 255 ), round( $g * 255 ), round( $b * 255 ) );
	}

	/**
	 * A tint (light or dark shade) of a colorful color after that color changed: the new hue, the saturation in the
	 * same proportion, its own lightness.
	 */
	public static function follow( $tint, $old_base, $new_base ) {
		list( , $ts, $tl )  = self::hsl( $tint );
		list( , $os )       = self::hsl( $old_base );
		list( $nh, $ns )    = self::hsl( $new_base );
		$s                  = $os > 0 ? min( 1, $ts * $ns / $os ) : $ts;
		return self::hex( $nh, $s, $tl );
	}

	/**
	 * Index of the colorful color with the nearest hue, null when none is close (within 25°).
	 */
	private static function nearest( array $colors, $index, array $colorful ) {
		$best = null;
		$diff = 25;
		foreach ( $colorful as $i ) {
			$d = abs( $colors[ $i ]['h'] - $colors[ $index ]['h'] );
			$d = min( $d, 360 - $d );
			if ( $d <= $diff ) {
				$diff = $d;
				$best = $i;
			}
		}
		return $best;
	}

	private static function with_role( array $colors, $role ) {
		foreach ( $colors as $i => $c ) {
			if ( $role === $c['role'] ) {
				return $i;
			}
		}
		return null;
	}
}
