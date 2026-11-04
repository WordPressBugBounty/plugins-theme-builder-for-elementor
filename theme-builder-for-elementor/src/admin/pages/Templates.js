import { __ } from '@wordpress/i18n';
import { Modal } from '@wordpress/components';
import { useMemo, useState } from '@wordpress/element';
import { api } from '../api';
import { useStore } from '../store';
import TemplateRow from '../components/TemplateRow';
import ConditionsModal from '../components/ConditionsModal';
import TypeArt from '../components/TypeArt';
import Icon from '../components/Icon';
import { ProAction, ProBadge, ProBanner } from '../components/Pro';

function RenameModal( { template, onClose } ) {
	const { putTemplate, toast } = useStore();
	const [ title, setTitle ] = useState( template.title );
	const save = ( event ) => {
		event.preventDefault();
		api.updateTemplate( template.id, { title } )
			.then( ( saved ) => {
				putTemplate( saved );
				onClose();
			} )
			.catch( ( e ) => toast( e.message, 'error' ) );
	};
	return (
		<Modal title={ __( 'Rename template', 'theme-builder-for-elementor' ) } onRequestClose={ onClose } className="tbfe-modal" size="small">
			<form onSubmit={ save }>
				<label className="tbfe-field">
					<span className="tbfe-field__label">{ __( 'Name', 'theme-builder-for-elementor' ) }</span>
					{ /* eslint-disable-next-line jsx-a11y/no-autofocus */ }
					<input className="tbfe-input" autoFocus value={ title } onChange={ ( e ) => setTitle( e.target.value ) } />
				</label>
				<div className="tbfe-modal__footer">
					<button type="button" className="tbfe-btn tbfe-btn--ghost" onClick={ onClose }>
						{ __( 'Cancel', 'theme-builder-for-elementor' ) }
					</button>
					<button type="submit" className="tbfe-btn tbfe-btn--primary">
						{ __( 'Save', 'theme-builder-for-elementor' ) }
					</button>
				</div>
			</form>
		</Modal>
	);
}

export default function Templates( { filter, onNew } ) {
	const { data } = useStore();
	const [ search, setSearch ] = useState( '' );
	const [ conditionsFor, setConditionsFor ] = useState( null );
	const [ renaming, setRenaming ] = useState( null );

	const counts = useMemo( () => {
		const c = {};
		data.templates.forEach( ( t ) => ( c[ t.type ] = ( c[ t.type ] || 0 ) + 1 ) );
		return c;
	}, [ data.templates ] );

	const list = data.templates.filter( ( t ) => ( ! filter || t.type === filter ) && ( ! search || t.title.toLowerCase().includes( search.toLowerCase() ) ) );
	const current = data.types.find( ( t ) => t.slug === filter );
	// The dialog shows the newest data of the template.
	const conditionsTemplate = conditionsFor && data.templates.find( ( t ) => t.id === conditionsFor.id );

	return (
		<div className="tbfe-templates">
			<aside className="tbfe-sidenav" aria-label={ __( 'Template types', 'theme-builder-for-elementor' ) }>
				<a href="#/templates" className={ 'tbfe-sidenav__item' + ( ! filter ? ' is-active' : '' ) }>
					<span>{ __( 'All templates', 'theme-builder-for-elementor' ) }</span>
					<span className="tbfe-count">{ data.templates.length }</span>
				</a>
				{ data.types.map( ( t ) => (
					<a key={ t.slug } href={ '#/templates/' + t.slug } className={ 'tbfe-sidenav__item' + ( filter === t.slug ? ' is-active' : '' ) }>
						<span>
							{ t.plural }
							{ t.pro && <ProBadge locked={ t.locked } /> }
						</span>
						<span className="tbfe-count">{ counts[ t.slug ] || 0 }</span>
					</a>
				) ) }
			</aside>

			<section className="tbfe-card tbfe-templates__list">
				<div className="tbfe-list-head">
					<div>
						<h1 className="tbfe-h1">{ current ? current.plural : __( 'All templates', 'theme-builder-for-elementor' ) }</h1>
						{ current && <p className="tbfe-muted tbfe-type-lead">{ current.help }</p> }
					</div>
					<div className="tbfe-list-head__tools">
						<label className="tbfe-search-field">
							<Icon name="search" size={ 16 } />
							<input type="search" value={ search } onChange={ ( e ) => setSearch( e.target.value ) } placeholder={ __( 'Search templates', 'theme-builder-for-elementor' ) } aria-label={ __( 'Search templates', 'theme-builder-for-elementor' ) } />
						</label>
						{ current?.locked ? (
							<ProAction size="" />
						) : (
							<button type="button" className="tbfe-btn tbfe-btn--primary" onClick={ () => onNew( filter ) }>
								<Icon name="plus" size={ 16 } />
								{ current ? current.label : __( 'Add new', 'theme-builder-for-elementor' ) }
							</button>
						) }
					</div>
				</div>

				{ current?.locked && <ProBanner title={ current.plural } /> }

				{ list.length ? (
					<div className="tbfe-rows">
						<div className="tbfe-row tbfe-row--head" aria-hidden="true">
							<span>{ __( 'Template', 'theme-builder-for-elementor' ) }</span>
							<span>{ __( 'Shown on', 'theme-builder-for-elementor' ) }</span>
							<span>{ __( 'Status', 'theme-builder-for-elementor' ) }</span>
							<span />
						</div>
						{ list.map( ( t ) => (
							<TemplateRow key={ t.id } template={ t } onConditions={ setConditionsFor } onRename={ setRenaming } />
						) ) }
					</div>
				) : (
					<div className="tbfe-empty">
						<TypeArt type={ filter || 'header' } />
						<h2>{ search ? __( 'No template matches the search.', 'theme-builder-for-elementor' ) : __( 'No templates here yet', 'theme-builder-for-elementor' ) }</h2>
						{ ! search && (
							<>
								<p>{ __( 'Create one from scratch or start from a ready-made demo.', 'theme-builder-for-elementor' ) }</p>
								<div className="tbfe-empty__actions">
									<button type="button" className="tbfe-btn tbfe-btn--primary" onClick={ () => onNew( filter ) }>
										<Icon name="plus" size={ 16 } />
										{ __( 'Create template', 'theme-builder-for-elementor' ) }
									</button>
									<a className="tbfe-btn tbfe-btn--soft" href={ '#/library' + ( filter ? '/' + filter : '' ) }>
										<Icon name="sparkle" size={ 16 } />
										{ __( 'Browse demos', 'theme-builder-for-elementor' ) }
									</a>
								</div>
							</>
						) }
					</div>
				) }
			</section>

			{ conditionsTemplate && <ConditionsModal template={ conditionsTemplate } onClose={ () => setConditionsFor( null ) } /> }
			{ renaming && <RenameModal template={ renaming } onClose={ () => setRenaming( null ) } /> }
		</div>
	);
}
