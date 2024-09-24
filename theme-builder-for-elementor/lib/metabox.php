<?php
add_action('add_meta_boxes', 'tbfe_pro_add_meta_box');

if (!function_exists('tbfe_pro_add_meta_box')) {

    /**
     * Add meta box to page screen
     *
     * This function handles the addition of variuos meta boxes to your page or post screens.
     * You can add as many meta boxes as you want, but as a rule of thumb it's better to add
     * only what you need. If you can logically fit everything in a single metabox then add
     * it in a single meta box, rather than putting each control in a separate meta box.
     *
     * @since 1.0.0
     */
    function tbfe_pro_add_meta_box() {
        add_meta_box('additional-page-metabox-options', esc_html__('Theme Builder Custom Options', 'tbfe'), 'tbfe_pro_metabox_controls', 'page', 'normal', 'default');
        add_meta_box('additional-page-metabox-options', esc_html__('Theme Builder Custom Options', 'tbfe'), 'tbfe_pro_metabox_controls', 'post', 'normal', 'default');
    }

}

if (!function_exists('tbfe_pro_metabox_controls')) {

    /**
     * Meta box render function
     *
     * @param  object $post Post object.
     * @since  1.0.0
     */
    function tbfe_pro_metabox_controls($post) {
        $meta = get_post_meta($post->ID);
        $screen = get_current_screen();
        $options = get_option('tbfe_settings_page');
        $options_post = get_option('tbfe_settings_post');

        $elementor_header_page = ( isset($meta['tbfe_elementor_header_page'][0]) && '' !== $meta['tbfe_elementor_header_page'][0] ) ? $meta['tbfe_elementor_header_page'][0] : '';
        $elementor_footer_page = ( isset($meta['tbfe_elementor_footer_page'][0]) && '' !== $meta['tbfe_elementor_footer_page'][0] ) ? $meta['tbfe_elementor_footer_page'][0] : '';
        $elementor_page = ( isset($meta['tbfe_elementor_page'][0]) && '' !== $meta['tbfe_elementor_page'][0] ) ? $meta['tbfe_elementor_page'][0] : '';

        $elementor_header_post = ( isset($meta['tbfe_elementor_header_post'][0]) && '' !== $meta['tbfe_elementor_header_post'][0] ) ? $meta['tbfe_elementor_header_post'][0] : '';
        $elementor_footer_post = ( isset($meta['tbfe_elementor_footer_post'][0]) && '' !== $meta['tbfe_elementor_footer_post'][0] ) ? $meta['tbfe_elementor_footer_post'][0] : '';
        $elementor_post = ( isset($meta['tbfe_elementor_post'][0]) && '' !== $meta['tbfe_elementor_post'][0] ) ? $meta['tbfe_elementor_post'][0] : '';

        wp_nonce_field('tbfe_pro_control_meta_box', 'tbfe_pro_control_meta_box_nonce'); // Always add nonce to your meta boxes!
        ?>
        <style type="text/css">
            .post_meta_extras p{margin: 20px 0;}
            .post_meta_extras label{display:block; margin-bottom: 10px;}
            .post_meta_extras .left_part{display: inline-block;width: 45%;margin-right: 30px; vertical-align: top;}
            .post_meta_extras .right_part{display: inline-block; width: 46%; vertical-align: top;}
            .post_meta_extras.tbfe-elementor {float: none; text-align: center; padding: 50px; background-color: #f1f1f1;}
        </style>

        <div class="post_meta_extras tbfe-elementor">
            <div class="left_part">
                <h3><?php esc_html_e('Elementor Options', 'tbfe'); ?></h3>
                <?php if (tbfe_pro_check_for_elementor() && (isset($options['tbfe_elementor_header_page']) && $options['tbfe_elementor_header_page'] != '' ) && ($screen->post_type == 'page')) { ?>
                    <p>
                        <label for="tbfe_pro_header">
                            <?php esc_html_e('Custom Elementor Header', 'tbfe'); ?>
                        </label>
                        <?php
                        $args = array('post_type' => 'elementor-header', 'post_status' => 'publish');
                        $pages = get_posts($args);
                        ?>
                        <select name="tbfe_elementor_header_page">
                            <option value=""><?php esc_html_e('Default from global options', 'tbfe') ?></option>
                            <?php
                            foreach ($pages as $page) {
                                $selected = selected($elementor_header_page, absint($page->ID));
                                echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
                            }
                            echo '</select>';
                            ?>
                    </p>
                <?php } ?>
                <?php if (tbfe_pro_check_for_elementor() && (isset($options['tbfe_elementor_page']) && $options['tbfe_elementor_page'] != '') && ($screen->post_type == 'page')) { ?>
                    <p>
                        <label for="tbfe_pro_page">
                            <?php esc_html_e('Custom Elementor Page', 'tbfe'); ?>
                        </label>
                        <?php
                        $args = array('post_type' => 'elementor-page', 'post_status' => 'publish');
                        $pages = get_posts($args);
                        ?>
                        <select name="tbfe_elementor_page">
                            <option value=""><?php esc_html_e('Default from global options', 'tbfe') ?></option>
                            <?php
                            foreach ($pages as $page) {
                                $selected = selected($elementor_page, absint($page->ID));
                                echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
                            }
                            echo '</select>';
                            ?>
                    </p>
                <?php } ?>
                <?php if (tbfe_pro_check_for_elementor() && (isset($options['tbfe_elementor_footer_page']) && $options['tbfe_elementor_footer_page'] != '' ) && ($screen->post_type == 'page')) { ?>
                    <p>
                        <label for="tbfe_pro_footer">
                            <?php esc_html_e('Custom Elementor Footer', 'tbfe'); ?>
                        </label>
                        <?php
                        $args = array('post_type' => 'elementor-footer', 'post_status' => 'publish');
                        $pages = get_posts($args);
                        ?>
                        <select name="tbfe_elementor_footer_page">
                            <option value=""><?php esc_html_e('Default from global options', 'tbfe') ?></option>
                            <?php
                            foreach ($pages as $page) {
                                $selected = selected($elementor_footer_page, absint($page->ID));
                                echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
                            }
                            echo '</select>';
                            ?>
                    </p>
                <?php } ?>
                <?php if (tbfe_pro_check_for_elementor() && (isset($options_post['tbfe_elementor_header_post']) && $options_post['tbfe_elementor_header_post'] != '' ) && ($screen->post_type == 'post')) { ?>
                    <p>
                        <label for="tbfe_pro_header">
                            <?php esc_html_e('Custom Elementor Header', 'tbfe'); ?>
                        </label>
                        <?php
                        $args = array('post_type' => 'elementor-header', 'post_status' => 'publish');
                        $pages = get_posts($args);
                        ?>
                        <select name="tbfe_elementor_header_post">
                            <option value=""><?php esc_html_e('Default from global options', 'tbfe') ?></option>
                            <?php
                            foreach ($pages as $page) {
                                $selected = selected($elementor_header_post, absint($page->ID));
                                echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
                            }
                            echo '</select>';
                            ?>
                    </p>
                <?php } ?>
                <?php if (tbfe_pro_check_for_elementor() && (isset($options_post['tbfe_elementor_post']) && $options_post['tbfe_elementor_post'] != '') && ($screen->post_type == 'post')) { ?>
                    <p>
                        <label for="tbfe_pro_post">
                            <?php esc_html_e('Custom Elementor Post', 'tbfe'); ?>
                        </label>
                        <?php
                        $args = array('post_type' => 'elementor-post', 'post_status' => 'publish');
                        $pages = get_posts($args);
                        ?>
                        <select name="tbfe_elementor_post">
                            <option value=""><?php esc_html_e('Default from global options', 'tbfe') ?></option>
                            <?php
                            foreach ($pages as $page) {
                                $selected = selected($elementor_post, absint($page->ID));
                                echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
                            }
                            echo '</select>';
                            ?>
                    </p>
                <?php } ?>
                <?php if (tbfe_pro_check_for_elementor() && (isset($options_post['tbfe_elementor_footer_post']) && $options_post['tbfe_elementor_footer_post'] != '' ) && ($screen->post_type == 'post')) { ?>
                    <p>
                        <label for="tbfe_pro_footer">
                            <?php esc_html_e('Custom Elementor Footer', 'tbfe'); ?>
                        </label>
                        <?php
                        $args = array('post_type' => 'elementor-footer', 'post_status' => 'publish');
                        $pages = get_posts($args);
                        ?>
                        <select name="tbfe_elementor_footer_post">
                            <option value=""><?php esc_html_e('Default from global options', 'tbfe') ?></option>
                            <?php
                            foreach ($pages as $page) {
                                $selected = selected($elementor_footer_post, absint($page->ID));
                                echo '<option value="' . absint($page->ID) . '" ' . $selected . ' >' . esc_html($page->post_title) . '</option>';
                            }
                            echo '</select>';
                            ?>
                    </p>
                <?php } ?>
            </div>
        </div>

        <?php
    }

}

add_action('save_post', 'tbfe_pro_save_metaboxes');

if (!function_exists('tbfe_pro_save_metaboxes')) {

    /**
     * Save controls from the meta boxes
     *
     * @param  int $post_id Current post id.
     * @since 1.0.0
     */
    function tbfe_pro_save_metaboxes($post_id) {
        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times. Add as many nonces, as you
         * have metaboxes.
         */
        if (!isset($_POST['tbfe_pro_control_meta_box_nonce']) || !wp_verify_nonce(sanitize_key($_POST['tbfe_pro_control_meta_box_nonce']), 'tbfe_pro_control_meta_box')) { // Input var okay.
            return $post_id;
        }

        // Check the user's permissions.
        if (isset($_POST['post_type']) && 'page' === $_POST['post_type']) { // Input var okay.
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
        }

        /*
         * If this is an autosave, our form has not been submitted,
         * so we don't want to do anything.
         */
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        /* Ok to save */

        // ELEMENTOR
        if (isset($_POST['tbfe_elementor_page'])) { // Input var okay.
            update_post_meta($post_id, 'tbfe_elementor_page', sanitize_text_field(wp_unslash($_POST['tbfe_elementor_page']))); // Input var okay.
        }
        if (isset($_POST['tbfe_elementor_header_page'])) { // Input var okay.
            update_post_meta($post_id, 'tbfe_elementor_header_page', sanitize_text_field(wp_unslash($_POST['tbfe_elementor_header_page']))); // Input var okay.
        }
        if (isset($_POST['tbfe_elementor_footer_page'])) { // Input var okay.
            update_post_meta($post_id, 'tbfe_elementor_footer_page', sanitize_text_field(wp_unslash($_POST['tbfe_elementor_footer_page']))); // Input var okay.
        }
        if (isset($_POST['tbfe_elementor_post'])) { // Input var okay.
            update_post_meta($post_id, 'tbfe_elementor_post', sanitize_text_field(wp_unslash($_POST['tbfe_elementor_post']))); // Input var okay.
        }
        if (isset($_POST['tbfe_elementor_header_post'])) { // Input var okay.
            update_post_meta($post_id, 'tbfe_elementor_header_post', sanitize_text_field(wp_unslash($_POST['tbfe_elementor_header_post']))); // Input var okay.
        }
        if (isset($_POST['tbfe_elementor_footer_post'])) { // Input var okay.
            update_post_meta($post_id, 'tbfe_elementor_footer_post', sanitize_text_field(wp_unslash($_POST['tbfe_elementor_footer_post']))); // Input var okay.
        }
    }

}