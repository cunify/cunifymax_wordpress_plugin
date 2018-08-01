<?php

global $wp;
if (!is_user_logged_in() && !cunifyadx_is_login_page()) {
    wp_redirect(wp_login_url(site_url($wp->request)));
    exit;
}

function save_post() {

    $return_data = array('is_new' => 1);

    $save_data = array();
    $save_data['ID'] = (isset($_POST['ID'])) ? (int) $_POST['ID'] : 0;
    $save_data['post_content'] = $_POST['description'];
    $save_data['post_title'] = $title = $_POST['title'];
    $save_data['post_type'] = $_POST['type'];
    $save_data['post_name'] = $_POST['alias'];
    $save_data['post_status'] = 'publish';
    $save_data['comment_status'] = 'open';
    $save_data['ping_status'] = 'open';

    $design_id = wp_insert_post($save_data);

    if ($save_data['ID']) {
        $return_data['is_new'] = 0;
    }

    if (0 === $design_id || $design_id instanceof WP_Error) {
        $return_data['message'] = 'Saving failed.';
        $return_data['status'] = 0;
    } else {

        post_save_processing($design_id);

        if (!isset($_POST['ID']) || $_POST['ID'] == '') {
            clone_block_posts($design_id);
        }

        http_response_code(200);
        $return_data['message'] = 'Saved Successfully.';
        $return_data['post_id'] = $design_id;
        $return_data['status'] = 1;
    }

    return $return_data;
}

function post_save_processing($design_id) {

    $tags = explode(',', $_POST['tags']);

    wp_set_post_tags($design_id, $tags, true);
    unset($_POST['tags']);

    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            update_post_meta($design_id, $key, $value);
        } else {
            $value_str = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $value);
            update_post_meta($design_id, $key, $value_str);
        }
    }

    if (!empty($_FILES)) {

        foreach ($_FILES as $field_name => $file) {

            if (isset($_FILES['featured_image'])) {

                $file = $_FILES['featured_image'];

                $attachment_id = save_attachment($design_id, $file);

                if ($attachment_id) {
                    update_post_meta($design_id, '_thumbnail_id', $attachment_id);
                }
            }
        }
    }
}

function clone_block_posts($design_id) {

    $tmp_positions = get_post_meta($design_id, 'positions', true);
    $tmp_css = get_post_meta($design_id, 'css', true);
    $tmp_js = get_post_meta($design_id, 'js', true);
    $tmp_json = get_post_meta($design_id, 'json', true);

    $positions = ($tmp_positions <> '' && $tmp_positions <> '{}' && cunifyadx_json_validator($tmp_positions) ) ? json_decode($tmp_positions, true) : array();

    foreach ($positions as $position_name => $blocks) {
        foreach ($blocks as $block_key => $block_id) {

            $design = get_post($block_id);

            unset($design->ID);

            $new_post_id = wp_insert_post($design);

            // Copy post metadata
            $data = get_post_custom($block_id);
            foreach ($data as $key => $values) {
                foreach ($values as $value) {
                    add_post_meta($new_post_id, $key, $value);
                }
            }

            $positions[$position_name][$block_key] = $new_post_id;

            $tmp_css = str_replace('block_' . $block_id, 'block_' . $new_post_id, $tmp_css);
            $tmp_js = str_replace('block_' . $block_id, 'block_' . $new_post_id, $tmp_js);
            $tmp_json = str_replace('block_' . $block_id, 'block_' . $new_post_id, $tmp_json);
        }
    }

    update_post_meta($design_id, 'positions', json_encode($positions));
    update_post_meta($design_id, 'css', $tmp_css);
    update_post_meta($design_id, 'js', $tmp_js);
    update_post_meta($design_id, 'json', $tmp_json);
}

function save_attachment($design_id, $file = array()) {

    $attachment_id = '';

    require_once( ABSPATH . 'wp-admin/includes/admin.php' );

    $file_return = wp_handle_upload($file, array('test_form' => false));

    if (isset($file_return['error']) || isset($file_return['upload_error_handler'])) {

        return false;
    } else {

        $args = array(
            'post_type' => 'attachment',
            'numberposts' => -1,
            'post_content' => 'design',
            'post_status' => null,
            'post_parent' => $design_id
        );

        $attachments = get_posts($args);

        if ($attachments) {
            foreach ($attachments as $key => $attachment) {

                if (!$key) {
                    $attachment_id = $attachment->ID;

                    $meta = get_post_meta($attachment_id, '_wp_attachment_metadata', true);

                    $upload_path = wp_upload_dir();
                    $file_upload_parts = explode('/', $meta['file']);
                    array_pop($file_upload_parts);
                    $file_upload = implode('/', $file_upload_parts);

                    wp_delete_file($upload_path['basedir'] . '/' . $meta['file']);

                    foreach ($meta['sizes'] as $key => $meta_single) {
                        wp_delete_file($upload_path['basedir'] . '/' . $file_upload . '/' . $meta_single['file']);
                    }
                } else {
                    wp_delete_attachment($attachment->id, true);
                }
            }
        }

        $filename = $file_return['file'];

        $attachment = array(
            'ID' => $attachment_id,
            'post_mime_type' => $file_return['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content' => 'design',
            'post_status' => 'inherit',
            'post_parent' => $design_id,
            'guid' => $file_return['url']
        );

        $attachment_id = wp_insert_attachment($attachment, $file_return['url']);

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $filename);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        if ((int) $attachment_id) {
            return $attachment_id;
        }
    }

    return false;
}

$return_data = save_post();

header('Content-Type: application/json');
echo json_encode($return_data);
exit;
