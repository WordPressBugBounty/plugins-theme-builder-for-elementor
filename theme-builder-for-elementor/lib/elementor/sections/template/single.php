<?php

$options = get_option('tbfe_settings_post');

if (isset($options['tbfe_elementor_header_post']) && $options['tbfe_elementor_header_post'] != '') {
    tbfe_template_header('post');
} else {
    get_header();
}
echo '<main id="site-content" role="main" class="' .  join( ' ', get_post_class() ) . '">';

if (have_posts()) : while (have_posts()) : the_post();

        $meta = get_post_meta(get_the_ID(), 'tbfe_elementor_post', true);

        if (isset($meta) && '' !== $meta) {
            $id = $meta;
        } else {
            $id = $options['tbfe_elementor_post'];
        }
        echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( absint($id) );

    endwhile;
endif;

echo '</main>';

if (isset($options['tbfe_elementor_footer_post']) && $options['tbfe_elementor_footer_post'] != '') {
    tbfe_template_footer('post');
} else {
    get_footer();
}
