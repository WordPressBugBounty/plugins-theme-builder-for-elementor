import { createRoot } from '@wordpress/element';
import App from './App';
import { StoreProvider } from './store';
import './admin.scss';

const root = document.getElementById( 'tbfe-app' );

if ( root ) {
	createRoot( root ).render(
		<StoreProvider>
			<App />
		</StoreProvider>
	);
}
