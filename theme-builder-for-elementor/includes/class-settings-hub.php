<?php
/**
 * The pages of the Settings screen besides General and Tools: one page per feature, listed in its left menu.
 *
 * A section: id, label, icon, description, pro, locked, fields, values (and features for a locked teaser). PRO adds
 * its sections with the filter tbfe/settings_sections and saves them with tbfe/save_settings_section. Without PRO its
 * sections are listed locked, with what they offer.
 *
 * Field: id, type (toggle, select, number, text, textarea, page), label, help, options (select, page), min, max.
 *
 * @package TBFE
 */

namespace TBFE;

defined( 'ABSPATH' ) || exit;

class Settings_Hub {

	/**
	 * The PRO sections as locked teasers.
	 */
	private static function teasers() {
		$teasers = array(
			'wishlist'        => array( __( 'Wishlist', 'theme-builder-for-elementor' ), 'heart', array( __( 'Wishlist page, buttons and the header icon', 'theme-builder-for-elementor' ), __( 'Buttons on every product card of the shop', 'theme-builder-for-elementor' ), __( 'For guests or only for customers with an account', 'theme-builder-for-elementor' ) ) ),
			'compare'         => array( __( 'Compare', 'theme-builder-for-elementor' ), 'compare', array( __( 'Compare page with the product table', 'theme-builder-for-elementor' ), __( 'Up to 4 products side by side', 'theme-builder-for-elementor' ) ) ),
			'quick-view'      => array( __( 'Quick View', 'theme-builder-for-elementor' ), 'eye', array( __( 'The product in a window: gallery, variations, add to cart', 'theme-builder-for-elementor' ) ) ),
			'search'          => array( __( 'Product Search', 'theme-builder-for-elementor' ), 'search', array( __( 'Live results while typing, by name and SKU', 'theme-builder-for-elementor' ) ) ),
			'filters'         => array( __( 'Product Filters', 'theme-builder-for-elementor' ), 'filter', array( __( 'AJAX filters by category, price, attributes, rating, sale and stock', 'theme-builder-for-elementor' ) ) ),
			'swatches'        => array( __( 'Variation Swatches', 'theme-builder-for-elementor' ), 'swatch', array( __( 'Colors, images and labels instead of the dropdowns of variable products', 'theme-builder-for-elementor' ), __( 'Swatches on the product cards', 'theme-builder-for-elementor' ) ) ),
		);
		$sections = array();
		foreach ( $teasers as $id => $teaser ) {
			$sections[ $id ] = array(
				'id'          => $id,
				'label'       => $teaser[0],
				'icon'        => $teaser[1],
				'description' => '',
				'pro'         => true,
				'locked'      => true,
				'features'    => $teaser[2],
				'fields'      => array(),
				'values'      => new \stdClass(),
			);
		}
		return $sections;
	}

	/**
	 * All sections, in the order of the menu.
	 */
	public static function sections() {
		/**
		 * Filters the pages of the Settings screen.
		 *
		 * @param array $sections Sections keyed by id.
		 */
		$sections = (array) apply_filters( 'tbfe/settings_sections', self::teasers() );
		foreach ( $sections as $id => $section ) {
			$sections[ $id ] = array_merge(
				array(
					'id'          => $id,
					'icon'        => 'settings',
					'description' => '',
					'pro'         => false,
					'locked'      => false,
					'features'    => array(),
					'fields'      => array(),
					'values'      => new \stdClass(),
				),
				$section
			);
		}
		return array_values( $sections );
	}

	/**
	 * Saves the values of a section.
	 *
	 * @return array|\WP_Error The saved values.
	 */
	public static function save( $id, array $values ) {
		foreach ( self::sections() as $section ) {
			if ( $section['id'] !== $id ) {
				continue;
			}
			if ( $section['locked'] ) {
				return Pro::locked_error();
			}
			/**
			 * Saves the values of a settings section and returns them (or a WP_Error).
			 *
			 * @param array|\WP_Error|null $saved  Saved values.
			 * @param string               $id     Section.
			 * @param array                $values Values from the form.
			 */
			$saved = apply_filters( 'tbfe/save_settings_section', null, $id, $values );
			if ( null === $saved ) {
				break;
			}
			return $saved;
		}
		return new \WP_Error( 'tbfe_settings', __( 'Unknown settings page.', 'theme-builder-for-elementor' ), array( 'status' => 404 ) );
	}
}
