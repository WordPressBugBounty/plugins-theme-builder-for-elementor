import { __, sprintf } from '@wordpress/i18n';
import { Modal } from '@wordpress/components';
import { useState } from '@wordpress/element';
import ColorStep from './ColorStep';
import Icon from './Icon';

/**
 * Import of one template from the Demo Library: its colors can be changed first.
 */
export default function ColorImportModal( { item, onClose, onImport } ) {
	const [ colors, setColors ] = useState( null );
	return (
		<Modal
			title={ sprintf( /* translators: %s: demo title */ __( 'Import “%s”', 'theme-builder-for-elementor' ), item.title ) }
			onRequestClose={ onClose }
			className="tbfe-modal tbfe-modal--colors"
			size="medium"
		>
			<p className="tbfe-modal__lead">{ __( 'Keep the colors of the demo or choose your own. The template arrives as a draft.', 'theme-builder-for-elementor' ) }</p>
			<ColorStep entries={ [ { kind: 'item', id: item.id } ] } preview={ item.preview } onChange={ setColors } />
			<div className="tbfe-modal__footer">
				<button type="button" className="tbfe-btn tbfe-btn--ghost" onClick={ onClose }>
					{ __( 'Cancel', 'theme-builder-for-elementor' ) }
				</button>
				<button type="button" className="tbfe-btn tbfe-btn--primary" onClick={ () => onImport( colors ) }>
					<Icon name="download" size={ 16 } />
					{ __( 'Import', 'theme-builder-for-elementor' ) }
				</button>
			</div>
		</Modal>
	);
}
