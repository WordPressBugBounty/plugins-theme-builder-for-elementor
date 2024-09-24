<?php

$options = get_option('tbfe_settings_page');

if (isset($options['tbfe_elementor_header_page']) && $options['tbfe_elementor_header_page'] != '') {
    tbfe_template_header('page');
} else {
    get_header();
}
echo '<main id="site-content" role="main" class="' .  join( ' ', get_post_class() ) . '">';

if (have_posts()) : while (have_posts()) : the_post();

        $meta = get_post_meta(get_the_ID(), 'tbfe_elementor_page', true);

        if (isset($meta) && '' !== $meta) {
            $id = $meta;
        } else {
            $id = $options['tbfe_elementor_page'];
        }
        echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( absint($id) );

    endwhile;
endif;

echo '</main>';

if (isset($options['tbfe_elementor_footer_page']) && $options['tbfe_elementor_footer_page'] != '') {
    tbfe_template_footer('page');
} else {
    get_footer();
}
