<?php
/**
 * Page template "Theme Builder - Full width" (template-custom.php of version 1): the page content between the
 * header and footer, without the layout of the theme.
 *
 * @package TBFE
 */

use TBFE\Render\Theme_Support;

defined( 'ABSPATH' ) || exit;

Theme_Support::page_start();
?>
<main id="content" class="tbfe-location tbfe-location-content site-main">
	<?php
	while ( have_posts() ) {
		the_post();
		?>
		<div <?php post_class( 'tbfe-page' ); ?>>
			<?php the_content(); ?>
		</div>
		<?php
	}
	?>
</main>
<?php
Theme_Support::page_end();
