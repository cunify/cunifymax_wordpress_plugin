<?php

global $wp;
if (!is_user_logged_in() && !cunifymax_is_login_page()) {
    wp_redirect(wp_login_url(site_url($wp->request)));
    exit;
}

function cunifymax_delete_block_from_position($design_post_id, $block_id) {

    $found_block = false;

    $tmp_positions = get_post_meta($design_post_id, 'positions', true);

    $positions = ($tmp_positions <> '' && $tmp_positions <> '{}' && cunifymax_json_validator($tmp_positions) ) ? json_decode($tmp_positions, true) : array();

    foreach ($positions as $position_name => $blocks) {
        foreach ($blocks as $key => $block) {

            if ($block == $block_id) {
                $found_block = true;
                unset($positions[$position_name][$key]);
            }
        }
    }

    update_post_meta($design_post_id, 'positions', json_encode($positions));

    if ($found_block) {
        $return_data['message'] = 'Block Successfully Deleted.';
        $return_data['status'] = 1;
    } else {

        $return_data['message'] = 'No Block Was found.';
        $return_data['status'] = 0;
    }

     wp_delete_post($block_id);

    return $return_data;
}

$block_id = $_POST['block_id'];
$design_post_id = $_POST['design_post_id'];

$return_data = cunifymax_delete_block_from_position($design_post_id, $block_id);

http_response_code(200);
header('Content-Type: application/json');
echo json_encode($return_data);
exit;
