import { __ } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useRoute, go } from './router';
import { useStore } from './store';
import Icon from './components/Icon';
import NewTemplateModal from './components/NewTemplateModal';
import { ProAction } from './components/Pro';
import Dashboard from './pages/Dashboard';
import Templates from './pages/Templates';
import Library from './pages/Library';
import Websites from './pages/Websites';
import SettingsPage from './pages/SettingsPage';

const config = window.tbfeAdmin || {};

const NAV = [
	{ route: '', label: __( 'Dashboard', 'theme-builder-for-elementor' ), icon: 'home' },
	{ route: 'templates', label: __( 'Templates', 'theme-builder-for-elementor' ), icon: 'layers' },
	{ route: 'websites', label: __( 'Websites Import', 'theme-builder-for-elementor' ), icon: 'download' },
	{ route: 'library', label: __( 'Template Library', 'theme-builder-for-elementor' ), icon: 'sparkle' },
	{ route: 'settings', label: __( 'Settings', 'theme-builder-for-elementor' ), icon: 'settings' },
];

function Logo() {
	return (
		<svg className="tbfe-logo" viewBox="0 0 32 32" aria-hidden="true">
			<rect x="2" y="2" width="28" height="8" rx="2.5" className="tbfe-logo__a" />
			<rect x="2" y="12" width="11" height="18" rx="2.5" className="tbfe-logo__b" />
			<rect x="15" y="12" width="15" height="8" rx="2.5" className="tbfe-logo__c" />
			<rect x="15" y="22" width="15" height="8" rx="2.5" className="tbfe-logo__b" />
		</svg>
	);
}

function Toasts() {
	const { toasts, dismissToast } = useStore();
	return (
		<div className="tbfe-toasts" role="status" aria-live="polite">
			{ toasts.map( ( t ) => (
				<div key={ t.id } className={ `tbfe-toast tbfe-toast--${ t.type }` }>
					<Icon name={ 'error' === t.type ? 'warning' : 'check' } />
					<span className="tbfe-toast__text">{ t.message }</span>
					{ t.action && (
						<a className="tbfe-toast__action" href={ t.action.href }>
							{ t.action.label }
						</a>
					) }
					<button type="button" className="tbfe-toast__close" onClick={ () => dismissToast( t.id ) } aria-label={ __( 'Close', 'theme-builder-for-elementor' ) }>
						<Icon name="close" size={ 14 } />
					</button>
				</div>
			) ) }
		</div>
	);
}

export default function App() {
	const route = useRoute();
	const { data, error, reload } = useStore();
	const [ creating, setCreating ] = useState( null );

	const page = route[ 0 ] || '';
	const openNew = ( type = '' ) => setCreating( { type } );

	let content;
	if ( error ) {
		content = (
			<div className="tbfe-empty">
				<Icon name="warning" size={ 32 } />
				<h2>{ __( 'The administration could not load.', 'theme-builder-for-elementor' ) }</h2>
				<p>{ error }</p>
				<button type="button" className="tbfe-btn tbfe-btn--primary" onClick={ reload }>
					{ __( 'Try again', 'theme-builder-for-elementor' ) }
				</button>
			</div>
		);
	} else if ( ! data ) {
		content = (
			<div className="tbfe-loading">
				<Spinner />
			</div>
		);
	} else if ( 'templates' === page ) {
		content = <Templates filter={ route[ 1 ] || '' } onNew={ openNew } />;
	} else if ( 'websites' === page ) {
		content = <Websites siteId={ route[ 1 ] || '' } />;
	} else if ( 'library' === page ) {
		content = <Library filter={ route[ 1 ] || '' } />;
	} else if ( 'settings' === page ) {
		content = <SettingsPage section={ route[ 1 ] || '' } />;
	} else {
		content = <Dashboard onNew={ openNew } />;
	}

	return (
		<div className="tbfe-shell">
			<header className="tbfe-topbar">
				<div className="tbfe-topbar__brand">
					<Logo />
					<span className="tbfe-topbar__name">{ __( 'Theme Builder', 'theme-builder-for-elementor' ) }</span>
					<span className="tbfe-chip">{ config.version }</span>
				</div>
				<nav className="tbfe-topbar__nav" aria-label={ __( 'Theme Builder', 'theme-builder-for-elementor' ) }>
					{ NAV.map( ( item ) => (
						<a
							key={ item.route }
							href={ '#/' + item.route }
							className={ 'tbfe-topbar__link' + ( page === item.route ? ' is-active' : '' ) }
							aria-current={ page === item.route ? 'page' : undefined }
						>
							<Icon name={ item.icon } size={ 16 } />
							{ item.label }
						</a>
					) ) }
				</nav>
				<div className="tbfe-topbar__actions">
					{ data && data.pro.active && (
						<a className="tbfe-btn tbfe-btn--ghost tbfe-pro-active" href={ data.pro.license } title={ __( 'License', 'theme-builder-for-elementor' ) }>
							<Icon name="key" size={ 16 } />
							<span className="tbfe-hide-sm">PRO</span>
						</a>
					) }
					{ data && ! data.pro.active && data.pro.action && (
						<span className="tbfe-hide-sm">
							<ProAction size="" />
						</span>
					) }
					<a className="tbfe-btn tbfe-btn--ghost" href={ config.docsUrl } target="_blank" rel="noreferrer">
						<Icon name="book" size={ 16 } />
						<span className="tbfe-hide-sm">{ __( 'Docs', 'theme-builder-for-elementor' ) }</span>
					</a>
					<button type="button" className="tbfe-btn tbfe-btn--primary" onClick={ () => openNew() } disabled={ ! data }>
						<Icon name="plus" size={ 16 } />
						{ __( 'New template', 'theme-builder-for-elementor' ) }
					</button>
				</div>
			</header>

			<main className="tbfe-main">{ content }</main>

			{ creating && data && (
				<NewTemplateModal
					initialType={ creating.type }
					onClose={ () => setCreating( null ) }
					onBrowse={ ( type ) => {
						setCreating( null );
						go( 'library' + ( type ? '/' + type : '' ) );
					} }
				/>
			) }
			<Toasts />
		</div>
	);
}
