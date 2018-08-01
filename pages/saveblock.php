<?php

global $wp;
if (!is_user_logged_in() && !cunifymax_is_login_page()) {
    wp_redirect(wp_login_url(site_url($wp->request)));
    exit;
}

function cunifymax_get_position_array($position) {

    if (is_array($position)) {
        return $position;
    } elseif (cunifymax_json_validator($position)) {
        return json_decode($position, true);
    } else {
        return array();
    }
}

function cunifymax_save_post_position($section_post_id, $new_insert_id, $position_name) {

    $tmp_positions = get_post_meta($section_post_id, 'positions', true);

    $positions = cunifymax_get_position_array($tmp_positions);

    $positions[$position_name][] = $new_insert_id;

    update_post_meta($section_post_id, 'positions', json_encode($positions));

    return $positions;
}

function cunifymax_save_visibility() {


    global $wpdb;

    $return_data = array('is_new' => 1);

    $block_id = $_POST['block_id'];
    $active_page = $_POST['active_page'];
    $field = $_POST['field'];
    $value = $_POST['value'];
    $design_post_id = $_POST['design_post_id'];


    if ($design_post_id && $block_id) {

        if ($field === 'show_on_page' || $field === 'hide_on_page') {

            $page_visibility = get_post_meta($block_id, $field, true);
            $page_visibility_arr = cunifymax_json_to_array($page_visibility);

            $page_visibility_arr[] = $active_page;

            update_post_meta($block_id, $field, json_encode($page_visibility_arr, JSON_PRETTY_PRINT));
        } else {
            update_post_meta($block_id, $field, $value);
        }


        $return_data['message'] = 'Saved Successfully.';
        $return_data['status'] = 1;
    } else {
        $return_data['message'] = 'Saving failed.';
        $return_data['status'] = 0;
    }

    return $return_data;
}

function cunifymax_save_post() {

    global $wpdb;
    $return_data = array('is_new' => 1);
    $section_id = $_POST['section_id'];
    $position_name = $_POST['position_name'];

    $design_id = $_POST['insert_id'];
    $design_type = 'designer_themeblocks';

    if ($design_id) {

        $design = get_post($design_id);

        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;
        /*
         * new post data array
         */
        if (isset($design) && $design != null) {

            $args = array(
                'comment_status' => $design->comment_status,
                'ping_status' => $design->ping_status,
                'post_author' => $new_post_author,
                'post_content' => $design->post_content,
                'post_excerpt' => $design->post_excerpt,
                'post_name' => $design->post_name,
                'post_parent' => $design_id,
                'post_password' => $design->post_password,
                'post_status' => 'draft',
                'post_title' => $design->post_title,
                'post_type' => $design_type,
                'to_ping' => $design->to_ping,
                'menu_order' => $design->menu_order
            );

            /*
             * insert the post by wp_insert_post() function
             */

            $return_data['post_id'] = $new_post_id = wp_insert_post($args);

            /*
             * get all current post terms ad set them to the new post draft
             */

            $taxonomies = get_object_taxonomies($design->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
            foreach ($taxonomies as $taxonomy) {
                $design_terms = wp_get_object_terms($design_id, $taxonomy, array('fields' => 'slugs'));
                wp_set_object_terms($new_post_id, $design_terms, $taxonomy, false);
            }

            /*
             * duplicate all post meta just in two SQL queries
             */

            $design_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$design_id");
            if (count($design_meta_infos) != 0) {
                $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                foreach ($design_meta_infos as $meta_info) {
                    $meta_key = $meta_info->meta_key;
                    if ($meta_key == '_wp_old_slug')
                        continue;
                    $meta_value = addslashes($meta_info->meta_value);
                    $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
                }
                $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                $wpdb->query($sql_query);
            }


            $return_data['positions'] = cunifymax_save_post_position($section_id, $new_post_id, $position_name);

            cunifymax_modify_block_content_ref($new_post_id, $design_id, $section_id);

            $design = get_post($section_id);

            $return_data['message'] = 'Saved Successfully.';
            $return_data['status'] = 1;
            $return_data['design'] = cunifymax_prepare_post_json($design);
        }else {
            $return_data['message'] = 'Saving failed.';
            $return_data['status'] = 0;
        }
    } else {
        $return_data['message'] = 'Saving failed.';
        $return_data['status'] = 0;
    }

    return $return_data;
}

function cunifymax_modify_block_content_ref($new_block_id, $old_block_id, $parent_id) {

    $terms = array('css', 'js', 'json', 'predefined', 'customset', 'records', 'settings');
    $custom_terms = array('css', 'js', 'json');

    foreach ($terms as $term) {

        $parent_content = get_post_meta($parent_id, $term, true);
        $new_block_content = get_post_meta($new_block_id, $term, true);

        if ($term == 'css' || $term == 'js') {
            $parent_content = $parent_content . $new_block_content;
        } else {
            $parent_content = cunifymax_get_position_array($parent_content);
            $new_block_content = cunifymax_get_position_array($new_block_content);

            $parent_content = json_encode(array_merge($parent_content, $new_block_content));
        }

        update_post_meta($parent_id, $term, $parent_content);
    }

    foreach ($custom_terms as $term) {
        $tmp_content = get_post_meta($parent_id, $term, true);
        $content = str_replace('block_' . $old_block_id, 'block_' . $new_block_id, $tmp_content);
        update_post_meta($parent_id, $term, $content);
    }
}

$visibility = $_POST['visibility'];

if ($visibility) {
    $return_data = cunifymax_save_visibility();
} else {
    $return_data = cunifymax_save_post();
}

http_response_code(200);
header('Content-Type: application/json');
echo json_encode($return_data);
exit;
