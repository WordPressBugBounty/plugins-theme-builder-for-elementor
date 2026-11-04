<?php
/**
 * Theme Builder For Elementor PRO: whether it is installed and licensed, and what the free plugin shows without it.
 *
 * - Without PRO the WooCommerce template types, the PRO widgets and the PRO demos are listed, but locked: the types
 *   cannot be created, the widgets are placeholders (nothing on the site) and the demos cannot be imported. Each lock
 *   offers "Get PRO".
 * - With PRO but without an active license (never activated, inactive or expired) the same parts stay locked with
 *   "Activate license" or "Renew license". What was built before keeps working on the site.
 *
 * PRO reports its license with the filter tbfe/license_state and its links with tbfe/pro_config.
 *
 * @package TBFE
 */

namespace TBFE;

defined( 'ABSPATH' ) || exit;

class Pro {

	/** Main file of the PRO plugin. */
	const PLUGIN = 'theme-builder-for-elementor-pro/theme-builder-for-elementor-pro.php';

	/**
	 * PRO is installed and running.
	 */
	public static function installed() {
		return defined( 'TBFEP_VERSION' );
	}

	/**
	 * none (PRO not installed), inactive (no key, or the key is not active), expired or active.
	 */
	public static function state() {
		if ( ! self::installed() ) {
			return 'none';
		}
		/**
		 * Filters the license state of PRO.
		 *
		 * @param string $state inactive, expired or active.
		 */
		$state = (string) apply_filters( 'tbfe/license_state', 'inactive' );
		return in_array( $state, array( 'inactive', 'expired', 'active' ), true ) ? $state : 'inactive';
	}

	public static function is_active() {
		return 'active' === self::state();
	}

	/**
	 * Links of the locks.
	 */
	public static function config() {
		/**
		 * Filters the links of PRO: buy_url (pricing with the FastSpring checkout), activate_url (License page of PRO),
		 * renew_url (license page of the order in the customer account).
		 *
		 * @param array $config Links.
		 */
		return (array) apply_filters(
			'tbfe/pro_config',
			array(
				'buy_url'      => 'https://blocks-wp.com/theme-builder-for-elementor-pro/#pricing',
				'activate_url' => admin_url( 'admin.php?page=tbfe-license' ),
				'renew_url'    => 'https://blocks-wp.com/my-account/',
			)
		);
	}

	/**
	 * What the lock offers in the current state: kind (buy, activate, renew), label, url, note.
	 */
	public static function action() {
		$config = self::config();
		switch ( self::state() ) {
			case 'active':
				return null;
			case 'expired':
				return array(
					'kind'  => 'renew',
					'label' => __( 'Renew license', 'theme-builder-for-elementor' ),
					'url'   => esc_url_raw( $config['renew_url'] ),
					'note'  => __( 'Your PRO license has expired. What you built keeps working; renew it to create and edit PRO templates again.', 'theme-builder-for-elementor' ),
				);
			case 'inactive':
				return array(
					'kind'  => 'activate',
					'label' => __( 'Activate license', 'theme-builder-for-elementor' ),
					'url'   => esc_url_raw( $config['activate_url'] ),
					'note'  => __( 'Theme Builder PRO is installed. Activate your license key to unlock it.', 'theme-builder-for-elementor' ),
				);
		}
		return array(
			'kind'  => 'buy',
			'label' => __( 'Get Theme Builder PRO', 'theme-builder-for-elementor' ),
			'url'   => esc_url_raw( $config['buy_url'] ),
			'note'  => __( 'Build the WooCommerce shop with Elementor: product pages, shop archives, cart, checkout and the customer account.', 'theme-builder-for-elementor' ),
		);
	}

	/**
	 * Data for the administration.
	 */
	public static function for_admin() {
		return array(
			'installed' => self::installed(),
			'state'     => self::state(),
			'active'    => self::is_active(),
			'action'    => self::action(),
			'license'   => self::installed() ? esc_url_raw( self::config()['activate_url'] ) : '',
			'buy'       => esc_url_raw( self::config()['buy_url'] ),
			'woo'       => class_exists( 'WooCommerce' ),
			// WooCommerce can be installed or activated from the administration (Admin\Plugin_Installer).
			'wooFiles'  => file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ),
			'canInstall' => Admin\Plugin_Installer::can_install(),
		);
	}

	/**
	 * The WooCommerce template types of PRO (the same texts as in PRO). Without PRO they are listed as locked types.
	 */
	public static function teaser_types() {
		return array(
			'mega-menu'       => array(
				'label'       => __( 'Mega Menu', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Mega Menus', 'theme-builder-for-elementor' ),
				'description' => __( 'A big drop-down of the menu: columns, images, products.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The panel that opens under an item of the menu: columns of links, images, banners, products or a search. Design it here, then choose it for a top-level item in Appearance › Menus; the Nav Menu widget opens it on hover or click, and in the mobile menu as an accordion.', 'theme-builder-for-elementor' ),
				'location'    => '',
				'conditions'  => array(),
				'pro'         => true,
				'group'       => 'menu',
			),
			'popup'           => array(
				'label'       => __( 'Popup', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Popups', 'theme-builder-for-elementor' ),
				'description' => __( 'A window over the page: newsletter, offer, notice.', 'theme-builder-for-elementor' ),
				'help'        => __( 'A window over the page: a newsletter sign-up, a discount, a cookie or age notice, a login form. Choose when it opens (after a delay, on scroll, when the visitor is leaving, or with a click on a link), how often, and on which pages.', 'theme-builder-for-elementor' ),
				'location'    => 'popup',
				'conditions'  => array( 'general', 'singular', 'archive' ),
				'pro'         => true,
				'group'       => 'popup',
			),
			'product'         => array(
				'label'       => __( 'Single Product', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Single Products', 'theme-builder-for-elementor' ),
				'description' => __( 'The product page: gallery, price, add to cart, tabs.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The page of one product: the gallery, title, price, the add to cart button, the description tabs, reviews and related products. Use it for all products or the products of chosen categories.', 'theme-builder-for-elementor' ),
				'location'    => 'single',
				'conditions'  => array( 'product' ),
				'pro'         => true,
				'group'       => 'woocommerce',
			),
			'product-archive' => array(
				'label'       => __( 'Product Archive', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Product Archives', 'theme-builder-for-elementor' ),
				'description' => __( 'The shop, product categories, tags and product search.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The shop page and the product categories, tags and product search results. Its Products widget lists the products of the archive with sorting, the result count and pagination.', 'theme-builder-for-elementor' ),
				'location'    => 'archive',
				'conditions'  => array( 'shop' ),
				'pro'         => true,
				'group'       => 'woocommerce',
			),
			'cart'            => array(
				'label'       => __( 'Cart', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Cart', 'theme-builder-for-elementor' ),
				'description' => __( 'The cart page: products, totals, cross-sells.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The cart page of WooCommerce: the cart table, the totals with the checkout button and the cross-sells, placed and styled as you like. Switch it on with “Use as the cart page”.', 'theme-builder-for-elementor' ),
				'location'    => 'cart',
				'conditions'  => array( 'general' ),
				'pro'         => true,
				'group'       => 'woocommerce',
			),
			'checkout'        => array(
				'label'       => __( 'Checkout', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Checkout', 'theme-builder-for-elementor' ),
				'description' => __( 'The checkout page: billing, shipping, payment.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The checkout page of WooCommerce: the billing and shipping fields, the order review and the payment methods, in one or two columns with your styles. Switch it on with “Use as the checkout page”.', 'theme-builder-for-elementor' ),
				'location'    => 'checkout',
				'conditions'  => array( 'general' ),
				'pro'         => true,
				'group'       => 'woocommerce',
			),
			'my-account'      => array(
				'label'       => __( 'My Account', 'theme-builder-for-elementor' ),
				'plural'      => __( 'My Account', 'theme-builder-for-elementor' ),
				'description' => __( 'The customer account: orders, addresses, login.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The account page of WooCommerce: the login and registration forms, and for customers their dashboard, orders, downloads and addresses. Switch it on with “Use as the account page”.', 'theme-builder-for-elementor' ),
				'location'    => 'my-account',
				'conditions'  => array( 'general' ),
				'pro'         => true,
				'group'       => 'woocommerce',
			),
			'thankyou'        => array(
				'label'       => __( 'Order Received', 'theme-builder-for-elementor' ),
				'plural'      => __( 'Order Received', 'theme-builder-for-elementor' ),
				'description' => __( 'The thank you page after an order.', 'theme-builder-for-elementor' ),
				'help'        => __( 'The page the customer sees after the order: the thank you message, the order overview and details, and anything else you add – a coupon, recommended products, social links. Switch it on with “Use as the order received page”.', 'theme-builder-for-elementor' ),
				'location'    => 'thankyou',
				'conditions'  => array( 'general' ),
				'pro'         => true,
				'group'       => 'woocommerce',
			),
		);
	}

	/**
	 * The WooCommerce types of PRO (without the other PRO types like Popup).
	 */
	public static function woo_types() {
		return wp_list_filter( self::teaser_types(), array( 'group' => 'woocommerce' ) );
	}

	/**
	 * Is the type a PRO type (registered by PRO or known as a teaser)?
	 */
	public static function is_pro_type( $type ) {
		$registered = Templates\Types::get( $type );
		if ( $registered ) {
			return ! empty( $registered['pro'] );
		}
		return isset( self::teaser_types()[ $type ] );
	}

	/**
	 * Can templates of this type be created and imported now?
	 */
	public static function type_unlocked( $type ) {
		return ! self::is_pro_type( $type ) || ( self::is_active() && Templates\Types::exists( $type ) );
	}

	/**
	 * Error of a locked PRO action (REST).
	 */
	public static function locked_error() {
		$action = self::action();
		return new \WP_Error(
			'tbfe_pro_locked',
			$action ? $action['note'] : __( 'This needs Theme Builder PRO.', 'theme-builder-for-elementor' ),
			array(
				'status' => 403,
				'action' => $action,
			)
		);
	}

	/**
	 * The PRO widgets: name, title, Elementor icon, what it does. Without PRO they are locked placeholders in the
	 * Elementor panel. Keep the names in step with the widgets of PRO.
	 */
	public static function teaser_widgets() {
		$widgets = array(
			array( 'tbfe-product-title', __( 'Product Title', 'theme-builder-for-elementor' ), 'eicon-product-title', __( 'The name of the product.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-images', __( 'Product Images', 'theme-builder-for-elementor' ), 'eicon-product-images', __( 'The product gallery with zoom, lightbox and thumbnails.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-price', __( 'Product Price', 'theme-builder-for-elementor' ), 'eicon-product-price', __( 'The price with the sale price.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-add-to-cart', __( 'Add To Cart', 'theme-builder-for-elementor' ), 'eicon-product-add-to-cart', __( 'Quantity, variations and the add to cart button.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-rating', __( 'Product Rating', 'theme-builder-for-elementor' ), 'eicon-product-rating', __( 'The star rating and the number of reviews.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-stock', __( 'Product Stock', 'theme-builder-for-elementor' ), 'eicon-product-stock', __( 'In stock, out of stock or on backorder.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-short-description', __( 'Short Description', 'theme-builder-for-elementor' ), 'eicon-product-description', __( 'The short description of the product.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-content', __( 'Product Description', 'theme-builder-for-elementor' ), 'eicon-product-description', __( 'The full description of the product.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-meta', __( 'Product Meta', 'theme-builder-for-elementor' ), 'eicon-product-meta', __( 'SKU, categories and tags.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-tabs', __( 'Product Data Tabs', 'theme-builder-for-elementor' ), 'eicon-product-tabs', __( 'Description, additional information and reviews in tabs.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-attributes', __( 'Additional Information', 'theme-builder-for-elementor' ), 'eicon-product-info', __( 'The weight, dimensions and attributes.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-related', __( 'Related Products', 'theme-builder-for-elementor' ), 'eicon-product-related', __( 'Products from the same categories and tags.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-upsells', __( 'Upsells', 'theme-builder-for-elementor' ), 'eicon-product-upsell', __( 'The upsells of the product.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-woo-breadcrumbs', __( 'Shop Breadcrumbs', 'theme-builder-for-elementor' ), 'eicon-product-breadcrumbs', __( 'The breadcrumbs of WooCommerce.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-products', __( 'Products', 'theme-builder-for-elementor' ), 'eicon-products', __( 'A grid of products: the archive, the latest, on sale or from chosen categories.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-products-carousel', __( 'Products Carousel', 'theme-builder-for-elementor' ), 'eicon-posts-carousel', __( 'Products in a carousel: latest, on sale, featured, best selling, from categories or chosen ones.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-category-carousel', __( 'Category Carousel', 'theme-builder-for-elementor' ), 'eicon-carousel', __( 'Product categories with their images in a carousel.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-shop-description', __( 'Shop Description', 'theme-builder-for-elementor' ), 'eicon-product-description', __( 'The description of the shop page or the category.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-cart', __( 'Cart', 'theme-builder-for-elementor' ), 'eicon-cart', __( 'The cart table with coupons and the totals.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-cart-totals', __( 'Cart Totals', 'theme-builder-for-elementor' ), 'eicon-cart-medium', __( 'Subtotal, shipping, total and the checkout button.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-cross-sells', __( 'Cross-sells', 'theme-builder-for-elementor' ), 'eicon-product-related', __( 'Products recommended for the cart.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-checkout', __( 'Checkout', 'theme-builder-for-elementor' ), 'eicon-checkout', __( 'The checkout form in one or two columns.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-my-account', __( 'My Account', 'theme-builder-for-elementor' ), 'eicon-my-account', __( 'Login, registration and the customer account.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-order-received', __( 'Order Details', 'theme-builder-for-elementor' ), 'eicon-purchase-summary', __( 'The thank you message and the details of the order.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-woo-notices', __( 'Store Notices', 'theme-builder-for-elementor' ), 'eicon-info-circle-o', __( 'The messages of the shop: added to cart, coupon applied, errors.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-mini-cart', __( 'Mini Cart', 'theme-builder-for-elementor' ), 'eicon-cart-solid', __( 'The cart icon with the count and a dropdown for the header.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-wishlist-button', __( 'Wishlist Button', 'theme-builder-for-elementor' ), 'eicon-heart-o', __( 'Saves the product to the wishlist of the visitor.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-wishlist', __( 'Wishlist', 'theme-builder-for-elementor' ), 'eicon-heart', __( 'The saved products with the price, stock and add to cart.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-wishlist-counter', __( 'Wishlist Counter', 'theme-builder-for-elementor' ), 'eicon-heart', __( 'The wishlist icon with the number of products, for the header.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-compare-button', __( 'Compare Button', 'theme-builder-for-elementor' ), 'eicon-exchange', __( 'Adds the product to the comparison.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-compare-table', __( 'Compare Table', 'theme-builder-for-elementor' ), 'eicon-table', __( 'The compared products side by side with their prices and attributes.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-compare-counter', __( 'Compare Counter', 'theme-builder-for-elementor' ), 'eicon-exchange', __( 'The compare icon with the number of products, for the header.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-quick-view-button', __( 'Quick View Button', 'theme-builder-for-elementor' ), 'eicon-preview-medium', __( 'Opens the product in a window without leaving the page.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-search', __( 'Product Search', 'theme-builder-for-elementor' ), 'eicon-site-search', __( 'Product search with live results while typing.', 'theme-builder-for-elementor' ) ),
			array( 'tbfe-product-filter', __( 'Product Filter', 'theme-builder-for-elementor' ), 'eicon-filter', __( 'AJAX filters: categories, price, attributes, rating, on sale, in stock.', 'theme-builder-for-elementor' ) ),
		);
		return array_map(
			static function ( $w ) {
				return array(
					'name'        => $w[0],
					'title'       => $w[1],
					'icon'        => $w[2],
					'description' => $w[3],
				);
			},
			$widgets
		);
	}
}
