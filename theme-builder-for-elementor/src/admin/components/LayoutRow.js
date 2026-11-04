import { __ } from '@wordpress/i18n';
import { useEffect, useRef, useState } from '@wordpress/element';

/**
 * A row of choices that scrolls sideways (wheel, touch, keyboard) with arrows like a carousel; the arrows show only
 * when there is more to see in their direction.
 */
export default function LayoutRow( { title, children } ) {
	const track = useRef( null );
	const [ edges, setEdges ] = useState( { prev: false, next: false } );

	const measure = () => {
		const el = track.current;
		if ( el ) {
			setEdges( { prev: el.scrollLeft > 4, next: el.scrollLeft + el.clientWidth < el.scrollWidth - 4 } );
		}
	};
	useEffect( () => {
		measure();
		window.addEventListener( 'resize', measure );
		return () => window.removeEventListener( 'resize', measure );
	}, [] );

	const move = ( direction ) => {
		const el = track.current;
		el.scrollBy( { left: direction * Math.max( 200, el.clientWidth * 0.8 ), behavior: 'smooth' } );
	};

	return (
		<section className="tbfe-layout-row" aria-label={ title }>
			<h3>{ title }</h3>
			<div className="tbfe-layout-row__frame">
				<button type="button" className="tbfe-layout-row__arrow tbfe-layout-row__arrow--prev" onClick={ () => move( -1 ) } hidden={ ! edges.prev } aria-label={ __( 'Show the previous ones', 'theme-builder-for-elementor' ) }>
					<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" /></svg>
				</button>
				<div className="tbfe-layout-row__choices" role="radiogroup" aria-label={ title } ref={ track } onScroll={ measure }>
					{ children }
				</div>
				<button type="button" className="tbfe-layout-row__arrow tbfe-layout-row__arrow--next" onClick={ () => move( 1 ) } hidden={ ! edges.next } aria-label={ __( 'Show the next ones', 'theme-builder-for-elementor' ) }>
					<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" /></svg>
				</button>
			</div>
		</section>
	);
}
