<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function cunifymax_is_login_page()
{
    return in_array($GLOBALS['pagenow'], array('wp-login.php'));
}

function cunifymax_json_to_array($records)
{

    if (is_array($records)) {
        return $records;
    } elseif (cunifyadx_json_validator($records)) {
        return json_decode($records, true);
    } else {
        return array();
    }
}

function cunifymax_json_validator($data = null)
{

    if (!empty($data)) {

        @json_decode($data);

        return (json_last_error() === JSON_ERROR_NONE);
    }

    return false;
}

function cunifymax_load_designer_wrapper($designer_url, $id)
{
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $designer_url . '?id=' . $id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function cunifymax_get_frameworks()
{

    $path = cunifymax_plugin_path();

    $frameworks = json_decode(file_get_contents($path . '/frameworks.json'));

    return $frameworks;
}

function cunifymax_fetch_cunifymax_apps()
{

    $structure = array();

    $apps_path = cunifymax_plugin_path() . 'apps/';

    $app_files = scandir($apps_path);

    foreach ($app_files as $key => $app_file) {

        if ($app_file != '.' && $app_file != '..' && is_dir($apps_path)) {

            $manifest_path = $apps_path . $app_file . '/manifest.json';

            if (file_exists($manifest_path)) {

                $app_json = json_decode(file_get_contents($manifest_path), true);
                $structure[$app_file] = $app_json;

                $tables_path = $apps_path . '/' . $app_file . '/tables/';
                $table_files = scandir($tables_path);
                foreach ($table_files as $key => $table_file) {
                    if ($table_file != '.' && $table_file != '..') {

                        $table_json_path = $tables_path . $table_file;
                        $table_json = json_decode(file_get_contents($table_json_path), true);
                        $table_json['post_type'] = $app_json['alias'] . '_' . $table_json['alias'];

                        $structure[$app_file]['tables'][$table_json['alias']] = $table_json;
                    }
                }
            }
        }
    }

    return $structure;
}

function cunifymax_curl_call($params, $image_source)
{

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $image_source . "?" . http_build_query($params));

// in real life you should use something like:
    // curl_setopt($ch, CURLOPT_POSTFIELDS,
    //          http_build_query(array('postvar1' => 'value1')));
    // receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);

    curl_close($ch);

    return json_decode($server_output, true);
}

function cunifymax_load_menu()
{

    $apps_path = cunifymax_plugin_path() . 'apps/';
    $app_files = scandir($apps_path);
    foreach ($app_files as $key => $app_file) {
        if ($app_file != '.' && $app_file != '..' && is_dir($apps_path)) {

            $structure_arr = array();
            $mainfest_path = $apps_path . $app_file . '/manifest.json';
            $mainfest = json_decode(file_get_contents($mainfest_path), true);

            $app_name_arr = explode('.', $app_file);
            $app_name = $app_name_arr[0];

            $table_path = $apps_path . $app_file . '/tables/';
            $table_files = scandir($table_path);

            foreach ($table_files as $key => $table_file) {
                if ($table_file != '.' && $table_file != '..' && strpos($table_file, '.json')) {

                    $clr_table_path = $table_path . $table_file;
                    $table_name_arr = explode('.', $table_file);
                    $table_name = $table_name_arr[0];
                    $slug = str_replace(' ', '_', strtolower($app_name . '_' . $table_name));

                    $structure = json_decode(file_get_contents($clr_table_path), true);
                    $structure['slug'] = $slug;
                    $structure_arr[] = $structure;
                }
            }

            usort($structure_arr, function ($a, $b) {
                return $a['position'] <=> $b['position'];
            });

            add_menu_page(
                $mainfest['title'], $mainfest['title'], 'manage_options', 'edit.php?post_type=' . $structure_arr[0]['slug'], '', $mainfest['icon'], $mainfest['position']
            );

            $main_slug = $structure_arr[0]['slug'];
            //unset($structure_arr[0]);

            foreach ($structure_arr as $key => $structure) {
                add_submenu_page('edit.php?post_type=' . $main_slug, $structure['title'], $structure['title'], 'manage_options', 'edit.php?post_type=' . $structure['slug'], '');
            }
        }
    }
}

add_action('admin_menu', 'cunifymax_load_menu');

function cunifymax_load_apps()
{

    $apps_path = cunifymax_plugin_path() . 'apps/';
    $app_files = scandir($apps_path);

    foreach ($app_files as $key => $app_file) {
        if ($app_file != '.' && $app_file != '..' && is_dir($apps_path)) {

            $app_name_arr = explode('.', $app_file);
            $app_name = $app_name_arr[0];

            $table_path = $apps_path . $app_file . '/tables/';
            $table_files = scandir($table_path);

            foreach ($table_files as $key => $table_file) {
                if ($table_file != '.' && $table_file != '..' && strpos($table_file, '.json')) {

                    $clr_table_path = $table_path . $table_file;
                    $table_name_arr = explode('.', $table_file);
                    $table_name = $table_name_arr[0];

                    cunifymax_process_json($clr_table_path, $app_name, $table_name);
                }
            }
        }
    }

    $apps = $apps_path;
}

function cunifymax_process_json($clr_table_path, $app_name, $table_name)
{

    $structure = json_decode(file_get_contents($clr_table_path), true);

    cunifymax_register_post_type($structure, $app_name, $table_name);
}

function cunifymax_register_post_type($structure, $app_name, $table_name)
{

    $singular = $structure['singular'];
    $plural = $structure['plural'];
    $slug = str_replace(' ', '_', strtolower($app_name . '_' . $table_name));

    $labels = array(
        'name' => $plural,
        'singular_name' => $singular,
        'add_new' => 'Add New ' . $singular,
        'add_new_item' => 'Add New ' . $singular,
        'edit' => 'Edit',
        'edit_item' => 'Edit ' . $singular,
        'new_item' => 'New ' . $singular,
        'view' => 'View ' . $singular,
        'view_item' => 'View ' . $singular,
        'search_term' => 'Search ' . $plural,
        'parent' => 'Parent ' . $singular,
        'not_found' => 'No ' . $plural . ' found',
        'not_found_in_trash' => 'No ' . $plural . ' in Trash',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'show_in_nav_menus' => true,
        'show_ui' => true,
        'show_in_menu' => false,
        //'show_in_menu' => 'cunifymax-' . $app_name,
        'show_in_admin_bar' => false,
        'menu_position' => $structure['position'],
        'menu_icon' => 'dashicons-businessman',
        'can_export' => true,
        'delete_with_user' => false,
        'hierarchical' => false,
        'has_archive' => true,
        'query_var' => true,
        'capability_type' => 'post',
        'map_meta_cap' => true,
        // 'capabilities' => array(),
        'rewrite' => array(
            'slug' => $slug,
            'with_front' => true,
            'pages' => true,
            'feeds' => true,
        ),
        'supports' => array(
            'thumbnail',
            'comments',
        ),
    );

    register_post_type($slug, $args);
}

add_action('init', 'cunifymax_load_apps');
