<?php
/**
 *
 * Template name: Theme Builder - Custom Template
 * 
 */
$options = get_option('tbfe_settings_page');

if (isset($options['tbfe_elementor_header_page']) && $options['tbfe_elementor_header_page'] != '') {
    tbfe_template_header('page');
} else {
    get_header();
}
?>
<!-- start content container -->       
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div <?php post_class(); ?>>
            <?php the_content(); ?>
        </div>
    <?php endwhile; ?>        
<?php else : ?>                  
<?php endif; ?>    
<!-- end content container -->
<?php
if (isset($options['tbfe_elementor_footer_page']) && $options['tbfe_elementor_footer_page'] != '') {
    tbfe_template_footer('page');
} else {
    get_footer();
}