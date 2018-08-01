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

function cunifymax_save_section() {

    $section_id = $_POST['section_id'];
    $insert_id = $_POST['insert_id'];
    $alias = $_POST['alias'];

    $tmp_section_positions = array();
    $return_data = array('is_new' => 1);

    $section_positions = get_post_meta($section_id, 'positions', true);
    $inserted_positions = get_post_meta($insert_id, 'positions', true);

    $section_positions = cunifymax_get_position_array($section_positions);
    $inserted_positions = cunifymax_get_position_array($inserted_positions);

    $post_type = ($alias == 'sections') ? 'designer_sectblocks' : 'designer_themeblocks';

    foreach ($inserted_positions as $position_name => $position_ids) {
        $tmp_section_positions[$position_name] = array();
        foreach ($position_ids as $key => $position_id) {
            $block_post_id = cunifymax_save_post($position_id, $section_id, $post_type);
            if ($block_post_id) {
                $tmp_section_positions[$position_name][] = $block_post_id;
            }
        }
    }

    $section_positions = array_merge($section_positions, $tmp_section_positions);
    update_post_meta($section_id, 'positions', json_encode($section_positions));

    $post = get_post($section_id);
    $insert_post = get_post($insert_id);
    $return_data['post'] = cunifymax_prepare_post_json($post);
    $return_data['insert_post'] = cunifymax_prepare_post_json($insert_post);

    if (!empty($tmp_section_positions)) {
        $return_data['positions'] = $section_positions;
        $return_data['message'] = 'Saved Successfully.';
        $return_data['status'] = 1;
    } else {
        $return_data['message'] = 'Saving failed.';
        $return_data['status'] = 0;
    }

    return $return_data;
}

function cunifymax_save_post($insert_id, $parent_id, $post_type) {

    global $wpdb;

    $post_id = $insert_id;

    if ($post_id) {

        $post = get_post($post_id);

        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;
        /*
         * new post data array
         */
        if (isset($post) && $post != null) {

            $args = array(
                'comment_status' => $post->comment_status,
                'ping_status' => $post->ping_status,
                'post_author' => $new_post_author,
                'post_content' => $post->post_content,
                'post_excerpt' => $post->post_excerpt,
                'post_name' => $post->post_name,
                'post_parent' => $parent_id,
                'post_password' => $post->post_password,
                'post_status' => 'draft',
                'post_title' => $post->post_title,
                'post_type' => $post_type,
                'to_ping' => $post->to_ping,
                'menu_order' => $post->menu_order
            );

            /*
             * insert the post by wp_insert_post() function
             */

            $return_data['post_id'] = $new_post_id = wp_insert_post($args);

            /*
             * get all current post terms ad set them to the new post draft
             */

            $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
            }

            /*
             * duplicate all post meta just in two SQL queries
             */

            $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
            if (count($post_meta_infos) != 0) {
                $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                foreach ($post_meta_infos as $meta_info) {
                    $meta_key = $meta_info->meta_key;
                    if ($meta_key == '_wp_old_slug')
                        continue;
                    $meta_value = addslashes($meta_info->meta_value);
                    $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
                }
                $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                $wpdb->query($sql_query);
            }
        }

        cunifymax_modify_block_content_ref($new_post_id, $post_id, $parent_id);

        return $new_post_id;
    }

    return false;
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

$return_data = cunifymax_save_section();

http_response_code(200);
header('Content-Type: application/json');
echo json_encode($return_data);
exit;
