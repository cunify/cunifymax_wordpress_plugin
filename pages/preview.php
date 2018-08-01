<?php

global $wp;

$id = (isset($_GET['id'])) ? $_GET['id'] : 0;

$context = $timber->get_context();

function previewBlocksDesign($ids, $context) {

    global $timber;

    $blocks = array();

    foreach ($ids as $id) {

        $design = $timber->get_post($id);

        if (isset($design->custom['twig'])) {
            $html = $timber->compile_string($design->custom['twig'], $context);
        } else {
            $html = '';
        }
        $id = $design->id;

        $data = new stdClass();
        $data->post_name = $design->post_name;
        $data->html = '<div id="block_' . $id . '" class="cx-block" data-block-id="' . $id . '" data-block-name="block_' . $id . '">' . $html . '</div>' . "\n\n";

        $blocks[] = $data;
    }

    return $blocks;
}

function previewDesign($id, $context) {


    global $timber;

    if ($id) {

        $design = $timber->get_post($id);

        $terms = array('json', 'settings', 'customset', 'predefined', 'records', 'positions');

        foreach ($terms as $term) {

            $term_content = (isset($design->$term)) ? $design->$term : '{}';

            if (is_array($term_content)) {
                $design->$term = $term_content;
            } elseif (cunifymax_json_validator($term_content)) {
                $design->$term = json_decode($term_content, true);
            } else {
                $design->$term = array();
            }
        }

        if (!empty($design->json)) {
            $context['css_json'] = $design->json;
        }

        if (is_array($design->records)) {
            $context = array_merge($context, $design->records);
        }

        if (!empty($design->settings)) {

            $new_setting = array();

            foreach ($design->settings as $setting_group_name => $setting) {
                foreach ($setting['fields'] as $setting_field_name => $field) {
                    $new_setting[$setting_group_name][$setting_field_name] = ($field['value'] <> '') ? $field['value'] : $field['default'];
                }
            }

            $context = array_merge($context, $new_setting);
        }

        if (!empty($design->positions)) {
            foreach ($design->positions as $key => $tmp_position) {
                $context['positions'][$key] = previewBlocksDesign($tmp_position, $context);
            }
        }

       $design->custom['json_arr'] = json_decode($design->custom['json'], true);

        $context['design'] = $design;
        $context['html'] = $timber->compile_string($design->custom['twig'], $context);
    } else {
        $context['html'] = '';
    }

    $context['frameworks'] = cunifymax_get_frameworks();
    $context['plugin_path'] = cunifymax_plugin_path();

    return $context;
}

$context = previewDesign($id, $context);

$templates = array('preview.twig');
$html = $timber->compile($templates, $context);

http_response_code(200);

echo $html;
exit;
