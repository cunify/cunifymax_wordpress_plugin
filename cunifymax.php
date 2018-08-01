<?php

/*
 * Plugin Name: CUnify Max
 * Plugin URI: http://www.cunify.com
 * Description: Component Unify : Integrate twig components that are sharable between different CMS and for easy website development.
 * Author: Dedan Irungu
 * Author URI: http://www.cunify.com
 * Version: 0.0.1
 * License: GPLv3
 */

//Exit if accessed directory
if (!defined('ABSPATH')) {
    exit;
}

$image_source = 'http://img.cunify.com/api';
$designer_url = 'http://www.cunify.com/designer';
$api_url = 'http://www.cunify.com/api';
$preview_url = 'http://www.cunify.com/preview';

if (file_exists($composer_autoload = __DIR__ . '/vendor/autoload.php') /* check in self */ || file_exists($composer_autoload = WP_CONTENT_DIR . '/vendor/autoload.php') /* check in wp-content */ || file_exists($composer_autoload = plugin_dir_path(__FILE__) . 'vendor/autoload.php') /* check in plugin directory */ || file_exists($composer_autoload = get_stylesheet_directory() . '/vendor/autoload.php') /* check in child theme */ || file_exists($composer_autoload = get_template_directory() . '/vendor/autoload.php') /* check in parent theme */
) {
    require_once $composer_autoload;
}

if (file_exists(plugin_dir_path(__FILE__) . 'libraries/cunifyboot-override.php')) {
    require_once plugin_dir_path(__FILE__) . 'libraries/cunifyboot-override.php';
}

require_once plugin_dir_path(__FILE__) . 'libraries/cunifyboot-system.php';
require_once plugin_dir_path(__FILE__) . 'libraries/cunifyboot-fields.php';
require_once plugin_dir_path(__FILE__) . 'libraries/cunifyboot-metabox.php';
require_once plugin_dir_path(__FILE__) . 'libraries/cunifyboot-settings.php';
require_once plugin_dir_path(__FILE__) . 'Cunifymax/VirtualPages/cunifyboot-virtual-pages.php';

cunifymax_virtual_custom_page($controller, 'Edit Design', '', 'design.php', '/cx-design');
cunifymax_virtual_custom_page($controller, 'Preview', '', 'preview.php', '/cx-preview');
cunifymax_virtual_custom_page($controller, 'Fetch Images', '', 'fetchimages.php', '/cx-fetchimages');
cunifymax_virtual_custom_page($controller, 'Fetch', '', 'fetch.php', '/cx-fetch');
cunifymax_virtual_custom_page($controller, 'Save Theme', '', 'savetheme.php', '/cx-savetheme');
cunifymax_virtual_custom_page($controller, 'Save Block', '', 'saveblock.php', '/cx-saveblock');
cunifymax_virtual_custom_page($controller, 'Save Page', '', 'savepage.php', '/cx-savepage');
cunifymax_virtual_custom_page($controller, 'Save Section', '', 'savesection.php', '/cx-savesection');
cunifymax_virtual_custom_page($controller, 'Save Upload', '', 'saveupload.php', '/cx-saveupload');
cunifymax_virtual_custom_page($controller, 'Save Design', '', 'savedesign.php', '/cx-savedesign');
cunifymax_virtual_custom_page($controller, 'Load Design', '', 'loaddesign.php', '/cx-loaddesign');
cunifymax_virtual_custom_page($controller, 'Delete Block', '', 'deleteblock.php', '/cx-deleteblock');
cunifymax_virtual_custom_page($controller, 'Delete Page', '', 'deletepage.php', '/cx-deletepage');
cunifymax_virtual_custom_page($controller, 'Select Theme', '', 'themeselection.php', '/cx-themeselection');


function cunifymax_plugin_path()
{
    return plugin_dir_path(__FILE__);
}

$timber = new \Timber\Timber();

function cunifymax_virtual_custom_page($controller, $title, $content, $template, $url)
{

    $new_url = ltrim($url, '/');

    if (cunifymax_validate_current_page($new_url)) {

        add_filter('template_include', function () use ($template) {

            if (file_exists(get_stylesheet_directory() . '/' . $template)) {
                return get_stylesheet_directory() . '/' . $template;
            } else {
                return plugin_dir_path(__FILE__) . 'pages/' . $template;
            }
        });

        $tmp_template = $template;

        add_action('cunifymax_virtual_pages', function ($controller) use ($tmp_template, $title, $content, $template, $url) {

            // first page
            $controller->addPage(new \Frontpost\VirtualPages\Page($url))
                ->setTitle($title)
                ->setContent($content)
                ->setTemplate($template);
        });
    }
}

function cunifymax_validate_current_page($name)
{

    $url_name = '';
    $url_path = trim(parse_url(add_query_arg(array()), PHP_URL_PATH), '/');
    $url_path_arr = explode('/', $url_path);

    if (is_array($url_path_arr)) {
        $url_path_arr_rev = array_reverse($url_path_arr);

        $url_name = $url_path_arr_rev[0];
    }

    if ($name == $url_name) {
        return true;
    }

    return false;
}

function cunifymax_prepare_post_json($post) {

    $tmp_post = new stdClass();
    $tmp_post->id = $tmp_post->ID = $tmp_post->custom['ID'] = $post->ID;
    $tmp_post->title = $tmp_post->custom['title'] = $post->post_title;
    $tmp_post->description = $tmp_post->custom['description'] = $post->post_content;
    $tmp_post->alias = $tmp_post->slug = $tmp_post->custom['alias'] = $post->slug;
    $tmp_post->type = $tmp_post->custom['type'] = $post->post_type;
    $tmp_post->twig = $tmp_post->custom['twig'] = $post->twig;
    $tmp_post->css = $tmp_post->custom['css'] = $post->css;
    $tmp_post->js = $tmp_post->custom['js'] = $post->js;

    $tmp_post->post_title = $tmp_post->title;
    $tmp_post->post_content = $tmp_post->description;
    $tmp_post->post_type = $tmp_post->type;

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

        $tmp_post->$term = cunifyadx_json_to_array($post->$term);
        $tmp_post->custom[$term] = $tmp_post->$term;
    }

    return $tmp_post;
}


function cunifymax_prepare_post_json_short($post) {

    $tmp_post = new stdClass();
    $tmp_post->id = $tmp_post->ID = $tmp_post->custom['ID'] = $post->ID;
    $tmp_post->title = $tmp_post->custom['title'] = $post->post_title;
    $tmp_post->description = $tmp_post->custom['description'] = $post->post_content;
    $tmp_post->alias = $tmp_post->slug = $tmp_post->custom['alias'] = $post->slug;
    $tmp_post->type = $tmp_post->custom['type'] = $post->post_type;

    $tmp_post->post_title = $tmp_post->title;
    $tmp_post->post_content = $tmp_post->description;
    $tmp_post->post_type = $tmp_post->type;

    return $tmp_post;

}


function cunifymax_register_session()
{
    if (!session_id()) {
        session_start();
    }

}

add_action('init', 'cunifymax_register_session');
