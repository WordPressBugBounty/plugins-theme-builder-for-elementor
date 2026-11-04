<?php
/**
 * The 404 page with a 404 template.
 *
 * @package TBFE
 */

use TBFE\Render\Renderer;
use TBFE\Render\Theme_Support;

defined( 'ABSPATH' ) || exit;

Theme_Support::page_start();
?>
<main id="content" class="tbfe-location tbfe-location-404 site-main">
	<?php Renderer::render_location( '404' ); ?>
</main>
<?php
Theme_Support::page_end();
