import { __ } from '@wordpress/i18n';
import { Modal, ToggleControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { api } from '../api';
import { useStore } from '../store';
import ObjectPicker from './ObjectPicker';
import Icon from './Icon';

/**
 * Editor of the display conditions (and of the archive settings) of one template.
 */
export default function ConditionsModal( { template, onClose } ) {
	const { data, putTemplate, toast } = useStore();
	const type = data.types.find( ( t ) => t.slug === template.type );
	const groups = type?.conditions || [];
	const available = data.conditions.conditions.filter( ( c ) => groups.includes( c.group ) );
	const byName = Object.fromEntries( available.map( ( c ) => [ c.name, c ] ) );

	const [ rules, setRules ] = useState( () => template.conditions.filter( ( r ) => byName[ r.name ] ) );
	const [ settings, setSettings ] = useState( template.settings );
	const [ busy, setBusy ] = useState( false );

	const isToggleOnly = 1 === groups.length && 'general' === groups[ 0 ]; // 404 and search results: on or off.
	const isSearch = 'search' === template.type;
	const sections = data.templates.filter( ( t ) => 'section' === t.type && 'publish' === t.status );

	const update = ( index, patch ) => setRules( ( list ) => list.map( ( r, i ) => ( i === index ? { ...r, ...patch } : r ) ) );
	const remove = ( index ) => setRules( ( list ) => list.filter( ( r, i ) => i !== index ) );
	const add = () => setRules( ( list ) => [ ...list, { type: 'include', name: available[ 0 ]?.name || 'entire_site', ids: [] } ] );

	const save = () => {
		setBusy( true );
		const payload = { conditions: rules };
		if ( 'archive' === template.type ) {
			payload.settings = settings;
		}
		api.updateTemplate( template.id, payload )
			.then( ( saved ) => {
				putTemplate( saved );
				toast( __( 'Display conditions saved.', 'theme-builder-for-elementor' ) );
				onClose();
			} )
			.catch( ( e ) => {
				setBusy( false );
				toast( e.message, 'error' );
			} );
	};

	return (
		<Modal title={ template.title } onRequestClose={ onClose } className="tbfe-modal tbfe-modal--conditions" size="large">
			<p className="tbfe-modal__lead">
				{ isToggleOnly
					? isSearch
						? __( 'Use this template for the results of every search.', 'theme-builder-for-elementor' )
						: __( 'Use this template for every page that was not found.', 'theme-builder-for-elementor' )
					: __( 'Where is this template used? Add the places to include, and exclude the exceptions. When several templates match, the most specific one wins.', 'theme-builder-for-elementor' ) }
			</p>

			{ isToggleOnly ? (
				<div className="tbfe-panel">
					<ToggleControl
						__nextHasNoMarginBottom
						label={ isSearch ? __( 'Use for the search results', 'theme-builder-for-elementor' ) : __( 'Use as the 404 page', 'theme-builder-for-elementor' ) }
						checked={ rules.some( ( r ) => 'entire_site' === r.name && 'include' === r.type ) }
						onChange={ ( on ) => setRules( on ? [ { type: 'include', name: 'entire_site', ids: [] } ] : [] ) }
					/>
				</div>
			) : (
				<div className="tbfe-rules">
					{ ! rules.length && (
						<div className="tbfe-rules__empty">
							<Icon name="rules" size={ 28 } />
							<p>{ __( 'No conditions yet — the template is not shown anywhere.', 'theme-builder-for-elementor' ) }</p>
						</div>
					) }
					{ rules.map( ( rule, index ) => {
						const def = byName[ rule.name ];
						return (
							<div key={ index } className={ 'tbfe-rule tbfe-rule--' + rule.type }>
								<div className="tbfe-rule__main">
									<div className="tbfe-segmented" role="group" aria-label={ __( 'Include or exclude', 'theme-builder-for-elementor' ) }>
										{ [ 'include', 'exclude' ].map( ( value ) => (
											<button
												key={ value }
												type="button"
												className={ rule.type === value ? 'is-active' : '' }
												aria-pressed={ rule.type === value }
												onClick={ () => update( index, { type: value } ) }
											>
												{ 'include' === value ? __( 'Include', 'theme-builder-for-elementor' ) : __( 'Exclude', 'theme-builder-for-elementor' ) }
											</button>
										) ) }
									</div>
									<select
										className="tbfe-input tbfe-rule__select"
										value={ rule.name }
										aria-label={ __( 'Condition', 'theme-builder-for-elementor' ) }
										onChange={ ( e ) => update( index, { name: e.target.value, ids: [] } ) }
									>
										{ groups.map( ( group ) => (
											<optgroup key={ group } label={ data.conditions.groups[ group ] }>
												{ available
													.filter( ( c ) => c.group === group )
													.map( ( c ) => (
														<option key={ c.name } value={ c.name }>
															{ c.label }
														</option>
													) ) }
											</optgroup>
										) ) }
									</select>
									<button type="button" className="tbfe-icon-btn" onClick={ () => remove( index ) } aria-label={ __( 'Remove condition', 'theme-builder-for-elementor' ) }>
										<Icon name="trash" size={ 16 } />
									</button>
								</div>
								{ def?.object && <ObjectPicker key={ rule.name } object={ def.object } ids={ rule.ids } onChange={ ( ids ) => update( index, { ids } ) } /> }
							</div>
						);
					} ) }
					<button type="button" className="tbfe-btn tbfe-btn--soft" onClick={ add }>
						<Icon name="plus" size={ 16 } />
						{ __( 'Add condition', 'theme-builder-for-elementor' ) }
					</button>
				</div>
			) }

			{ 'archive' === template.type && (
				<div className="tbfe-panel tbfe-panel--settings">
					<h3>{ __( 'Archive layout', 'theme-builder-for-elementor' ) }</h3>
					<div className="tbfe-choice-cards">
						{ [
							{ value: 'layout', title: __( 'Whole archive', 'theme-builder-for-elementor' ), text: __( 'The template is the whole page. List the posts with the Posts widget.', 'theme-builder-for-elementor' ) },
							{ value: 'loop', title: __( 'Repeat for each post', 'theme-builder-for-elementor' ), text: __( 'The template is one post; it repeats with pagination (classic mode of version 1).', 'theme-builder-for-elementor' ) },
						].map( ( option ) => (
							<button
								key={ option.value }
								type="button"
								className={ 'tbfe-choice-card' + ( settings.mode === option.value ? ' is-selected' : '' ) }
								aria-pressed={ settings.mode === option.value }
								onClick={ () => setSettings( { ...settings, mode: option.value } ) }
							>
								<strong>{ option.title }</strong>
								<span>{ option.text }</span>
							</button>
						) ) }
					</div>
					{ 'loop' === settings.mode && (
						<div className="tbfe-form-row">
							<label className="tbfe-field">
								<span className="tbfe-field__label">{ __( 'Width', 'theme-builder-for-elementor' ) }</span>
								<select className="tbfe-input" value={ settings.width } onChange={ ( e ) => setSettings( { ...settings, width: e.target.value } ) }>
									<option value="boxed">{ __( 'Boxed', 'theme-builder-for-elementor' ) }</option>
									<option value="full">{ __( 'Full width', 'theme-builder-for-elementor' ) }</option>
								</select>
							</label>
							<label className="tbfe-field">
								<span className="tbfe-field__label">{ __( 'Sidebar', 'theme-builder-for-elementor' ) }</span>
								<select className="tbfe-input" value={ settings.sidebar } onChange={ ( e ) => setSettings( { ...settings, sidebar: parseInt( e.target.value, 10 ) } ) }>
									<option value={ 0 }>{ __( 'No sidebar', 'theme-builder-for-elementor' ) }</option>
									{ sections.map( ( s ) => (
										<option key={ s.id } value={ s.id }>
											{ s.title }
										</option>
									) ) }
								</select>
							</label>
						</div>
					) }
				</div>
			) }

			<div className="tbfe-modal__footer">
				<button type="button" className="tbfe-btn tbfe-btn--ghost" onClick={ onClose }>
					{ __( 'Cancel', 'theme-builder-for-elementor' ) }
				</button>
				<button type="button" className="tbfe-btn tbfe-btn--primary" onClick={ save } disabled={ busy }>
					{ busy ? __( 'Saving…', 'theme-builder-for-elementor' ) : __( 'Save conditions', 'theme-builder-for-elementor' ) }
				</button>
			</div>
		</Modal>
	);
}
