<?php
/**
 * Single post, page or custom post type with a Single template.
 *
 * @package TBFE
 */

use TBFE\Render\Renderer;
use TBFE\Render\Theme_Support;

defined( 'ABSPATH' ) || exit;

Theme_Support::page_start();
?>
<main id="content" class="tbfe-location tbfe-location-single site-main">
	<?php
	while ( have_posts() ) {
		the_post();
		if ( post_password_required() ) {
			echo '<div class="tbfe-container tbfe-password">' . \TBFE\Plugin::kses( get_the_password_form() ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with Plugin::kses().
			continue;
		}
		?>
		<article id="post-<?php the_ID(); ?>" class="<?php echo esc_attr( implode( ' ', Renderer::post_class( 'tbfe-single' ) ) ); ?>">
			<?php Renderer::render_location( 'single' ); ?>
		</article>
		<?php
	}
	?>
</main>
<?php
Theme_Support::page_end();
