import { __ } from '@wordpress/i18n';
import TypeArt from './TypeArt';
import Icon from './Icon';

/**
 * What a template type is and how it is used: one type (panel) or all types (list that can be opened).
 */
export default function TypeHelp( { types, type } ) {
	if ( type ) {
		return (
			<div className="tbfe-type-help">
				<span className="tbfe-type-help__art">
					<TypeArt type={ type.slug } />
				</span>
				<div>
					<strong>{ type.plural }</strong>
					<p>{ type.help }</p>
				</div>
			</div>
		);
	}
	return (
		<details className="tbfe-type-help tbfe-type-help--all">
			<summary>
				<Icon name="book" size={ 16 } />
				{ __( 'What are the template types?', 'theme-builder-for-elementor' ) }
			</summary>
			<dl className="tbfe-type-help__list">
				{ types.map( ( t ) => (
					<div key={ t.slug } className="tbfe-type-help__row">
						<span className="tbfe-type-help__art">
							<TypeArt type={ t.slug } />
						</span>
						<div>
							<dt>{ t.plural }</dt>
							<dd>{ t.help }</dd>
						</div>
					</div>
				) ) }
			</dl>
		</details>
	);
}
