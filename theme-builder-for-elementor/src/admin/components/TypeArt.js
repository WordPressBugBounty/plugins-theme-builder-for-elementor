/**
 * Small wireframe illustration of a template type. The highlighted part uses the accent color.
 */
const Frame = ( { children } ) => (
	<svg className="tbfe-art" viewBox="0 0 120 80" aria-hidden="true" focusable="false">
		<rect className="tbfe-art__page" x="1" y="1" width="118" height="78" rx="7" />
		{ children }
	</svg>
);

const Lines = ( { x = 12, y, widths } ) =>
	widths.map( ( w, i ) => <rect key={ i } className="tbfe-art__line" x={ x } y={ y + i * 7 } width={ w } height="3" rx="1.5" /> );

const arts = {
	header: (
		<Frame>
			<rect className="tbfe-art__hot" x="1" y="1" width="118" height="18" rx="7" />
			<rect className="tbfe-art__on-hot" x="9" y="7" width="16" height="6" rx="2" />
			<rect className="tbfe-art__on-hot" x="64" y="8.5" width="10" height="3" rx="1.5" />
			<rect className="tbfe-art__on-hot" x="78" y="8.5" width="10" height="3" rx="1.5" />
			<rect className="tbfe-art__on-hot" x="92" y="6.5" width="18" height="7" rx="3.5" />
			<Lines y={ 30 } widths={ [ 70, 96, 84, 60 ] } />
		</Frame>
	),
	footer: (
		<Frame>
			<Lines y={ 12 } widths={ [ 70, 96, 84, 60 ] } />
			<rect className="tbfe-art__hot" x="1" y="54" width="118" height="25" rx="7" />
			<rect className="tbfe-art__on-hot" x="10" y="61" width="22" height="3" rx="1.5" />
			<rect className="tbfe-art__on-hot" x="46" y="61" width="22" height="3" rx="1.5" />
			<rect className="tbfe-art__on-hot" x="82" y="61" width="22" height="3" rx="1.5" />
			<rect className="tbfe-art__on-hot" x="10" y="69" width="40" height="3" rx="1.5" />
		</Frame>
	),
	single: (
		<Frame>
			<rect className="tbfe-art__line" x="12" y="9" width="96" height="6" rx="1.5" />
			<rect className="tbfe-art__hot" x="12" y="20" width="96" height="26" rx="4" />
			<Lines y={ 52 } widths={ [ 96, 90, 78 ] } />
		</Frame>
	),
	archive: (
		<Frame>
			<rect className="tbfe-art__line" x="12" y="9" width="44" height="5" rx="1.5" />
			{ [ 12, 44, 76 ].map( ( x ) => (
				<g key={ x }>
					<rect className="tbfe-art__hot" x={ x } y="20" width="28" height="18" rx="3" />
					<rect className="tbfe-art__line" x={ x } y="42" width="26" height="3" rx="1.5" />
					<rect className="tbfe-art__line" x={ x } y="48" width="20" height="3" rx="1.5" />
				</g>
			) ) }
			<rect className="tbfe-art__line" x="44" y="62" width="32" height="6" rx="3" />
		</Frame>
	),
	search: (
		<Frame>
			<rect className="tbfe-art__line" x="12" y="9" width="50" height="5" rx="1.5" />
			<rect className="tbfe-art__card" x="12" y="19" width="80" height="11" rx="5.5" />
			<rect className="tbfe-art__hot" x="94" y="19" width="14" height="11" rx="5.5" />
			{ [ 38, 52, 66 ].map( ( y ) => (
				<g key={ y }>
					<rect className="tbfe-art__hot" x="12" y={ y } width="16" height="10" rx="2" />
					<rect className="tbfe-art__line" x="32" y={ y + 1 } width="60" height="3" rx="1.5" />
					<rect className="tbfe-art__line" x="32" y={ y + 6 } width="40" height="3" rx="1.5" />
				</g>
			) ) }
		</Frame>
	),
	'error-404': (
		<Frame>
			<text className="tbfe-art__text" x="60" y="44" textAnchor="middle">
				404
			</text>
			<rect className="tbfe-art__line" x="34" y="53" width="52" height="3" rx="1.5" />
			<rect className="tbfe-art__hot" x="45" y="61" width="30" height="7" rx="3.5" />
		</Frame>
	),
	'loop-item': (
		<Frame>
			<rect className="tbfe-art__card" x="30" y="8" width="60" height="64" rx="5" />
			<rect className="tbfe-art__hot" x="34" y="12" width="52" height="26" rx="3" />
			<Lines x={ 36 } y={ 44 } widths={ [ 44, 38, 30 ] } />
		</Frame>
	),
	section: (
		<Frame>
			<Lines y={ 10 } widths={ [ 80, 60 ] } />
			<rect className="tbfe-art__hot" x="12" y="28" width="96" height="24" rx="4" />
			<rect className="tbfe-art__on-hot" x="20" y="36" width="44" height="4" rx="2" />
			<rect className="tbfe-art__on-hot" x="84" y="35" width="16" height="7" rx="3.5" />
			<Lines y={ 60 } widths={ [ 70, 50 ] } />
		</Frame>
	),
	'mega-menu': (
		<Frame>
			<rect className="tbfe-art__line" x="12" y="9" width="14" height="4" rx="2" />
			<rect className="tbfe-art__hot" x="32" y="9" width="16" height="4" rx="2" />
			<rect className="tbfe-art__line" x="54" y="9" width="14" height="4" rx="2" />
			<rect className="tbfe-art__card" x="10" y="19" width="100" height="52" rx="4" />
			<Lines x={ 18 } y={ 27 } widths={ [ 20, 16, 18, 14 ] } />
			<Lines x={ 46 } y={ 27 } widths={ [ 20, 14, 18, 16 ] } />
			<rect className="tbfe-art__hot" x="74" y="26" width="28" height="36" rx="3" />
		</Frame>
	),
	popup: (
		<Frame>
			<Lines y={ 10 } widths={ [ 90, 70, 96, 80, 60, 88, 74, 92 ] } />
			<rect className="tbfe-art__shade" x="1" y="1" width="118" height="78" rx="7" />
			<rect className="tbfe-art__card" x="28" y="16" width="64" height="48" rx="5" />
			<rect className="tbfe-art__hot" x="36" y="26" width="48" height="6" rx="2" />
			<rect className="tbfe-art__line" x="36" y="37" width="40" height="3" rx="1.5" />
			<rect className="tbfe-art__hot" x="44" y="50" width="32" height="7" rx="3.5" />
			<circle className="tbfe-art__line" cx="86" cy="21" r="2.5" />
		</Frame>
	),
	product: (
		<Frame>
			<rect className="tbfe-art__hot" x="10" y="10" width="48" height="60" rx="4" />
			<rect className="tbfe-art__line" x="66" y="12" width="40" height="5" rx="1.5" />
			<rect className="tbfe-art__line" x="66" y="22" width="22" height="4" rx="1.5" />
			<Lines x={ 66 } y={ 32 } widths={ [ 42, 36, 40 ] } />
			<rect className="tbfe-art__hot" x="66" y="56" width="30" height="9" rx="4.5" />
		</Frame>
	),
	'product-archive': (
		<Frame>
			<rect className="tbfe-art__line" x="12" y="8" width="40" height="5" rx="1.5" />
			{ [ 12, 38, 64, 90 ].map( ( x ) => (
				<g key={ x }>
					<rect className="tbfe-art__hot" x={ x } y="18" width="20" height="22" rx="3" />
					<rect className="tbfe-art__line" x={ x } y="44" width="18" height="3" rx="1.5" />
					<rect className="tbfe-art__line" x={ x } y="50" width="10" height="3" rx="1.5" />
					<rect className="tbfe-art__card" x={ x } y="57" width="20" height="6" rx="3" />
				</g>
			) ) }
		</Frame>
	),
	cart: (
		<Frame>
			{ [ 12, 26, 40 ].map( ( y ) => (
				<g key={ y }>
					<rect className="tbfe-art__card" x="10" y={ y } width="10" height="10" rx="2" />
					<rect className="tbfe-art__line" x="24" y={ y + 3 } width="36" height="3" rx="1.5" />
					<rect className="tbfe-art__line" x="64" y={ y + 3 } width="8" height="3" rx="1.5" />
				</g>
			) ) }
			<rect className="tbfe-art__card" x="80" y="12" width="30" height="40" rx="3" />
			<Lines x={ 84 } y={ 18 } widths={ [ 22, 18, 22 ] } />
			<rect className="tbfe-art__hot" x="84" y="40" width="22" height="7" rx="3.5" />
			<rect className="tbfe-art__line" x="10" y="62" width="50" height="3" rx="1.5" />
		</Frame>
	),
	checkout: (
		<Frame>
			{ [ 10, 24, 38, 52 ].map( ( y ) => (
				<g key={ y }>
					<rect className="tbfe-art__line" x="10" y={ y } width="20" height="3" rx="1.5" />
					<rect className="tbfe-art__card" x="10" y={ y + 5 } width="56" height="6" rx="2" />
				</g>
			) ) }
			<rect className="tbfe-art__card" x="74" y="10" width="36" height="56" rx="3" />
			<Lines x={ 78 } y={ 16 } widths={ [ 28, 22, 28, 18 ] } />
			<rect className="tbfe-art__hot" x="78" y="52" width="28" height="8" rx="4" />
		</Frame>
	),
	'my-account': (
		<Frame>
			<rect className="tbfe-art__hot" x="10" y="10" width="30" height="60" rx="4" />
			<rect className="tbfe-art__on-hot" x="15" y="18" width="20" height="3" rx="1.5" />
			<rect className="tbfe-art__on-hot" x="15" y="26" width="16" height="3" rx="1.5" />
			<rect className="tbfe-art__on-hot" x="15" y="34" width="18" height="3" rx="1.5" />
			<rect className="tbfe-art__on-hot" x="15" y="42" width="14" height="3" rx="1.5" />
			<rect className="tbfe-art__line" x="48" y="12" width="40" height="5" rx="1.5" />
			<Lines x={ 48 } y={ 24 } widths={ [ 60, 52, 56 ] } />
			<rect className="tbfe-art__card" x="48" y="48" width="60" height="18" rx="3" />
		</Frame>
	),
	thankyou: (
		<Frame>
			<circle className="tbfe-art__hot" cx="60" cy="22" r="10" />
			<path className="tbfe-art__check" d="M55 22l3.5 3.5L65.5 18" />
			<rect className="tbfe-art__line" x="34" y="38" width="52" height="4" rx="2" />
			<rect className="tbfe-art__card" x="18" y="48" width="84" height="22" rx="3" />
			<Lines x={ 24 } y={ 53 } widths={ [ 50, 64, 40 ] } />
		</Frame>
	),
};

export default function TypeArt( { type } ) {
	return arts[ type ] || arts.section;
}
