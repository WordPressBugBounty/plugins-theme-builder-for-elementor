<?php
add_action('admin_menu', 'tbfe_add_admin_menu');
add_action('admin_init', 'tbfe_settings_init');

function tbfe_add_admin_menu() {

    add_submenu_page('theme-builder', 'Settings', 'Settings', 'manage_options', 'tbfe', 'tbfe_options_page');
}

function tbfe_settings_init() {

    // Pages
    register_setting('tbfe_pluginPage_page', 'tbfe_settings_page');
    add_settings_section(
            'tbfe_pluginPage_section_page',
            '',
            'tbfe_settings_section_callback',
            'tbfe_pluginPage_page'
    );
    add_settings_field(
            'tbfe_elementor_header_page',
            __('Page Header', 'tbfe'),
            'tbfe_select_elementor_page_header',
            'tbfe_pluginPage_page',
            'tbfe_pluginPage_section_page'
    );
    add_settings_field(
            'tbfe_elementor_page',
            __('Page Content', 'tbfe'),
            'tbfe_select_elementor_page',
            'tbfe_pluginPage_page',
            'tbfe_pluginPage_section_page'
    );
    add_settings_field(
            'tbfe_elementor_footer_page',
            __('Page Footer', 'tbfe'),
            'tbfe_select_elementor_page_footer',
            'tbfe_pluginPage_page',
            'tbfe_pluginPage_section_page'
    );
    // End Pages
    
    // Posts
    register_setting('tbfe_pluginPage_post', 'tbfe_settings_post');
    add_settings_section(
            'tbfe_pluginPage_section_post',
            '',
            'tbfe_settings_section_callback',
            'tbfe_pluginPage_post'
    );
    add_settings_field(
            'tbfe_elementor_header_post',
            __('Post Header', 'tbfe'),
            'tbfe_select_elementor_post_header',
            'tbfe_pluginPage_post',
            'tbfe_pluginPage_section_post'
    );
    add_settings_field(
            'tbfe_elementor_post',
            __('Post Content', 'tbfe'),
            'tbfe_select_elementor_post',
            'tbfe_pluginPage_post',
            'tbfe_pluginPage_section_post'
    );
    add_settings_field(
            'tbfe_elementor_footer_post',
            __('Post Footer', 'tbfe'),
            'tbfe_select_elementor_post_footer',
            'tbfe_pluginPage_post',
            'tbfe_pluginPage_section_post'
    );
    // End Posts
    
    // Archive
    register_setting('tbfe_pluginPage_archive', 'tbfe_settings_archive');
    add_settings_section(
            'tbfe_pluginPage_section_archive',
            '',
            'tbfe_settings_section_callback',
            'tbfe_pluginPage_archive'
    );
    add_settings_field(
            'tbfe_elementor_header_archive',
            __('Archive Header', 'tbfe'),
            'tbfe_select_elementor_archive_header',
            'tbfe_pluginPage_archive',
            'tbfe_pluginPage_section_archive'
    );
    add_settings_field(
            'tbfe_elementor_archive',
            __('Elementor Archive', 'tbfe'),
            'tbfe_select_elementor_archive',
            'tbfe_pluginPage_archive',
            'tbfe_pluginPage_section_archive'
    );
    add_settings_field(
            'tbfe_elementor_layout_archive',
            __('Elementor Archive Layout', 'tbfe'),
            'tbfe_select_elementor_layout_archive',
            'tbfe_pluginPage_archive',
            'tbfe_pluginPage_section_archive'
    );
    add_settings_field(
            'tbfe_elementor_sidebar_archive',
            __('Elementor Archive Sidebar', 'tbfe'),
            'tbfe_select_elementor_sidebar_archive',
            'tbfe_pluginPage_archive',
            'tbfe_pluginPage_section_archive'
    );
    add_settings_field(
            'tbfe_elementor_footer_archive',
            __('Archive Footer', 'tbfe'),
            'tbfe_select_elementor_archive_footer',
            'tbfe_pluginPage_archive',
            'tbfe_pluginPage_section_archive'
    );
    // End Archive
    
    // 404
    register_setting('tbfe_pluginPage_404', 'tbfe_settings_404');
    add_settings_section(
            'tbfe_pluginPage_section_404',
            '',
            'tbfe_settings_section_callback',
            'tbfe_pluginPage_404'
    );
    add_settings_field(
            'tbfe_elementor_header_404',
            __('404 Header', 'tbfe'),
            'tbfe_select_elementor_404_header',
            'tbfe_pluginPage_404',
            'tbfe_pluginPage_section_404'
    );
    add_settings_field(
            'tbfe_elementor_404',
            __('Elementor 404 Page', 'tbfe'),
            'tbfe_select_elementor_404',
            'tbfe_pluginPage_404',
            'tbfe_pluginPage_section_404'
    );
    add_settings_field(
            'tbfe_elementor_footer_404',
            __('404 Footer', 'tbfe'),
            'tbfe_select_elementor_404_footer',
            'tbfe_pluginPage_404',
            'tbfe_pluginPage_section_404'
    );
    // End 404
}

function tbfe_select_elementor_page_header() {
    $options = get_option('tbfe_settings_page');
    $args = array( 'post_type' => 'elementor-header', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_page[tbfe_elementor_header_page]">
    <option value=""><?php esc_html_e('Select header', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
		$selected = ( isset( $options['tbfe_elementor_header_page'] ) && $options['tbfe_elementor_header_page'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}
function tbfe_select_elementor_page() {
    $options = get_option('tbfe_settings_page');
    $args = array( 'post_type' => 'elementor-page', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_page[tbfe_elementor_page]">
    <option value=""><?php esc_html_e('Select page', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
        $selected = ( isset( $options['tbfe_elementor_page'] ) && $options['tbfe_elementor_page'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}
function tbfe_select_elementor_page_footer() {
    $options = get_option('tbfe_settings_page');
    $args = array( 'post_type' => 'elementor-footer', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_page[tbfe_elementor_footer_page]">
    <option value=""><?php esc_html_e('Select footer', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
		$selected = ( isset( $options['tbfe_elementor_footer_page'] ) && $options['tbfe_elementor_footer_page'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}


function tbfe_select_elementor_post_header() {
    $options = get_option('tbfe_settings_post');
    $args = array( 'post_type' => 'elementor-header', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_post[tbfe_elementor_header_post]">
    <option value=""><?php esc_html_e('Select header', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
		$selected = ( isset( $options['tbfe_elementor_header_post'] ) && $options['tbfe_elementor_header_post'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}
function tbfe_select_elementor_post() {
    $options = get_option('tbfe_settings_post');
    $args = array( 'post_type' => 'elementor-post', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_post[tbfe_elementor_post]">
    <option value=""><?php esc_html_e('Select post', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
        $selected = ( isset( $options['tbfe_elementor_post'] ) && $options['tbfe_elementor_post'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}
function tbfe_select_elementor_post_footer() {
    $options = get_option('tbfe_settings_post');
    $args = array( 'post_type' => 'elementor-footer', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_post[tbfe_elementor_footer_post]">
    <option value=""><?php esc_html_e('Select footer', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
		$selected = ( isset( $options['tbfe_elementor_footer_post'] ) && $options['tbfe_elementor_footer_post'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}


function tbfe_select_elementor_archive_header() {
    $options = get_option('tbfe_settings_archive');
    $args = array( 'post_type' => 'elementor-header', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_archive[tbfe_elementor_header_archive]">
    <option value=""><?php esc_html_e('Select header', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
		$selected = ( isset( $options['tbfe_elementor_header_archive'] ) && $options['tbfe_elementor_header_archive'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}
function tbfe_select_elementor_archive() {
    $options = get_option('tbfe_settings_archive');
    $args = array( 'post_type' => 'elementor-archive', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_archive[tbfe_elementor_archive]">
    <option value=""><?php esc_html_e('Select page', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
        $selected = ( isset( $options['tbfe_elementor_archive'] ) && $options['tbfe_elementor_archive'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}
function tbfe_select_elementor_layout_archive() {
    $options = get_option('tbfe_settings_archive');
    ?>
    <select name="tbfe_settings_archive[tbfe_elementor_layout_archive]">
        <option value="boxed" <?php echo ( isset( $options['tbfe_elementor_layout_archive'] ) && $options['tbfe_elementor_layout_archive'] == 'boxed' )  ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Boxed', 'tbfe') ?></option>
        <option value="full" <?php echo ( isset( $options['tbfe_elementor_layout_archive'] ) && $options['tbfe_elementor_layout_archive'] == 'full' )  ? 'selected="selected"' : ''; ?> ><?php esc_html_e('Full Width', 'tbfe') ?></option>
    </select>
    <?php
}
function tbfe_select_elementor_sidebar_archive() {
    $options = get_option('tbfe_settings_archive');
    $args = array( 'post_type' => 'elementor-sidebar', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_archive[tbfe_elementor_sidebar_archive]">
    <option value=""><?php esc_html_e('Select page', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
        $selected = ( isset( $options['tbfe_elementor_sidebar_archive'] ) && $options['tbfe_elementor_sidebar_archive'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}
function tbfe_select_elementor_archive_footer() {
    $options = get_option('tbfe_settings_archive');
    $args = array( 'post_type' => 'elementor-footer', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_archive[tbfe_elementor_footer_archive]">
    <option value=""><?php esc_html_e('Select footer', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
		$selected = ( isset( $options['tbfe_elementor_footer_archive'] ) && $options['tbfe_elementor_footer_archive'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}


function tbfe_select_elementor_404_header() {
    $options = get_option('tbfe_settings_404');
    $args = array( 'post_type' => 'elementor-header', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_404[tbfe_elementor_header_404]">
    <option value=""><?php esc_html_e('Select header', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
		$selected = ( isset( $options['tbfe_elementor_header_404'] ) && $options['tbfe_elementor_header_404'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}
function tbfe_select_elementor_404() {
    $options = get_option('tbfe_settings_404');
    $args = array( 'post_type' => 'elementor-404', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_404[tbfe_elementor_404]">
    <option value=""><?php esc_html_e('Select 404 page', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
        $selected = ( isset( $options['tbfe_elementor_404'] ) && $options['tbfe_elementor_404'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}
function tbfe_select_elementor_404_footer() {
    $options = get_option('tbfe_settings_404');
    $args = array( 'post_type' => 'elementor-footer', 'post_status' => 'publish');
    $pages = get_posts($args);
    ?>
    <select name="tbfe_settings_404[tbfe_elementor_footer_404]">
    <option value=""><?php esc_html_e('Select footer', 'tbfe') ?></option>
    <?php
    foreach ($pages as $page) {
        $selected = ( isset( $options['tbfe_elementor_footer_404'] ) && $options['tbfe_elementor_footer_404'] == absint($page->ID) )  ? 'selected="selected"' : '';
        echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}


add_action('admin_init', 'tbfe_theme_import');
function tbfe_theme_import() {
    // after submit action
    if ( isset( $_POST['tbfe_theme_install']) && $_POST['tbfe_theme_install'] == 'true' && is_admin() ) {
        tbfe_plugin_fire();
        add_action( 'admin_notices', 'tbfe_theme_notice' );
    }
    if ( isset( $_POST['header-import']) && $_POST['header-import'] == 'true' && is_admin() ) {
        add_action( 'admin_notices', 'tbfe_theme_notice' );
    }
}
function tbfe_theme_notice() {
    if ( isset( $_POST['tbfe_theme_install']) && $_POST['tbfe_theme_install'] == 'true' && is_admin() ) {

        $current_theme_folder = !is_dir( WP_CONTENT_DIR . '/themes/the-blocks/' );
        if ( $current_theme_folder ) {
            ?>
            <div class="warning notice">
                <p><?php printf( esc_html__( 'There was a problem installing or activating the theme. Try it again or do it manually. %1$s Documentation how to install The Blocks theme manually. %2$s', 'tbfe' ), '<a href="' . esc_url("https://blocks-wp.com/blog/docs/theme-builder-for-elementor-documentation/") .'" target="_blank">', '</a>'  ); ?></p>
            </div>
        <?php } else { ?>
            <div class="updated notice">
                <p><?php esc_html_e( 'The theme has been installed, excellent!', 'tbfe' ); ?></p>
            </div>
        <?php 
        }
    } 
    if ( isset( $_POST['header-import']) && $_POST['header-import'] == 'true' && is_admin() ) {
        ?>
        <div class="updated notice">
            <p><?php esc_html_e( 'Demo layouts imported!', 'tbfe' ); ?></p>
        </div>
    <?php
    }
}

function tbfe_theme_info_page() {
  $current_theme = get_option('template');
  $our_theme = 'the-blocks';
    ?>
        <div class="tbfe-welcome">
            <h2><?php esc_html_e( 'Welcome to Theme Builder For Elementor', 'tbfe' ); ?></h2>
            <?php esc_html_e( 'Plugin to style your website with Elementor. Style your website design without any limitation.', 'tbfe' ); ?>
        </div>

    <?php if ( $current_theme != $our_theme ) { ?>
        <div class="tbfe-theme-install">
            <h3><?php esc_html_e( 'One more step', 'tbfe' ); ?></h3>
            <b><?php esc_html_e( 'Install and activate our theme - The Blocks', 'tbfe' ); ?></b>
            <p>
              <?php esc_html_e( 'Why The Blocks?', 'tbfe' ); ?><br />
              <?php esc_html_e( 'The Theme Builder For Elementor is built to work with any theme, however there are many ways how to develop the theme and we can not support all of them.', 'tbfe' ); ?><br />
              <?php esc_html_e( 'To ensure that this plugin works correctly, install and activate The Blocks theme', 'tbfe' ); ?><br />
            </p>
            <p>
              <?php esc_html_e( 'Main theme features:', 'tbfe' ); ?><br />
              <ul>
                <li> - <?php esc_html_e( 'Lightweight & safe - only 8kb size. It is super small and fast.', 'tbfe' ); ?></li>
                <li> - <?php esc_html_e( 'Coded with all WordPress coding standards', 'tbfe' ); ?></li>
                <li> - <?php esc_html_e( '100% suported by this plugin', 'tbfe' ); ?></li>
                <li> - <?php esc_html_e( 'Fully customizable with this plugin - Header, Footer, Posts, Pages, Archives...', 'tbfe' ); ?></li>
              </ul>
            </p>
            <p><?php esc_html_e( 'Click the button below to automatically install and activate The Blocks theme.', 'tbfe' ); ?><br /></p>
            <form action="" method="post">
              <input type="hidden" name="tbfe_theme_install" value="true">
              <?php submit_button('Install and activate', 'secondary'); ?>
            </form>
        </div>
    <?php } ?>
        <div class="tbfe-demo-import">
            <h3><?php esc_html_e( 'Demo Import', 'tbfe' ); ?></h3>
            <?php esc_html_e( 'Import our Elementor demo layouts (Elementor headers, footers, posts & pages, ...) is the easiest way to create your own design. Click the button below, to import demo layouts.', 'tbfe' ); ?><br />
            <form method="POST" action="">
                <input type="hidden" name="header-import" value="true" />
                <?php submit_button('Import Data', 'primary'); ?>

            </form>
        </div>
        <div class="tbfe-how-to">
            <h3><?php esc_html_e( 'How to?', 'tbfe' ); ?></h3>
            <?php esc_html_e( 'Using Theme Builder For Elementor is simple and easy.', 'tbfe' ); ?><br />
            <ul>
                <li>
                    <?php
                    /* translators: %1$s opening bold <b> tag, %2$s closing bold </b> tag */
                    printf( esc_html__( '1. Create and style section(s) with Elementor (or import with our demo importer) under %1$s Theme Builder - Header %2$s (Footer, 404 page...)', 'tbfe' ), '<b>', '</b>' );
                    ?>
                </li>
                <li>
                    <?php
                    /* translators: %1$s opening bold <b> tag, %2$s closing bold </b> tag */
                    printf( esc_html__( '2. Once the sections are created, go to %1$s Theme Builder - Settings %2$s, select tab (Post, Page, Archive...) where you want display the sections and set them.', 'tbfe' ), '<b>', '</b>' );
                    ?>
                </li>
                <li>
                    <?php esc_html_e( '3. Save your choices and check the result.', 'tbfe' ); ?>
                </li>
            </ul>
            <p><?php esc_html_e( 'Still not lucky? Visit our ', 'tbfe' ); ?><a href="<?php echo esc_url('https://blocks-wp.com/blog/docs/theme-builder-for-elementor-documentation/') ?>" target="_blank"><?php esc_html_e( "doucmentation page", "tbfe" ) ?></a></p>
        </div>
    <?php
}

function tbfe_settings_section_callback() {
   
}

function tbfe_options_page() {
    ?>
    <div class="wrap">
     
        <h2><?php esc_html_e( 'Theme Builder For Elementor Options', 'tbfe' ); ?></h2>
        <?php settings_errors(); ?>
         
        <?php $active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field(wp_unslash( $_GET[ 'tab' ] )) : 'display_welcome_tab'; ?>
         
        <h2 class="nav-tab-wrapper">
            <a href="?page=tbfe&tab=display_welcome_tab" class="nav-tab <?php echo $active_tab == 'display_welcome_tab' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Welcome', 'tbfe') ?></a>
            <a href="?page=tbfe&tab=display_page_options" class="nav-tab <?php echo $active_tab == 'display_page_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Pages', 'tbfe') ?></a>
            <a href="?page=tbfe&tab=display_posts_options" class="nav-tab <?php echo $active_tab == 'display_posts_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Posts', 'tbfe') ?></a>
            <a href="?page=tbfe&tab=display_archive_options" class="nav-tab <?php echo $active_tab == 'display_archive_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Archive', 'tbfe') ?></a>
            <a href="?page=tbfe&tab=display_404_options" class="nav-tab <?php echo $active_tab == 'display_404_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('404', 'tbfe') ?></a>
        </h2>
         
        <?php 
            if( $active_tab == 'display_welcome_tab' ) {
                tbfe_theme_info_page();
            } else {
        ?>
        <form action='options.php' method='post'>
           <?php
             if( $active_tab == 'display_page_options' ) {
                settings_fields( 'tbfe_pluginPage_page' );
                do_settings_sections( 'tbfe_pluginPage_page' );
            } else if( $active_tab == 'display_posts_options' ) {
                settings_fields( 'tbfe_pluginPage_post' );
                do_settings_sections( 'tbfe_pluginPage_post' );
            } else if( $active_tab == 'display_archive_options' ) {
                settings_fields( 'tbfe_pluginPage_archive' );
                do_settings_sections( 'tbfe_pluginPage_archive' );
            } else if( $active_tab == 'display_404_options' ) {
                settings_fields( 'tbfe_pluginPage_404' );
                do_settings_sections( 'tbfe_pluginPage_404' );
            }
            submit_button();
            echo '' . esc_html__( "Are you lost? Visit our ", "tbfe" ) . '<a href="' . esc_url('https://blocks-wp.com/blog/docs/theme-builder-for-elementor-documentation/') . '" target="_blank">' . esc_html__( "doucmentation page", "tbfe" ) . '</a>';
            ?>
             
        </form>
         <?php }  ?>
    </div><!-- /.wrap -->
    
    
    <?php
}
