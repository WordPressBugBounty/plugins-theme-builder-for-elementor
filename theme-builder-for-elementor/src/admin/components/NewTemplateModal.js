import { __, sprintf } from '@wordpress/i18n';
import { Modal } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { api } from '../api';
import { useStore } from '../store';
import TypeArt from './TypeArt';
import Icon from './Icon';
import { ProBadge, ProBanner } from './Pro';

/**
 * Quick display choices when a template is created (the full editor of the conditions is in the template list).
 */
const quickChoices = ( type ) => {
	const later = { value: '', label: __( 'Decide later', 'theme-builder-for-elementor' ), rules: [] };
	const rule = ( name ) => [ { type: 'include', name, ids: [] } ];
	switch ( type ) {
		case 'header':
		case 'footer':
			return [ { value: 'site', label: __( 'Entire site', 'theme-builder-for-elementor' ), rules: rule( 'entire_site' ) }, later ];
		case 'single':
			return [
				{ value: 'posts', label: __( 'All posts', 'theme-builder-for-elementor' ), rules: rule( 'single_post' ) },
				{ value: 'pages', label: __( 'All pages', 'theme-builder-for-elementor' ), rules: rule( 'single_page' ) },
				later,
			];
		case 'archive':
			return [
				{ value: 'all', label: __( 'All archives', 'theme-builder-for-elementor' ), rules: rule( 'archive' ) },
				{ value: 'blog', label: __( 'Blog page only', 'theme-builder-for-elementor' ), rules: rule( 'blog' ) },
				later,
			];
		case 'search':
			return [ { value: 'on', label: __( 'Use for the search results', 'theme-builder-for-elementor' ), rules: rule( 'entire_site' ) }, later ];
		case 'error-404':
			return [ { value: 'on', label: __( 'Use as the 404 page', 'theme-builder-for-elementor' ), rules: rule( 'entire_site' ) }, later ];
		case 'popup':
			return [ { value: 'site', label: __( 'Entire site', 'theme-builder-for-elementor' ), rules: rule( 'entire_site' ) }, later ];
		case 'product':
			return [ { value: 'all', label: __( 'All products', 'theme-builder-for-elementor' ), rules: rule( 'product_all' ) }, later ];
		case 'product-archive':
			return [
				{ value: 'all', label: __( 'All product archives', 'theme-builder-for-elementor' ), rules: rule( 'shop_all' ) },
				{ value: 'shop', label: __( 'Shop page only', 'theme-builder-for-elementor' ), rules: rule( 'shop_page' ) },
				later,
			];
		case 'cart':
			return [ { value: 'on', label: __( 'Use as the cart page', 'theme-builder-for-elementor' ), rules: rule( 'entire_site' ) }, later ];
		case 'checkout':
			return [ { value: 'on', label: __( 'Use as the checkout page', 'theme-builder-for-elementor' ), rules: rule( 'entire_site' ) }, later ];
		case 'my-account':
			return [ { value: 'on', label: __( 'Use as the account page', 'theme-builder-for-elementor' ), rules: rule( 'entire_site' ) }, later ];
		case 'thankyou':
			return [ { value: 'on', label: __( 'Use as the order received page', 'theme-builder-for-elementor' ), rules: rule( 'entire_site' ) }, later ];
		default:
			return [];
	}
};

export default function NewTemplateModal( { initialType = '', onClose, onBrowse } ) {
	const { data, putTemplate, toast } = useStore();
	const [ type, setType ] = useState( initialType || 'header' );
	const [ title, setTitle ] = useState( '' );
	const [ choice, setChoice ] = useState( () => quickChoices( initialType || 'header' )[ 0 ]?.value ?? '' );
	const [ busy, setBusy ] = useState( false );

	const choices = quickChoices( type );
	const current = data.types.find( ( t ) => t.slug === type );
	const locked = !! current?.locked;

	const pick = ( slug ) => {
		setType( slug );
		setChoice( quickChoices( slug )[ 0 ]?.value ?? '' );
	};

	const create = ( event ) => {
		event.preventDefault();
		setBusy( true );
		const rules = choices.find( ( c ) => c.value === choice )?.rules || [];
		api.createTemplate( { type, title, conditions: rules } )
			.then( ( template ) => {
				putTemplate( template );
				window.location.href = template.edit_url;
			} )
			.catch( ( e ) => {
				setBusy( false );
				toast( e.message, 'error' );
			} );
	};

	return (
		<Modal
			title={ __( 'New template', 'theme-builder-for-elementor' ) }
			onRequestClose={ onClose }
			className="tbfe-modal tbfe-modal--new"
			size="large"
		>
			<form onSubmit={ create }>
				<p className="tbfe-modal__lead">{ __( 'What part of the site do you want to design?', 'theme-builder-for-elementor' ) }</p>
				<div className="tbfe-type-picker" role="radiogroup" aria-label={ __( 'Template type', 'theme-builder-for-elementor' ) }>
					{ data.types.map( ( t ) => (
						<button
							key={ t.slug }
							type="button"
							role="radio"
							aria-checked={ type === t.slug }
							className={ 'tbfe-type-option' + ( type === t.slug ? ' is-selected' : '' ) + ( t.locked ? ' is-locked' : '' ) }
							onClick={ () => pick( t.slug ) }
						>
							<TypeArt type={ t.slug } />
							<strong>
								{ t.label }
								{ t.pro && <ProBadge locked={ t.locked } /> }
							</strong>
							<span>{ t.description }</span>
						</button>
					) ) }
				</div>

				{ locked && <ProBanner title={ current.plural } /> }
				<div className={ 'tbfe-form-row' + ( locked ? ' is-hidden' : '' ) }>
					<label className="tbfe-field">
						<span className="tbfe-field__label">{ __( 'Name', 'theme-builder-for-elementor' ) }</span>
						<input
							type="text"
							className="tbfe-input"
							value={ title }
							placeholder={ sprintf( /* translators: %s: template type */ __( 'e.g. Main %s', 'theme-builder-for-elementor' ), current?.label.toLowerCase() ) }
							onChange={ ( e ) => setTitle( e.target.value ) }
						/>
					</label>
					{ choices.length > 0 && (
						<label className="tbfe-field">
							<span className="tbfe-field__label">{ __( 'Display on', 'theme-builder-for-elementor' ) }</span>
							<select className="tbfe-input" value={ choice } onChange={ ( e ) => setChoice( e.target.value ) }>
								{ choices.map( ( c ) => (
									<option key={ c.value } value={ c.value }>
										{ c.label }
									</option>
								) ) }
							</select>
						</label>
					) }
				</div>

				<div className="tbfe-modal__footer">
					<button type="button" className="tbfe-btn tbfe-btn--ghost" onClick={ () => onBrowse( type ) }>
						<Icon name="sparkle" size={ 16 } />
						{ __( 'Start from a demo', 'theme-builder-for-elementor' ) }
					</button>
					<button type="submit" className="tbfe-btn tbfe-btn--primary" disabled={ busy || locked }>
						{ busy ? __( 'Creating…', 'theme-builder-for-elementor' ) : __( 'Create and edit with Elementor', 'theme-builder-for-elementor' ) }
					</button>
				</div>
			</form>
		</Modal>
	);
}
