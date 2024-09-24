<?php
/*
 * Adding a menu to contain the custom post types for frontpage
 */

function tbfe_pro_theme_builder_admin_menu() {
    add_menu_page(
            'Theme Builder',
            'Theme Builder',
            'manage_options',
            'theme-builder',
            '',
            '',
            70
    );
}

add_action('admin_menu', 'tbfe_pro_theme_builder_admin_menu');

/*
 * Creating a Custom Post type for Features Section
 */

function tbfe_register_new_header() {
    $args = array(
        'labels' => array(
            'name' => __('Header', 'tbfe'),
            'singular_name' => __('Header', 'tbfe'),
            'add_new_item' => __('Add New Header', 'tbfe'),
        ),
        'description' => 'Add Elementor Header',
        'supports' => array('title'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => 'theme-builder',
        'rewrite' => array('slug' => 'elementor-header',),
    );
    register_post_type('elementor-header', $args);
}

add_action('init', 'tbfe_register_new_header');

function tbfe_register_new_footer() {
    $args = array(
        'labels' => array(
            'name' => __('Footer', 'tbfe'),
            'singular_name' => __('Footer', 'tbfe'),
            'add_new_item' => __('Add New Footer', 'tbfe'),
        ),
        'description' => 'Add Elementor Footer',
        'supports' => array('title'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => 'theme-builder',
        'rewrite' => array('slug' => 'elementor-footer',),
    );
    register_post_type('elementor-footer', $args);
}

add_action('init', 'tbfe_register_new_footer');

function tbfe_register_new_404() {
    $args = array(
        'labels' => array(
            'name' => __('404 Page', 'tbfe'),
            'singular_name' => __('404 Page', 'tbfe'),
            'add_new_item' => __('Add New 404 Page', 'tbfe'),
        ),
        'description' => 'Add Elementor 404 Page',
        'supports' => array('title'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => 'theme-builder',
        'rewrite' => array('slug' => 'elementor-404',),
    );
    register_post_type('elementor-404', $args);
}

add_action('init', 'tbfe_register_new_404');

function tbfe_register_new_post() {
    $args = array(
        'labels' => array(
            'name' => __('Post', 'tbfe'),
            'singular_name' => __('Post', 'tbfe'),
            'add_new_item' => __('Add New Post', 'tbfe'),
        ),
        'description' => 'Add Elementor Post',
        'supports' => array('title'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => 'theme-builder',
        'rewrite' => array('slug' => 'elementor-post',),
    );
    register_post_type('elementor-post', $args);
}

add_action('init', 'tbfe_register_new_post');

function tbfe_register_new_page() {
    $args = array(
        'labels' => array(
            'name' => __('Page', 'tbfe'),
            'singular_name' => __('Page', 'tbfe'),
            'add_new_item' => __('Add New Page', 'tbfe'),
        ),
        'description' => 'Add Elementor Page',
        'supports' => array('title'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => 'theme-builder',
        'rewrite' => array('slug' => 'elementor-page',),
    );
    register_post_type('elementor-page', $args);
}

add_action('init', 'tbfe_register_new_page');

function tbfe_register_new_archive() {
    $args = array(
        'labels' => array(
            'name' => __('Archive', 'tbfe'),
            'singular_name' => __('Archive', 'tbfe'),
            'add_new_item' => __('Add New Archive', 'tbfe'),
        ),
        'description' => 'Add Elementor Archive',
        'supports' => array('title'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => 'theme-builder',
        'rewrite' => array('slug' => 'elementor-archive',),
    );
    register_post_type('elementor-archive', $args);
}

add_action('init', 'tbfe_register_new_archive');

function tbfe_register_new_sidebar() {
    $args = array(
        'labels' => array(
            'name' => __('Archive Sidebar', 'tbfe'),
            'singular_name' => __('Archive Sidebar', 'tbfe'),
            'add_new_item' => __('Add New Archive Sidebar', 'tbfe'),
        ),
        'description' => 'Add Elementor Archive Sidebar',
        'supports' => array('title'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => 'theme-builder',
        'rewrite' => array('slug' => 'elementor-sidebar',),
    );
    register_post_type('elementor-sidebar', $args);
}

add_action('init', 'tbfe_register_new_sidebar');

function tbfe_add_cpt_support() {

    //if exists, assign to $cpt_support var
    $cpt_support = get_option('elementor_cpt_support');

    //check if option DOESN'T exist in db
    if (!$cpt_support) {
        $cpt_support = ['page', 'post', 'elementor-footer', 'elementor-header', 'elementor-404', 'elementor-post', 'elementor-page', 'elementor-archive', 'elementor-sidebar']; //create array of our default supported post types
        update_option('elementor_cpt_support', $cpt_support); //write it to the database
    }

    //if it DOES exist, but elementor-footer is NOT defined
    else if (!in_array('elementor-footer', $cpt_support)) {
        $cpt_support[] = 'elementor-footer'; //append to array
        $cpt_support[] = 'elementor-header'; //append to array
        $cpt_support[] = 'elementor-404'; //append to array
        $cpt_support[] = 'elementor-post'; //append to array
        $cpt_support[] = 'elementor-page'; //append to array
        $cpt_support[] = 'elementor-archive'; //append to array
        $cpt_support[] = 'elementor-sidebar'; //append to array
        update_option('elementor_cpt_support', $cpt_support); //update database
    }

    //otherwise do nothing
}

add_action('activated_plugin', 'tbfe_add_cpt_support');

function tbfe_load_elementor_template($template) {
    global $post;

    if ('elementor-header' === $post->post_type) {

        return TBFE_PRO_PATH . 'lib/elementor/sections/single-elementor-header.php';
    }
    if ('elementor-footer' === $post->post_type) {

        return TBFE_PRO_PATH . 'lib/elementor/sections/single-elementor-footer.php';
    }
    if ('elementor-404' === $post->post_type) {

        return TBFE_PRO_PATH . 'lib/elementor/sections/single-elementor-404.php';
    }
    if ('elementor-post' === $post->post_type) {

        return TBFE_PRO_PATH . 'lib/elementor/sections/single-elementor-post.php';
    }
    if ('elementor-page' === $post->post_type) {

        return TBFE_PRO_PATH . 'lib/elementor/sections/single-elementor-page.php';
    }
    if ('elementor-archive' === $post->post_type) {

        return TBFE_PRO_PATH . 'lib/elementor/sections/single-elementor-archive.php';
    }
    if ('elementor-sidebar' === $post->post_type ) {

        return TBFE_PRO_PATH . 'lib/elementor/sections/single-elementor-sidebar.php';
    }
    return $template;
}

add_filter('single_template', 'tbfe_load_elementor_template');

add_filter('template_include', 'tbfe_template', 99);

function tbfe_template($template) {
    $options_page = get_option('tbfe_settings_page');
    $options_post = get_option('tbfe_settings_post');
    $options_archive = get_option('tbfe_settings_archive');
    $options_404 = get_option('tbfe_settings_404');

    // Pages
    if (is_page() && !is_page_template() && !tbfe_is_elementor() && 'page' == get_post_type() && isset($options_page['tbfe_elementor_page']) && $options_page['tbfe_elementor_page'] != '') {
        $new_template = locate_template(array('theme-builder/page.php'));
        if ('' === $new_template) {
            return TBFE_PRO_PATH . 'lib/elementor/sections/template/page.php';
        }
    }
    // Posts
    if (is_single() && 'post' == get_post_type() && !tbfe_is_elementor() && isset($options_post['tbfe_elementor_post']) && $options_post['tbfe_elementor_post'] != '') {
        $new_template = locate_template(array('theme-builder/single.php'));
        if ('' === $new_template) {
            return TBFE_PRO_PATH . 'lib/elementor/sections/template/single.php';
        }
    }
    // Archives
    if ((is_archive() || is_home() ) && get_post_type(get_the_ID()) != 'product' && isset($options_archive['tbfe_elementor_archive']) && $options_archive['tbfe_elementor_archive'] != '') {
        $new_template = locate_template(array('theme-builder/archive.php'));
        if ('' === $new_template) {
            return TBFE_PRO_PATH . 'lib/elementor/sections/template/archive.php';
        }
    }
    // 404
    if (is_404() && isset($options_404['tbfe_elementor_404']) && $options_404['tbfe_elementor_404'] != '') {
        $new_template = locate_template(array('theme-builder/404.php'));
        if ('' === $new_template) {
            return TBFE_PRO_PATH . 'lib/elementor/sections/template/404.php';
        }
    }

    return $template;
}

function tbfe_template_header($header) {
    $options = get_option('tbfe_settings_' . $header);
    $meta = get_post_meta(get_the_ID(), 'tbfe_elementor_header_' . $header, true);
    if (isset($meta) && '' !== $meta && get_the_ID()) {
        $id = $meta;
    } else {
        $id = $options['tbfe_elementor_header_' . $header];
    }
    ?>
    <!DOCTYPE html>

    <html class="no-js" <?php language_attributes(); ?>>

        <head>

            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0" >

            <link rel="profile" href="https://gmpg.org/xfn/11">

            <?php wp_head(); ?>

        </head>

        <body <?php body_class(); ?>>

            <?php wp_body_open(); ?>
            <header id="site-header" class="builder-header header-id-<?php echo absint($id); ?>" role="banner">
                <?php echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( absint($id) ); ?>
            </header>
            <?php
        }

        function tbfe_template_footer($footer) {
            $options = get_option('tbfe_settings_' . $footer);
            $meta = get_post_meta(get_the_ID(), 'tbfe_elementor_footer_' . $footer, true);
            if (isset($meta) && '' !== $meta && get_the_ID()) {
                $id = $meta;
            } else {
                $id = $options['tbfe_elementor_footer_' . $footer];
            }
            ?>
            <footer id="site-footer" role="contentinfo" class="builder-footer footer-id-<?php echo absint($id); ?>">
                <?php echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( absint($id) ); ?>
            </footer>
            <?php wp_footer(); ?>
        </body>
    </html>
    <?php
}
