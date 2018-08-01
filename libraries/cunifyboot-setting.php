<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function cunifymax_add_metabox() {

    $tmp_fields = array();
    $post_type = get_post_type();
    $post_type_arr = explode('_', $post_type);
    $clr_table_path = cunifymax_plugin_path() . 'apps/' . $post_type_arr[0] . '/tables/' . $post_type_arr[1] . '.json';

    $structure = json_decode(file_get_contents($clr_table_path), true);

    foreach ($structure['fields'] as $key => $field) {
        $tmp_fields[$field['name']] = $field;
    }

    foreach ($structure['groups'] as $key => $group) {

        $position = 'normal';
        $priority = 'high';
        $identifier = $post_type . '_' . $group['alias'];

        $callback_args = array(
            'post_type' => $post_type,
            'identifier' => $identifier,
            'group' => $group,
            'fields' => $tmp_fields
        );

        if (isset($structure['zones']['right']) && in_array($group['alias'], $structure['zones']['right']['groups'])) {
            $position = 'side';
            $priority = 'low';
        }

        add_meta_box(
                'cunifymax_meta_' . $identifier, __($group['title']), 'cunifymax_meta_callback', $post_type, $position, $priority, $callback_args
        );
        
        
        
    }
}

add_action('add_meta_boxes', 'cunifymax_add_metabox');

function cunifymax_meta_callback($post, $callback_args) {
 
    $cunifymax_stored_meta = get_post_meta($post->ID);
   
    $identifier = $callback_args['args']['identifier'];
    $post_type = $callback_args['args']['post_type'];
    $fields = $callback_args['args']['fields'];
    $group = $callback_args['args']['group'];

    wp_nonce_field(basename(__DIR__), 'cunifymax_' . $post_type . '_nonce');
 
      
    foreach ($group['fields'] as $key => $group_field) {
        if ($field <> 'title' && $field <> 'description') {

            $field = $fields[$group_field];

            switch ($field['input']) {
                case 'text':
                    cunifymax_get_field_text($field, $identifier, $cunifymax_stored_meta);
                    break;
                case 'textarea':
                    cunifymax_get_field_textarea($field, $identifier, $cunifymax_stored_meta);
                    break;
                case 'editor':
                    cunifymax_get_field_editor($field, $identifier, $cunifymax_stored_meta);
                    break;
                case 'gallery':
                    cunifymax_get_field_gallery($field, $identifier, $cunifymax_stored_meta);
                    break;
                case 'map':
                    cunifymax_get_field_map($field, $identifier, $cunifymax_stored_meta);
                    break;

                default:
                    break;
            }
        }
    }
}
