<?php
/**
 * Demo library: ready-made templates and website demos from demos.blocks-wp.com (see Remote and Previews).
 *
 * The imported content goes through Elementor's own import (new element IDs, images copied into the media library).
 *
 * A website demo is imported page by page (one request each, so large sites do not time out) and then finished:
 * links between the pages point to the new pages, a menu is created, the home page can become the front page.
 *
 * @package TBFE
 */

namespace TBFE\Library;

use TBFE\Pro;
use TBFE\Templates\Template;
use TBFE\Templates\Types;

defined( 'ABSPATH' ) || exit;

class Library {

	private static function text( $item, $key ) {
		return sanitize_text_field( isset( $item[ $key ] ) ? (string) $item[ $key ] : '' );
	}

	/**
	 * A PRO demo (a PRO website, a template marked PRO, or a template of a PRO type).
	 */
	private static function is_pro( array $entry ) {
		return ! empty( $entry['pro'] ) || ( isset( $entry['type'] ) && Pro::is_pro_type( $entry['type'] ) );
	}

	/**
	 * A PRO demo can be imported only with an active PRO license (and its type registered by PRO).
	 *
	 * @return true|\WP_Error
	 */
	private static function check_pro( array $entry ) {
		if ( ! empty( $entry['woocommerce'] ) && ! class_exists( 'WooCommerce' ) ) {
			return self::woocommerce_error();
		}
		if ( ! self::is_pro( $entry ) ) {
			return true;
		}
		if ( ! Pro::is_active() ) {
			return Pro::locked_error();
		}
		if ( isset( $entry['type'] ) && ! Types::exists( $entry['type'] ) ) {
			return self::woocommerce_error();
		}
		return true;
	}

	/**
	 * The demo needs WooCommerce (the administration offers to install it).
	 */
	public static function woocommerce_error() {
		return new \WP_Error(
			'tbfe_needs_woocommerce',
			__( 'This demo needs WooCommerce. Install and activate WooCommerce, then import it again.', 'theme-builder-for-elementor' ),
			array(
				'status' => 409,
				'needs'  => 'woocommerce',
			)
		);
	}

	/**
	 * Widget types of Elementor content.
	 *
	 * @return string[]
	 */
	private static function widget_types( array $elements ) {
		$types = array();
		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}
			if ( ! empty( $element['widgetType'] ) ) {
				$types[] = (string) $element['widgetType'];
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$types = array_merge( $types, self::widget_types( $element['elements'] ) );
			}
		}
		return array_values( array_unique( $types ) );
	}

	/**
	 * Every widget of the demo must be on this site: Elementor would drop a missing one without a word.
	 *
	 * @return true|\WP_Error What is missing and why: WooCommerce, PRO, a widget switched off, another plugin.
	 */
	private static function check_widgets( array $content ) {
		$manager = \Elementor\Plugin::$instance->widgets_manager;
		$missing = array_values(
			array_filter(
				self::widget_types( $content ),
				static function ( $type ) use ( $manager ) {
					return ! $manager->get_widget_types( $type );
				}
			)
		);
		$pro = wp_list_pluck( Pro::teaser_widgets(), 'title', 'name' );
		// PRO widgets cannot be added without an active license (also not with a demo).
		if ( Pro::installed() && ! Pro::is_active() && array_intersect( self::widget_types( $content ), array_keys( $pro ) ) ) {
			return Pro::locked_error();
		}
		if ( ! $missing ) {
			return true;
		}
		$disabled = array_intersect( $missing, \TBFE\Elementor\Integration::disabled() );
		if ( $disabled ) {
			// The names of all widgets, also of the switched-off ones.
			$pro = array_merge( wp_list_pluck( \TBFE\Elementor\Integration::widget_list(), 'title', 'name' ), $pro );
		}
		$names    = static function ( array $types ) use ( $pro ) {
			return implode(
				', ',
				array_map(
					static function ( $type ) use ( $pro ) {
						return isset( $pro[ $type ] ) ? $pro[ $type ] : $type;
					},
					$types
				)
			);
		};
		if ( $disabled ) {
			return new \WP_Error(
				'tbfe_widgets_off',
				/* translators: %s: widget names */
				sprintf( __( 'This demo uses widgets that are switched off in Settings › Widgets: %s. Switch them on and import it again.', 'theme-builder-for-elementor' ), $names( array_values( $disabled ) ) ),
				array( 'status' => 409 )
			);
		}
		if ( array_intersect( $missing, array_keys( $pro ) ) ) {
			if ( ! class_exists( 'WooCommerce' ) ) {
				return self::woocommerce_error();
			}
			if ( ! Pro::installed() ) {
				return Pro::locked_error();
			}
		}
		return new \WP_Error(
			'tbfe_widgets_missing',
			/* translators: %s: widget names */
			sprintf( __( 'This demo uses widgets that are not on this site: %s. Activate the plugin that adds them, then import it again.', 'theme-builder-for-elementor' ), $names( $missing ) ),
			array( 'status' => 409 )
		);
	}

	/**
	 * The library for the administration: the lists with the preview images, and when it was loaded.
	 *
	 * @param bool $manual The user asked to refresh it.
	 * @return array|\WP_Error
	 */
	public static function for_admin( $manual = false ) {
		$index = Remote::index( $manual );
		if ( is_wp_error( $index ) ) {
			return $index;
		}
		$base  = $index['previews_url'];
		$items = array();
		foreach ( $index['items'] as $item ) {
			$item['image'] = Previews::data( $item['image'], $base );
			$item['pro']   = self::is_pro( $item );
			$item['new']   = News::is_new( $item );
			unset( $item['file'] );
			$items[] = $item;
		}
		$sites = array();
		foreach ( $index['sites'] as $site ) {
			$site['image'] = Previews::data( $site['image'], $base );
			$site['pro']   = self::is_pro( $site );
			$site['new']   = News::is_new( $site );
			// Needs WooCommerce: a page with WooCommerce widgets or a shop template.
			$site['woocommerce'] = ! empty( $site['woocommerce'] ) || in_array( true, wp_list_pluck( $site['pages'], 'woocommerce' ), true ) || (bool) array_intersect( wp_list_pluck( $site['templates'], 'type' ), array_keys( Pro::woo_types() ) );
			foreach ( $site['pages'] as $i => $page ) {
				$page['image'] = Previews::data( $page['image'], $base );
				unset( $page['file'] );
				$site['pages'][ $i ] = $page;
			}
			$sites[] = $site;
		}
		return array_merge(
			array(
				'items'     => $items,
				'sites'     => $sites,
				'published' => $index['published'],
			),
			Remote::status()
		);
	}

	/**
	 * Entry of the list: a template (items) or a page of a website (pages).
	 */
	private static function find( $kind, $id ) {
		$index = Remote::index();
		if ( is_wp_error( $index ) ) {
			return $index;
		}
		$id = absint( $id );
		if ( 'items' === $kind ) {
			foreach ( $index['items'] as $item ) {
				if ( $item['id'] === $id ) {
					return $item;
				}
			}
		} else {
			foreach ( $index['sites'] as $site ) {
				foreach ( $site['pages'] as $page ) {
					if ( $page['id'] === $id ) {
						return $page;
					}
				}
			}
		}
		return new \WP_Error( 'tbfe_library', __( 'This demo is not in the library any more. Refresh the library.', 'theme-builder-for-elementor' ), array( 'status' => 404 ) );
	}

	/**
	 * Saves Elementor content into a post (after Elementor's import step).
	 *
	 * @return true|\WP_Error
	 */
	private static function save_content( $post_id, array $content, array $settings ) {
		$importer = new Elementor_Importer();
		$content  = $importer->prepare( References::content( Colors::content( $content ) ) );
		$settings = Colors::settings( $settings );
		unset( $settings['tbfe_preview_id'], $settings['post_status'], $settings['post_title'], $settings['template'] );

		$document = \Elementor\Plugin::$instance->documents->get( $post_id, false );
		if ( ! $document ) {
			return new \WP_Error( 'tbfe_library', __( 'Elementor could not open the new post.', 'theme-builder-for-elementor' ) );
		}
		$document->save(
			array(
				'elements' => $content,
				'settings' => $settings,
			)
		);
		return true;
	}

	/**
	 * Imports one template as a new template (draft, without conditions).
	 *
	 * @return int|\WP_Error Template ID.
	 */
	public static function import( $item_id, $status = 'draft' ) {
		$item_id = absint( $item_id );
		$item    = self::find( 'items', $item_id );
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$allowed = self::check_pro( $item );
		if ( is_wp_error( $allowed ) ) {
			return $allowed;
		}
		$data = Remote::file( $item['file'] );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		$widgets = self::check_widgets( isset( $data['content'] ) && is_array( $data['content'] ) ? $data['content'] : array() );
		if ( is_wp_error( $widgets ) ) {
			return $widgets;
		}
		$type = isset( $data['type'] ) ? sanitize_key( $data['type'] ) : '';
		if ( ! Pro::type_unlocked( $type ) ) {
			return Pro::locked_error();
		}
		if ( ! Types::exists( $type ) || ! isset( $data['content'] ) || ! is_array( $data['content'] ) ) {
			return new \WP_Error( 'tbfe_library', __( 'This demo cannot be imported.', 'theme-builder-for-elementor' ) );
		}

		$title = self::text( $data, 'title' );
		$id    = Template::create( $type, '' !== $title ? $title : Types::get( $type )['label'], $status );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$saved = self::save_content( $id, $data['content'], isset( $data['page_settings'] ) && is_array( $data['page_settings'] ) ? $data['page_settings'] : array() );
		if ( is_wp_error( $saved ) ) {
			wp_delete_post( $id, true );
			return $saved;
		}
		update_post_meta( $id, '_tbfe_library_item', $item_id );
		if ( ! empty( $data['settings'] ) && is_array( $data['settings'] ) ) {
			Template::set_settings( $id, References::settings( $data['settings'] ) );
		}
		return $id;
	}

	/**
	 * Imports one page of a website demo as a new page, or into an existing page or post (its content is replaced).
	 *
	 * @param int    $page_id Page ID on the demos site.
	 * @param string $status  publish or draft (new pages only).
	 * @param int    $into    Existing page or post that gets the design (0 = a new page).
	 * @param string $layout  full-width (between the header and footer) or canvas (the page alone).
	 * @return array|\WP_Error array( 'id', 'demo_url', 'url', 'edit_url', 'title', 'home' )
	 */
	public static function import_page( $page_id, $status = 'draft', $into = 0, $layout = 'full-width' ) {
		$page = self::find( 'pages', $page_id );
		if ( is_wp_error( $page ) ) {
			return $page;
		}
		$allowed = self::check_pro( $page );
		if ( is_wp_error( $allowed ) ) {
			return $allowed;
		}
		$data = Remote::file( $page['file'] );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		$widgets = self::check_widgets( isset( $data['content'] ) && is_array( $data['content'] ) ? $data['content'] : array() );
		if ( is_wp_error( $widgets ) ) {
			return $widgets;
		}
		if ( ! isset( $data['content'] ) || ! is_array( $data['content'] ) ) {
			return new \WP_Error( 'tbfe_library', __( 'This page cannot be imported.', 'theme-builder-for-elementor' ) );
		}
		$title = self::text( $data, 'title' );
		if ( $into ) {
			$target = self::import_target( $into );
			if ( is_wp_error( $target ) ) {
				return $target;
			}
			$id = $target->ID;
			// A new post that was never saved would be removed by WordPress as an auto-draft. It still has the "Auto Draft"
			// title, so it gets the title of the demo page like a post without a title.
			$update = array( 'ID' => $id );
			$fresh  = 'auto-draft' === $target->post_status;
			if ( $fresh ) {
				$update['post_status'] = 'draft';
			}
			if ( ( $fresh || '' === trim( $target->post_title ) ) && '' !== $title ) {
				$update['post_title'] = $title;
			}
			if ( count( $update ) > 1 ) {
				wp_update_post( $update );
			}
		} else {
			$id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_title'  => '' !== $title ? $title : __( 'Imported page', 'theme-builder-for-elementor' ),
					'post_status' => 'publish' === $status ? 'publish' : 'draft',
				),
				true
			);
			if ( is_wp_error( $id ) ) {
				return $id;
			}
		}
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $id, '_elementor_template_type', 'post' === get_post_type( $id ) ? 'wp-post' : 'wp-page' );
		// A page template of the plugin (it works with every theme): between the header and footer, or the page alone.
		$layouts = \TBFE\Render\Renderer::layouts();
		update_post_meta( $id, '_wp_page_template', isset( $layouts[ $layout ] ) ? $layouts[ $layout ] : $layouts['full-width'] );

		$saved = self::save_content( $id, $data['content'], isset( $data['page_settings'] ) && is_array( $data['page_settings'] ) ? $data['page_settings'] : array() );
		if ( is_wp_error( $saved ) ) {
			// Only a page created by this import is removed, never the page or post the design was imported into.
			if ( ! $into ) {
				wp_delete_post( $id, true );
			}
			return $saved;
		}
		update_post_meta( $id, '_tbfe_library_page', absint( $page_id ) );

		return array(
			'id'       => $id,
			'title'    => get_the_title( $id ),
			'home'     => ! empty( $data['home'] ),
			'demo_url' => esc_url_raw( isset( $data['url'] ) ? $data['url'] : $page['preview'] ),
			'url'      => get_permalink( $id ),
			'edit_url' => Template::edit_url( $id ),
		);
	}

	/**
	 * Page or post the current user may fill with a demo page.
	 *
	 * @return \WP_Post|\WP_Error
	 */
	public static function import_target( $post_id ) {
		$post = get_post( absint( $post_id ) );
		if ( ! $post || ! in_array( $post->post_type, array( 'page', 'post' ), true ) || ! current_user_can( 'edit_post', $post->ID ) ) {
			return new \WP_Error( 'tbfe_library', __( 'You cannot import a design into this post.', 'theme-builder-for-elementor' ), array( 'status' => 403 ) );
		}
		return $post;
	}

	/**
	 * Last step of a website import.
	 *
	 * @param array $pages     Imported pages: array( array( 'id' => new page ID, 'demo_url' => URL on the demos site, 'home' => bool ) ).
	 * @param int[] $templates Imported template IDs.
	 * @param array $options   front (bool), menu (bool), name (string, site title for the menu).
	 * @return array Result: menu ID, front page ID.
	 */
	public static function finish( array $pages, array $templates, array $options ) {
		$pages     = array_values(
			array_filter(
				$pages,
				static function ( $page ) {
					return ! empty( $page['id'] ) && in_array( get_post_type( (int) $page['id'] ), array( 'page', 'post' ), true ) && get_post_meta( (int) $page['id'], '_tbfe_library_page', true );
				}
			)
		);
		$templates = array_values( array_filter( array_map( 'absint', $templates ), array( Template::class, 'is_template' ) ) );
		$result    = array(
			'menu'  => 0,
			'front' => 0,
		);

		// Links to the demo pages → links to the new pages.
		$map = array();
		foreach ( $pages as $page ) {
			$demo = isset( $page['demo_url'] ) ? esc_url_raw( $page['demo_url'] ) : '';
			if ( $demo ) {
				$map[ untrailingslashit( $demo ) ] = untrailingslashit( get_permalink( (int) $page['id'] ) );
			}
		}
		// Longest URLs first, so the home URL does not replace the start of the subpage URLs.
		uksort(
			$map,
			static function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			}
		);
		// Every other link to the demos site (categories, posts…) points to the same path on this site, like the kit
		// import of Elementor does.
		$index = Remote::index();
		$demo  = ! is_wp_error( $index ) && ! empty( $index['home'] ) ? untrailingslashit( $index['home'] ) : '';
		if ( $demo && 0 !== strpos( untrailingslashit( home_url() ), $demo ) ) {
			$map[ $demo ] = untrailingslashit( home_url() );
		}

		// Menu of the imported pages.
		if ( ! empty( $options['menu'] ) && $pages ) {
			$name    = sanitize_text_field( isset( $options['name'] ) && '' !== $options['name'] ? $options['name'] : __( 'Imported menu', 'theme-builder-for-elementor' ) );
			$menu_id = wp_create_nav_menu( wp_get_nav_menu_object( $name ) ? $name . ' ' . wp_date( 'Y-m-d H:i' ) : $name );
			if ( ! is_wp_error( $menu_id ) ) {
				foreach ( $pages as $page ) {
					$item_id = wp_update_nav_menu_item(
						$menu_id,
						0,
						array(
							'menu-item-object-id' => (int) $page['id'],
							'menu-item-object'    => 'page',
							'menu-item-type'      => 'post_type',
							'menu-item-status'    => 'publish',
						)
					);
					$entry = is_wp_error( $item_id ) ? null : self::find( 'pages', (int) get_post_meta( (int) $page['id'], '_tbfe_library_page', true ) );
					if ( $entry && ! is_wp_error( $entry ) ) {
						/**
						 * Templates that a menu item of the imported website brings along (Theme Builder PRO imports the
						 * mega menu of the item). Their links to the demos site are updated like the other templates.
						 *
						 * @param int[] $added   Template IDs.
						 * @param int   $item_id New menu item.
						 * @param array $entry   The page in the library list (with 'mega' when its item opens one).
						 */
						$added     = (array) apply_filters( 'tbfe/library/menu_item_templates', array(), (int) $item_id, $entry );
						$templates = array_merge( $templates, array_filter( array_map( 'absint', $added ) ) );
					}
				}
				$result['menu'] = (int) $menu_id;
			}
		}

		$posts = array_map( 'absint', array_merge( wp_list_pluck( $pages, 'id' ), $templates ) );
		$next  = 0;
		while ( $next < count( $posts ) ) {
			$post_id  = $posts[ $next++ ];
			$document = \Elementor\Plugin::$instance->documents->get( $post_id, false );
			if ( ! $document ) {
				continue;
			}
			$elements = $document->get_elements_data();
			// Templates imported with the content (slides of a Slider, Section templates, Post Cards) get their links
			// updated too.
			foreach ( References::ids( $elements ) as $ref ) {
				if ( ! in_array( $ref, $posts, true ) && Template::is_template( $ref ) && get_post_meta( $ref, '_tbfe_library_item', true ) ) {
					$posts[] = $ref;
				}
			}
			$changed = false;
			$elements = self::walk(
				$elements,
				static function ( $element ) use ( $map, $result, &$changed ) {
					// Navigation menus of the demo point to the new menu.
					if ( $result['menu'] && isset( $element['widgetType'] ) && 'tbfe-nav-menu' === $element['widgetType'] ) {
						$element['settings']['menu'] = (string) $result['menu'];
						$changed                     = true;
					}
					if ( $map && ! empty( $element['settings'] ) ) {
						$json = wp_json_encode( $element['settings'] );
						$new  = $json;
						foreach ( $map as $from => $to ) {
							// The URLs as they are written inside JSON (escaped slashes), also inside HTML of text widgets.
							$new = str_replace( trim( wp_json_encode( $from ), '"' ), trim( wp_json_encode( $to ), '"' ), $new );
						}
						if ( $new !== $json ) {
							$element['settings'] = json_decode( $new, true );
							$changed             = true;
						}
					}
					return $element;
				}
			);
			if ( $changed ) {
				$document->save( array( 'elements' => $elements ) );
			}
		}

		// The home page of the demo as the front page.
		if ( ! empty( $options['front'] ) ) {
			foreach ( $pages as $page ) {
				if ( ! empty( $page['home'] ) ) {
					wp_update_post(
						array(
							'ID'          => (int) $page['id'],
							'post_status' => 'publish',
						)
					);
					update_option( 'show_on_front', 'page' );
					update_option( 'page_on_front', (int) $page['id'] );
					$result['front'] = (int) $page['id'];
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * Content of a template or a demo page, ready to be inserted into the page open in the Elementor editor: new
	 * element IDs, images copied into the media library, links to the demos site pointing to this site.
	 *
	 * @param string $kind item (a template) or page (a page of a website demo).
	 * @param int    $id   ID on the demos site.
	 * @return array|\WP_Error array( 'title' => string, 'content' => array )
	 */
	public static function content( $kind, $id ) {
		$entry = self::find( 'page' === $kind ? 'pages' : 'items', $id );
		if ( is_wp_error( $entry ) ) {
			return $entry;
		}
		$allowed = self::check_pro( $entry );
		if ( is_wp_error( $allowed ) ) {
			return $allowed;
		}
		$data = Remote::file( $entry['file'] );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		$widgets = self::check_widgets( isset( $data['content'] ) && is_array( $data['content'] ) ? $data['content'] : array() );
		if ( is_wp_error( $widgets ) ) {
			return $widgets;
		}
		if ( ! isset( $data['content'] ) || ! is_array( $data['content'] ) ) {
			return new \WP_Error( 'tbfe_library', __( 'This demo cannot be inserted.', 'theme-builder-for-elementor' ) );
		}
		$importer = new Elementor_Importer();
		$content  = $importer->prepare( References::content( Colors::content( $data['content'] ) ) );

		$index = Remote::index();
		$demo  = ! is_wp_error( $index ) && ! empty( $index['home'] ) ? untrailingslashit( $index['home'] ) : '';
		$home  = untrailingslashit( home_url() );
		if ( $demo && 0 !== strpos( $home, $demo ) ) {
			$from    = trim( wp_json_encode( $demo ), '"' );
			$to      = trim( wp_json_encode( $home ), '"' );
			$content = self::walk(
				$content,
				static function ( $element ) use ( $from, $to ) {
					if ( ! empty( $element['settings'] ) ) {
						$json = wp_json_encode( $element['settings'] );
						$new  = str_replace( $from, $to, $json );
						if ( $new !== $json ) {
							$element['settings'] = json_decode( $new, true );
						}
					}
					return $element;
				}
			);
		}
		$title = self::text( $data, 'title' );
		return array(
			'title'   => '' !== $title ? $title : self::text( $entry, 'title' ),
			'content' => array_values( $content ),
		);
	}

	/**
	 * Calls $callback for every element (recursively) and returns the changed elements.
	 */
	private static function walk( array $elements, callable $callback ) {
		foreach ( $elements as $i => $element ) {
			$element = $callback( $element );
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$element['elements'] = self::walk( $element['elements'], $callback );
			}
			$elements[ $i ] = $element;
		}
		return $elements;
	}
}
