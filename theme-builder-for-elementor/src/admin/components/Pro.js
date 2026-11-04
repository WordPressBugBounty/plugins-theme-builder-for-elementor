import { __ } from '@wordpress/i18n';
import { useStore } from '../store';
import Icon from './Icon';
import InstallWoo from './InstallWoo';

/**
 * Theme Builder PRO in the administration: the badge, the lock button and the banner of locked parts.
 *
 * The button depends on the state of PRO: not installed → "Get Theme Builder PRO" (pricing with the FastSpring
 * checkout), installed without an active license → "Activate license", expired → "Renew license".
 */
export function ProBadge( { locked = false } ) {
	return (
		<span className={ 'tbfe-pro-badge' + ( locked ? ' is-locked' : '' ) } title={ locked ? __( 'Locked – needs Theme Builder PRO', 'theme-builder-for-elementor' ) : undefined }>
			{ locked && <Icon name="lock" size={ 11 } /> }
			PRO
		</span>
	);
}

/**
 * The action of the lock, or a short note when PRO is active but WooCommerce is missing. "short" is for small
 * places (the cards of the dashboard and the library): Get PRO, Activate, Renew.
 */
export function ProAction( { size = 'sm', label = '', short = false } ) {
	const { data } = useStore();
	const action = data.pro.action;
	if ( ! action ) {
		return data.pro.woo ? null : <InstallWoo size={ size } short={ short } />;
	}
	const external = 'activate' !== action.kind;
	const shortLabels = {
		buy: __( 'Get PRO', 'theme-builder-for-elementor' ),
		activate: __( 'Activate', 'theme-builder-for-elementor' ),
		renew: __( 'Renew', 'theme-builder-for-elementor' ),
	};
	return (
		<a
			className={ 'tbfe-btn tbfe-btn--pro' + ( size ? ' tbfe-btn--' + size : '' ) }
			href={ action.url }
			title={ short ? action.label : undefined }
			target={ external ? '_blank' : undefined }
			rel={ external ? 'noopener noreferrer' : undefined }
		>
			<Icon name={ 'buy' === action.kind ? 'sparkle' : 'lock' } size={ 'lg' === size ? 18 : 15 } />
			{ label || ( short && shortLabels[ action.kind ] ) || action.label }
		</a>
	);
}

/**
 * Banner above a locked part: what PRO gives and the action.
 */
export function ProBanner( { title } ) {
	const { data } = useStore();
	const action = data.pro.action;
	let note = action ? action.note : '';
	if ( ! action && ! data.pro.woo ) {
		note = __( 'The WooCommerce templates need the WooCommerce plugin. Install and activate WooCommerce to use them.', 'theme-builder-for-elementor' );
	}
	if ( ! note ) {
		return null;
	}
	return (
		<div className="tbfe-pro-banner">
			<span className="tbfe-pro-banner__icon">
				<Icon name="lock" size={ 20 } />
			</span>
			<div className="tbfe-pro-banner__text">
				<strong>{ title || __( 'Theme Builder PRO', 'theme-builder-for-elementor' ) }</strong>
				<p>{ note }</p>
			</div>
			{ action ? <ProAction size="" /> : <InstallWoo size="" /> }
		</div>
	);
}
