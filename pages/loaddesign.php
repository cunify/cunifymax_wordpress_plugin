<?php

//wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
//wp_register_script('cunifymax_jquery-ui-core', site_url() . '/wp-includes/js/jquery/ui/core.min.js', array('jquery'));

global $wp;

$id = (isset($_GET['id'])) ? $_GET['id'] : 0;
$active_page = (isset($_GET['active_page'])) ? $_GET['active_page'] : 0;

if (!is_user_logged_in() && !cunifymax_is_login_page()) {
    wp_redirect(wp_login_url(site_url($wp->request)));
    exit;
}

function loadBlocksDesign($ids, $context, $active_page)
{

    global $timber;

    $blocks = array();

    foreach ($ids as $id) {

        $block = $timber->get_post($id);

        if (isset($block->custom['twig'])) {
            $html = $timber->compile_string($block->custom['twig'], $context);
        } else {
            $html = '';
        }

        $id = $block->id;

        $show_on_page = cunifymax_json_to_array($block->show_on_page);
        $hide_on_page = cunifymax_json_to_array($block->hide_on_page);

        $is_hidden = '';
        if ($block->visibility === 'hide') {
            $is_hidden = 'is_hidden';
        }

        if (!empty($show_on_page)) {
            $is_hidden = 'is_hidden';
        }

        if (in_array($active_page, $show_on_page)) {
            $is_hidden = '';
        }

        if (in_array($active_page, $hide_on_page)) {
            $is_hidden = 'is_hidden';
        }

        $data = new stdClass();
        $data->post_name = $block->post_name;
        $data->html = '<div id="block_' . $id . '" class="cx-block ' . $is_hidden . '" data-block-id="' . $id . '" data-block-name="block_' . $id . '">'
            . $html
            . '</div>' . "\n\n";

        $blocks[] = $data;
    }

    return $blocks;
}

function loadDesign($id, $active_page)
{

    global $timber;

    $context = array();
    $return_data = array();

    if ($id) {

        $design = $timber->get_post($id);

        $terms = array(
            'json',
            'settings',
            'customset',
            'predefined',
            'records',
            'positions',
            'zones',
        );

        foreach ($terms as $term) {

            $term_content = (isset($design->$term)) ? $design->$term : '{}';

            $design->$term = cunifymax_json_to_array($term_content);
        }

        if (!empty($design->json)) {
            $context['css_json'] = $design->json;
        }

        if ($design->post_type == 'designer_themes') {
            $design->custom['twig'] = file_get_contents(cunifymax_plugin_path() . 'pages/views/design-modal-code-twig.twig');
        }

        if (is_array($design->records)) {
            $context = array_merge($context, $design->records);
        }

        if (!empty($design->settings)) {

            $new_setting = array();

            foreach ($design->settings as $setting_group_name => $setting) {
                foreach ($setting['fields'] as $setting_field_name => $field) {
                    $new_setting[$setting_group_name][$setting_field_name] = ($field['value'] != '') ? $field['value'] : $field['default'];
                }
            }

            $context = array_merge($context, $new_setting);
        }

        if (!empty($design->positions)) {
            foreach ($design->positions as $key => $tmp_position) {
                $context['positions'][$key] = loadBlocksDesign($tmp_position, $context, $active_page);
            }
        }

        $tmp_design = cunifymax_prepare_post_json($design);

        $return_data['design'] = $context['design'] = $tmp_design;
        $return_data['css'] = $design->custom['css'];
        $return_data['js'] = $design->custom['js'];

        $return_data['html'] = $timber->compile_string($design->custom['twig'], $context);
    } else {
        $templates = array('design-modal-code-twig.twig');
        $return_data['html'] = $timber->compile($templates, $context);
    }

    return $return_data;
}

$return_data = loadDesign($id, $active_page);

http_response_code(200);

header('Content-Type: application/json');
echo json_encode($return_data);
exit;
