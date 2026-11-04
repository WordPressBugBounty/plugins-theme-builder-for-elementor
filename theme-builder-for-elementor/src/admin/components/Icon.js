/**
 * Line icons of the administration (24×24, stroke = currentColor).
 */
const paths = {
	plus: 'M12 5v14M5 12h14',
	edit: 'M4 20h4L19 9l-4-4L4 16v4ZM14 6l4 4',
	rules: 'M4 6h10M4 12h16M4 18h7M17 4v4M14 6h6',
	more: 'M5 12h.01M12 12h.01M19 12h.01',
	copy: 'M9 9h10v10H9zM5 15V5h10',
	eye: 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Zm10 3a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
	trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3',
	download: 'M12 4v11m0 0-4-4m4 4 4-4M5 20h14',
	search: 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Zm5-2 4 4',
	check: 'M5 12l5 5L20 7',
	close: 'M6 6l12 12M18 6 6 18',
	book: 'M5 4h11a3 3 0 0 1 3 3v13H8a3 3 0 0 1-3-3V4Zm0 13a3 3 0 0 1 3-3h11',
	external: 'M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5',
	refresh: 'M20 11a8 8 0 1 0-2.3 5.7M20 4v7h-7',
	sparkle: 'M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M6 18l2.5-2.5M15.5 8.5 18 6',
	warning: 'M12 4 2.5 20h19L12 4Zm0 6v4m0 3h.01',
	globe: 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm-9-9h18M12 3c2.5 2.5 3.5 5.5 3.5 9S14.5 18.5 12 21c-2.5-2.5-3.5-5.5-3.5-9S9.5 5.5 12 3Z',
	layers: 'M12 3 2 8l10 5 10-5-10-5Zm-10 9 10 5 10-5M2 16l10 5 10-5',
	settings: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm7.4-3a7.4 7.4 0 0 0-.1-1.2l2-1.6-2-3.4-2.4 1a7.5 7.5 0 0 0-2-1.2L14.5 3h-4l-.4 2.6a7.5 7.5 0 0 0-2 1.2l-2.4-1-2 3.4 2 1.6a7.4 7.4 0 0 0 0 2.4l-2 1.6 2 3.4 2.4-1a7.5 7.5 0 0 0 2 1.2l.4 2.6h4l.4-2.6a7.5 7.5 0 0 0 2-1.2l2.4 1 2-3.4-2-1.6c.1-.4.1-.8.1-1.2Z',
	home: 'M3 11 12 4l9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9Z',
	heart: 'M12 20.5S3 15 3 9c0-2.8 2.1-5 4.7-5 1.8 0 3.4 1 4.3 2.6C12.9 5 14.5 4 16.3 4 18.9 4 21 6.2 21 9c0 6-9 11.5-9 11.5Z',
	compare: 'M7 4 3 8l4 4M3 8h13M17 20l4-4-4-4M21 16H8',
	filter: 'M4 5h16l-6 8v5l-4 2v-7L4 5Z',
	swatch: 'M12 21a9 9 0 1 1 0-18c5 0 9 3.6 9 8 0 2.8-2.2 4-4.5 4H15a2 2 0 0 0-1.4 3.4A1.5 1.5 0 0 1 12 21ZM7.5 11h.01M10 7.5h.01M14.5 7.5h.01',
	widgets: 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
	lock: 'M7 11V8a5 5 0 0 1 10 0v3M6 11h12v9H6z',
	cart: 'M3 4h2l2.4 11h10.2L20 8H6.2M9 20h.01M17 20h.01',
	key: 'M14 10a4 4 0 1 0-1.2 2.8L20 20M17 17l2-2M15 15l2-2',
};

export default function Icon( { name, size = 18 } ) {
	return (
		<svg
			className="tbfe-icon"
			width={ size }
			height={ size }
			viewBox="0 0 24 24"
			fill="none"
			stroke="currentColor"
			strokeWidth="1.8"
			strokeLinecap="round"
			strokeLinejoin="round"
			aria-hidden="true"
			focusable="false"
		>
			<path d={ paths[ name ] || paths.plus } />
		</svg>
	);
}
