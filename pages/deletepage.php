<?php

global $wp;
if (!is_user_logged_in() && !cunifymax_is_login_page()) {
    wp_redirect(wp_login_url(site_url($wp->request)));
    exit;
}

function cunifymax_delete_page($design_post_id, $page_id, $timber) {

    $return_data = array();
    $found_block = true;

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

    if ($found_block) {
        $return_data['message'] = 'Block Successfully Deleted.';
        $return_data['status'] = 1;
    } else {

        $return_data['message'] = 'No Block Was found.';
        $return_data['status'] = 0;
    }

    wp_delete_post($page_id);

    return $return_data;
}

$page_id = $_POST['page_id'];
$design_post_id = $_POST['design_post_id'];

$return_data = cunifymax_delete_page($design_post_id, $page_id, $timber);

http_response_code(200);
header('Content-Type: application/json');
echo json_encode($return_data);
exit;
