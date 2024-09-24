<?php

$options = get_option('tbfe_settings_404');

if (isset($options['tbfe_elementor_header_404']) && $options['tbfe_elementor_header_404'] != '') {
    tbfe_template_header('404');
} else {
    get_header();
}
echo '<main id="site-content" role="main">';

        $id = $options['tbfe_elementor_404'];
        echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( absint($id) );

echo '</main>';

if (isset($options['tbfe_elementor_footer_404']) && $options['tbfe_elementor_footer_404'] != '') {
    tbfe_template_footer('404');
} else {
    get_footer();
}