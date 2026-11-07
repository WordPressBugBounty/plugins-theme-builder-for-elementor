<?php
/**
 * A template alone (the Elementor editor and the preview of a template), and the page template
 * "Theme Builder - Canvas": the content of the page without any header or footer.
 *
 * @package TBFE
 */

defined( 'ABSPATH' ) || exit;

// No header and footer of the theme either.
TBFE\Render\Theme_Support::remove_theme_part( 'header' );
TBFE\Render\Theme_Support::remove_theme_part( 'footer' );

require __DIR__ . '/parts/document-start.php';

while ( have_posts() ) {
	the_post();
	echo '<div class="tbfe-canvas">';
	the_content();
	echo '</div>';
}

require __DIR__ . '/parts/document-end.php';
