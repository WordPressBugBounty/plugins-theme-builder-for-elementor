import { __, sprintf } from '@wordpress/i18n';
import { DropdownMenu, MenuGroup, MenuItem } from '@wordpress/components';
import { api } from '../api';
import { useStore } from '../store';
import Icon from './Icon';
import { ProAction, ProBadge } from './Pro';

export const status = ( template, type ) => {
	if ( 'publish' !== template.status ) {
		return { key: 'draft', label: __( 'Draft', 'theme-builder-for-elementor' ) };
	}
	if ( type && type.location && ! template.conditions.length ) {
		return { key: 'idle', label: __( 'Not shown', 'theme-builder-for-elementor' ) };
	}
	return { key: 'active', label: type?.location ? __( 'Active', 'theme-builder-for-elementor' ) : __( 'Ready', 'theme-builder-for-elementor' ) };
};

const ago = ( iso ) => {
	const diff = ( Date.now() - new Date( iso ).getTime() ) / 1000;
	if ( diff < 3600 ) {
		return sprintf( /* translators: %d: minutes */ __( '%d min ago', 'theme-builder-for-elementor' ), Math.max( 1, Math.round( diff / 60 ) ) );
	}
	if ( diff < 86400 ) {
		return sprintf( /* translators: %d: hours */ __( '%d h ago', 'theme-builder-for-elementor' ), Math.round( diff / 3600 ) );
	}
	return new Date( iso ).toLocaleDateString();
};

export default function TemplateRow( { template, onConditions, onRename } ) {
	const { type: getType, putTemplate, removeTemplate, toast } = useStore();
	const type = getType( template.type );
	const state = status( template, type );
	const hasConditions = type && type.conditions.length > 0;

	const run = ( promise, message ) =>
		promise
			.then( ( result ) => {
				if ( result && result.id ) {
					putTemplate( result );
				}
				if ( message ) {
					toast( message );
				}
			} )
			.catch( ( e ) => toast( e.message, 'error' ) );

	// A locked PRO template (no active license) keeps working on the site, but only Preview and the trash are offered.
	const actions = [
		! template.locked && {
			title: __( 'Rename', 'theme-builder-for-elementor' ),
			icon: 'edit',
			onClick: () => onRename( template ),
		},
		! template.locked && {
			title: __( 'Duplicate', 'theme-builder-for-elementor' ),
			icon: 'copy',
			onClick: () => run( api.duplicateTemplate( template.id ), __( 'Copy created as a draft.', 'theme-builder-for-elementor' ) ),
		},
		{
			title: __( 'Preview', 'theme-builder-for-elementor' ),
			icon: 'eye',
			onClick: () => window.open( template.preview, '_blank', 'noopener' ),
		},
		! template.locked && {
			title: 'publish' === template.status ? __( 'Switch to draft', 'theme-builder-for-elementor' ) : __( 'Publish', 'theme-builder-for-elementor' ),
			icon: 'check',
			onClick: () =>
				run(
					api.updateTemplate( template.id, { status: 'publish' === template.status ? 'draft' : 'publish' } ),
					'publish' === template.status ? __( 'Template switched to draft.', 'theme-builder-for-elementor' ) : __( 'Template published.', 'theme-builder-for-elementor' )
				),
		},
	].filter( Boolean );
	if ( ! type?.location ) {
		actions.push( {
			title: __( 'Copy shortcode', 'theme-builder-for-elementor' ),
			icon: 'copy',
			onClick: () => {
				window.navigator.clipboard?.writeText( template.shortcode );
				toast( sprintf( /* translators: %s: shortcode */ __( 'Copied %s', 'theme-builder-for-elementor' ), template.shortcode ) );
			},
		} );
	}

	const trash = () => {
		// eslint-disable-next-line no-alert
		if ( ! window.confirm( sprintf( /* translators: %s: template title */ __( 'Move “%s” to the trash?', 'theme-builder-for-elementor' ), template.title ) ) ) {
			return;
		}
		api.deleteTemplate( template.id )
			.then( () => {
				removeTemplate( template.id );
				toast( __( 'Template moved to the trash.', 'theme-builder-for-elementor' ) );
			} )
			.catch( ( e ) => toast( e.message, 'error' ) );
	};

	return (
		<div className="tbfe-row">
			<div className="tbfe-row__title">
				{ template.locked ? (
					<span className="tbfe-row__name">{ template.title || __( '(no title)', 'theme-builder-for-elementor' ) }</span>
				) : (
					<a href={ template.edit_url } className="tbfe-row__name">
						{ template.title || __( '(no title)', 'theme-builder-for-elementor' ) }
					</a>
				) }
				<span className="tbfe-row__meta">
					{ type?.label } · { ago( template.modified ) } · { template.author }
					{ template.locked && <ProBadge locked /> }
				</span>
			</div>
			<div className="tbfe-row__where">
				{ hasConditions ? (
					<button type="button" className="tbfe-where" onClick={ () => onConditions( template ) } disabled={ template.locked }>
						<Icon name="rules" size={ 15 } />
						<span>{ template.summary || __( 'Set where it is shown', 'theme-builder-for-elementor' ) }</span>
					</button>
				) : (
					<code className="tbfe-shortcode">{ template.shortcode }</code>
				) }
			</div>
			<div className="tbfe-row__status">
				<span className={ 'tbfe-pill tbfe-pill--' + state.key }>{ state.label }</span>
			</div>
			<div className="tbfe-row__actions">
				{ template.locked ? (
					<ProAction short />
				) : (
					<a className="tbfe-btn tbfe-btn--soft tbfe-btn--sm" href={ template.edit_url }>
						<Icon name="edit" size={ 15 } />
						{ __( 'Edit', 'theme-builder-for-elementor' ) }
					</a>
				) }
				<DropdownMenu icon={ <Icon name="more" /> } label={ __( 'More actions', 'theme-builder-for-elementor' ) } popoverProps={ { placement: 'bottom-end' } }>
					{ ( { onClose } ) => (
						<>
							<MenuGroup>
								{ actions.map( ( action ) => (
									<MenuItem
										key={ action.title }
										icon={ <Icon name={ action.icon } size={ 16 } /> }
										iconPosition="left"
										onClick={ () => {
											onClose();
											action.onClick();
										} }
									>
										{ action.title }
									</MenuItem>
								) ) }
							</MenuGroup>
							<MenuGroup>
								<MenuItem
									isDestructive
									icon={ <Icon name="trash" size={ 16 } /> }
									iconPosition="left"
									onClick={ () => {
										onClose();
										trash();
									} }
								>
									{ __( 'Move to trash', 'theme-builder-for-elementor' ) }
								</MenuItem>
							</MenuGroup>
						</>
					) }
				</DropdownMenu>
			</div>
		</div>
	);
}
