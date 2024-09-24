<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * The import
 */
add_action('admin_init', 'tbfe_import_demo_content');
function tbfe_import_demo_content() {
    if (isset($_POST['header-import']) && is_admin()) {
        // Download and parse the xml
        $xml_path = 'https://blocks-wp.com/wp-content/uploads/download//data.xml';
        $internal_errors = libxml_use_internal_errors(true);

        $dom = new DOMDocument;
        $old_value = null;
        if (function_exists('libxml_disable_entity_loader')) {
            $old_value = libxml_disable_entity_loader(true);
        }
        $success = $dom->loadXML(file_get_contents($xml_path));
        if (!is_null($old_value)) {
            libxml_disable_entity_loader($old_value);
        }

        if (!$success || isset($dom->doctype)) {
            return new WP_Error('SimpleXML_parse_error', __('There was an error when reading this WXR file', 'ebdi-kc'), libxml_get_errors());
        }

        $xml = simplexml_import_dom($dom);
        unset($dom);

        // halt if loading produces an error
        if (!$xml)
            return new WP_Error('SimpleXML_parse_error', __('There was an error when reading this WXR file', 'ebdi-kc'), libxml_get_errors());

        $wxr_version = $xml->xpath('/rss/channel/wp:wxr_version');
        if (!$wxr_version)
            return new WP_Error('WXR_parse_error', __('This does not appear to be a WXR file, missing/invalid WXR version number', 'ebdi-kc'));

        $wxr_version = (string) trim($wxr_version[0]);
        // confirm that we are dealing with the correct file format
        if (!preg_match('/^\d+\.\d+$/', $wxr_version))
            return new WP_Error('WXR_parse_error', __('This does not appear to be a WXR file, missing/invalid WXR version number', 'ebdi-kc'));

        $base_url = $xml->xpath('/rss/channel/wp:base_site_url');
        $base_url = (string) trim($base_url[0]);

        $namespaces = $xml->getDocNamespaces();
        if (!isset($namespaces['wp']))
            $namespaces['wp'] = 'http://wordpress.org/export/1.1/';
        if (!isset($namespaces['excerpt']))
            $namespaces['excerpt'] = 'http://wordpress.org/export/1.1/excerpt/';


        // Succesfully loaded?
        if ($xml !== FALSE) {
            // Loop through some items in the xml
            foreach ($xml->channel->item as $item) {
                // Let's start with creating the post itself
                $content = $item->children('http://purl.org/rss/1.0/modules/content/');
                $wp = $item->children($namespaces['wp']);
                $postCreated = array(
                    'post_title' => (string) $item->title,
                    'post_content' => (string) $content->encoded,
                    'post_status' => 'publish',
                    'post_type' => $wp->post_type, // Or "post" or some custom post type
                );

                if (!function_exists('post_exists')) {
                    require_once( ABSPATH . 'wp-admin/includes/post.php' );
                }

                // Check if the post exist...
                $my_post_id = post_exists($postCreated['post_title']);

                // ...if yes do nothing
                if (!$my_post_id) {
                    // Create the posts
                    $post_id = wp_insert_post($postCreated);

                    // Grab post meta from xml
                    $post = array(
                        'post_title' => (string) $item->title,
                        'guid' => (string) $item->guid,
                    );
                    foreach ($wp->postmeta as $meta) {
                        $post['postmeta'][] = array(
                            'key' => (string) $meta->meta_key,
                            'value' => (string) $meta->meta_value
                        );
                    }
                    // Save post meta from xml
                    foreach ($post['postmeta'] as $key => $value) {

                        if ('kc_data' === $value['key']) {
                            $values = unserialize($value['value']);
                        } else {
                            $values = $value['value'];
                        }
                        // Add the post options
                        update_post_meta($post_id, $value['key'], $values);
                    }
                }
            }

        } // XML false end
    } // if $_POST['submit'] end
}

function tbfe_plugin_fire() {
    $plugins = array(
        array('name' => 'the-blocks', 'path' => esc_url('http://blocks-wp.com/wp-content/uploads/download/the-blocks.zip')),
    );
    tbfe_get_plugins($plugins);
}

function tbfe_plugin_unpack($args, $target, $name) {
    $my_theme = wp_get_theme($name);
    if ($my_theme->exists())
        return;
    if ($zip = zip_open($target)) {
        while ($entry = zip_read($zip)) {
            $is_file = substr(zip_entry_name($entry), -1) == '/' ? false : true;
            $file_path = $args['path'] . zip_entry_name($entry);
            if ($is_file) {
                if (zip_entry_open($zip, $entry, "r")) {
                    $fstream = zip_entry_read($entry, zip_entry_filesize($entry));
                    file_put_contents($file_path, $fstream);
                    chmod($file_path, 0777);
                    //echo "save: ".$file_path."<br />";
                }
                zip_entry_close($entry);
            } else {
                if (zip_entry_name($entry)) {
                    mkdir($file_path);
                    chmod($file_path, 0777);
                    //echo "create: ".$file_path."<br />";
                }
            }
        }
        zip_close($zip);
    }
    if ($args['preserve_zip'] === false) {
        unlink($target);
    }
}
function tbfe_plugin_download($url, $path) {
    $content = file_get_contents($url);
    file_put_contents($path, $content);
}

function tbfe_plugin_activate($installer) {
    $current = get_option('template');
    $current_theme_folder = is_dir( WP_CONTENT_DIR . '/themes/the-blocks/' );
    if ($current != $installer && $current_theme_folder ) {
        include( trailingslashit( ABSPATH ) . 'wp-load.php');

        switch_theme($installer);
        return true;
    }
}

function tbfe_get_plugins($plugins) {
    $upload = wp_upload_dir();
    $args = array(
        'path' => WP_CONTENT_DIR . '/themes/',
        'preserve_zip' => false
    );

    foreach ($plugins as $plugin) {
        tbfe_plugin_download($plugin['path'], $args['path'].$plugin['name'].'.zip');
        tbfe_plugin_unpack($args, $args['path'].$plugin['name'].'.zip', $plugin['name']);
        tbfe_plugin_activate($plugin['name']);
    }
}