<?php
$options = get_option('tbfe_settings_archive');
$archive = $options['tbfe_elementor_archive'];
$sidebar = $options['tbfe_elementor_sidebar_archive'];
$layout = $options['tbfe_elementor_layout_archive'];

if (isset($options['tbfe_elementor_header_archive']) && $options['tbfe_elementor_header_archive'] != '') {
    tbfe_template_header('archive');
} else {
    get_header();
}
?>
<main id="site-content" role="main">
    <div class="container<?php echo (($layout == 'boxed') ? '' : '-fluid') ?>">
        <div class="row">

            <div class="col-md-<?php echo ((!empty($sidebar) && $sidebar != '') ? '9' : '12') ?>">

                <?php
                if (have_posts()) :

                    while (have_posts()) : the_post();
                        ?>
                        <article <?php post_class(); ?>>
                            <?php echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( absint($archive) ); ?>
                        </article>
                        <?php
                    endwhile;

                    the_posts_pagination();

                else :

                endif;
                ?>

            </div>

            <?php if (!empty($sidebar) && $sidebar != '') { ?>
                <aside id="sidebar" class="col-md-3">
                    <?php echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( absint($sidebar) ); ?>
                </aside>
            <?php } ?>                

        </div>
    </div>
</main>
<?php
if (isset($options['tbfe_elementor_footer_archive']) && $options['tbfe_elementor_footer_archive'] != '') {
    tbfe_template_footer('page');
} else {
    get_footer();
}

