<?php

global $wp;
if (!is_user_logged_in() && !cunifymax_is_login_page()) {
    wp_redirect(wp_login_url(site_url($wp->request)));
    exit;
}

function save_post($timber) {

    $return_data = array('is_new' => 1);

    $save_data = array();
    $save_data['ID'] = (isset($_POST['ID'])) ? (int) $_POST['ID'] : 0;
    $save_data['post_content'] = $_POST['description'];
    $save_data['post_title'] = $title = $_POST['title'];
    $save_data['post_type'] = 'designer_themepages';
    $save_data['post_name'] = $_POST['slug'];
    $save_data['post_parent'] = $_POST['parent_id'];
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

        $args = array(
            // Get all posts
            'posts_per_page' => 20,
            // Order by post date
            'orderby' => array(
                'date' => 'DESC'
        ));

        $parsed_pages = array();
        $themepages_args = array_merge($args, array('post_type' => 'designer_themepages'));

        $tmp_pages = $timber->get_posts($themepages_args);

        foreach ($tmp_pages as $key => $tmp_page) {
            $parsed_pages[] = cunifymax_prepare_post_json($tmp_page);
        }

        $return_data['pages'] = $parsed_pages;

        http_response_code(200);
        $return_data['message'] = 'Saved Successfully.';
        $return_data['post_id'] = $design_id;
        $return_data['status'] = 1;
    }

    return $return_data;
}

function post_save_processing($design_id) {

    $tags = explode(',', $_POST['keywords']);

    wp_set_post_tags($design_id, $tags, true);
    unset($_POST['keywords']);

    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            update_post_meta($design_id, $key, $value);
        } else {
            $value_str = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $value);
            update_post_meta($design_id, $key, $value_str);
        }
    }
}

$return_data = save_post($timber);

http_response_code(200);
header('Content-Type: application/json');
echo json_encode($return_data);
exit;
