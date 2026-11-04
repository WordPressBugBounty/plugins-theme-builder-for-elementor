<?php
/**
 * Blog, archives and search results with an Archive template.
 *
 * Mode "layout": the template is the whole archive (it lists the posts with the Posts widget).
 * Mode "loop" (version 1): the template is repeated for each post, with an optional sidebar and the pagination.
 *
 * @package TBFE
 */

use TBFE\Conditions\Resolver;
use TBFE\Render\Renderer;
use TBFE\Render\Theme_Support;
use TBFE\Templates\Template;

defined( 'ABSPATH' ) || exit;

// On a search the Search Results template wins over the Archive template.
$tbfe_location = ( is_search() && Resolver::get( 'search' ) ) ? 'search' : 'archive';
$tbfe_id       = Resolver::get( $tbfe_location );
$tbfe_settings = Template::settings( $tbfe_id );

Theme_Support::page_start();
?>
<main id="content" class="tbfe-location tbfe-location-<?php echo esc_attr( $tbfe_location ); ?> site-main">
	<?php
	if ( 'loop' !== $tbfe_settings['mode'] ) {
		Renderer::render_location( $tbfe_location );
	} else {
		$tbfe_sidebar = (int) $tbfe_settings['sidebar'];
		?>
		<div class="tbfe-archive tbfe-archive--<?php echo esc_attr( $tbfe_settings['width'] ); ?><?php echo $tbfe_sidebar ? ' tbfe-archive--sidebar' : ''; ?>">
			<div class="tbfe-archive__posts">
				<?php
				if ( have_posts() ) {
					while ( have_posts() ) {
						the_post();
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'tbfe-archive__item' ); ?>>
							<?php Renderer::render_template( $tbfe_id ); ?>
						</article>
						<?php
					}
					the_posts_pagination(
						array(
							'class'     => 'tbfe-pagination',
							'prev_text' => '&larr;',
							'next_text' => '&rarr;',
						)
					);
				} else {
					echo '<p class="tbfe-archive__empty">' . esc_html__( 'Nothing found.', 'theme-builder-for-elementor' ) . '</p>';
				}
				?>
			</div>
			<?php if ( $tbfe_sidebar ) : ?>
				<aside class="tbfe-archive__sidebar">
					<?php Renderer::render_template( $tbfe_sidebar ); ?>
				</aside>
			<?php endif; ?>
		</div>
		<?php
	}
	?>
</main>
<?php
Theme_Support::page_end();
