import { __ } from '@wordpress/i18n';

/**
 * Layout of imported pages: between the header and footer of the site, or the page alone. Both are page templates
 * of the plugin, so they do not depend on the theme.
 */
export default function LayoutChoice( { value, onChange } ) {
	const options = [
		{
			value: 'full-width',
			title: __( 'Full width', 'theme-builder-for-elementor' ),
			text: __( 'The page between the header and footer of your site, without the sidebar and the boxes of the theme.', 'theme-builder-for-elementor' ),
		},
		{
			value: 'canvas',
			title: __( 'Canvas', 'theme-builder-for-elementor' ),
			text: __( 'The page alone, without any header or footer – for landing pages.', 'theme-builder-for-elementor' ),
		},
	];
	return (
		<div className="tbfe-layout-choice">
			<span className="tbfe-field__label">{ __( 'Page layout', 'theme-builder-for-elementor' ) }</span>
			<div className="tbfe-choice-cards" role="radiogroup" aria-label={ __( 'Page layout', 'theme-builder-for-elementor' ) }>
				{ options.map( ( option ) => (
					<button
						key={ option.value }
						type="button"
						role="radio"
						aria-checked={ value === option.value }
						className={ 'tbfe-choice-card' + ( value === option.value ? ' is-selected' : '' ) }
						onClick={ () => onChange( option.value ) }
					>
						<strong>{ option.title }</strong>
						<span>{ option.text }</span>
					</button>
				) ) }
			</div>
		</div>
	);
}
