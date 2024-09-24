<?php

$options = get_option('tbfe_settings');

$id = $options['tbfe_elementor_sidebar'];

echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( absint($id) );

