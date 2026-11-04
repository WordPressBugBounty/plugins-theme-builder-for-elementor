import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { api } from '../api';
import { useStore } from '../store';
import Icon from './Icon';

/**
 * Installs (from WordPress.org) and activates WooCommerce, or only activates it when its files are there. The page
 * reloads afterwards: the shop parts of Theme Builder start with WooCommerce.
 *
 * Users who cannot install plugins get a link to the plugin screen.
 */
export default function InstallWoo( { size = 'sm', short = false } ) {
	const { data, toast } = useStore();
	const [ busy, setBusy ] = useState( false );
	const pro = data.pro || {};
	const config = window.tbfeAdmin || {};
	const label = pro.wooFiles ? __( 'Activate WooCommerce', 'theme-builder-for-elementor' ) : __( 'Install WooCommerce', 'theme-builder-for-elementor' );
	const className = 'tbfe-btn tbfe-btn--woo' + ( size ? ' tbfe-btn--' + size : '' );

	if ( pro.woo ) {
		return null;
	}
	if ( ! pro.canInstall ) {
		return (
			<a className={ className } href={ ( config.adminUrl || '' ) + 'plugin-install.php?s=woocommerce&tab=search&type=term' }>
				<Icon name="cart" size={ 15 } />
				{ short ? __( 'WooCommerce', 'theme-builder-for-elementor' ) : label }
			</a>
		);
	}

	const run = () => {
		setBusy( true );
		api.installPlugin( 'woocommerce' )
			.then( () => {
				toast( __( 'WooCommerce is active. Reloading…', 'theme-builder-for-elementor' ) );
				window.location.reload();
			} )
			.catch( ( e ) => {
				setBusy( false );
				toast( e.message, 'error', e.data?.url ? { href: e.data.url, label: __( 'Open Plugins', 'theme-builder-for-elementor' ) } : null );
			} );
	};

	return (
		<button type="button" className={ className } onClick={ run } disabled={ busy } title={ label }>
			<Icon name="cart" size={ 15 } />
			{ busy ? ( pro.wooFiles ? __( 'Activating…', 'theme-builder-for-elementor' ) : __( 'Installing…', 'theme-builder-for-elementor' ) ) : short ? __( 'Get WooCommerce', 'theme-builder-for-elementor' ) : label }
		</button>
	);
}

/**
 * The "Needs WooCommerce" chip of a demo.
 */
export function WooChip() {
	return (
		<span className="tbfe-woo-chip">
			<Icon name="cart" size={ 11 } />
			{ __( 'Needs WooCommerce', 'theme-builder-for-elementor' ) }
		</span>
	);
}
