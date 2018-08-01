<?php

global $wp;
if (!is_user_logged_in() && !cunifymax_is_login_page()) {
    wp_redirect(wp_login_url(site_url($wp->request)));
    exit;
}

function cunifymax_get_google_fonts()
{

    $google_fonts = array();

    $google_fonts['Roboto'] = "'Roboto'";
    $google_fonts['Zilla Slab Highlight'] = "'Zilla Slab Highlight'";
    $google_fonts['Open Sans'] = "'Open Sans'";
    $google_fonts['Spectral'] = "'Spectral'";
    $google_fonts['Slabo 27px'] = "'Slabo 27px'";
    $google_fonts['Lato'] = "'Lato'";
    $google_fonts['Roboto Condensed'] = "'Roboto Condensed'";
    $google_fonts['Oswald'] = "'Oswald'";
    $google_fonts['Source Sans Pro'] = "'Source Sans Pro'";
    $google_fonts['Raleway'] = "'Raleway'";
    $google_fonts['Zilla Slab'] = "'Zilla Slab'";
    $google_fonts['Montserrat'] = "'Montserrat'";
    $google_fonts['PT Sans'] = "'PT Sans'";
    $google_fonts['Roboto Slab'] = "'Roboto Slab'";
    $google_fonts['Merriweather'] = "'Merriweather'";
    $google_fonts['Saira Condensed'] = "'Saira Condensed'";
    $google_fonts['Saira'] = "'Saira'";
    $google_fonts['Open Sans Condensed'] = "'Open Sans Condensed'";
    $google_fonts['Saira Semi Condensed'] = "'Saira Semi Condensed'";
    $google_fonts['Saira Extra Condensed'] = "'Saira Extra Condensed'";
    $google_fonts['Julee'] = "'Julee'";
    $google_fonts['Archivo'] = "'Archivo'";
    $google_fonts['Ubuntu'] = "'Ubuntu'";
    $google_fonts['Lora'] = "'Lora'";
    $google_fonts['Manuale'] = "'Manuale'";
    $google_fonts['Asap Condensed'] = "'Asap Condensed'";
    $google_fonts['Faustina'] = "'Faustina'";
    $google_fonts['Cairo'] = "'Cairo'";
    $google_fonts['Playfair Display'] = "'Playfair Display'";
    $google_fonts['Droid Serif'] = "'Droid Serif'";
    $google_fonts['Noto Sans'] = "'Noto Sans'";
    $google_fonts['PT Serif'] = "'PT Serif'";
    $google_fonts['Droid Sans'] = "'Droid Sans'";
    $google_fonts['Arimo'] = "'Arimo'";
    $google_fonts['Poppins'] = "'Poppins'";
    $google_fonts['Sedgwick Ave Display'] = "'Sedgwick Ave Display'";
    $google_fonts['Titillium Web'] = "'Titillium Web'";
    $google_fonts['Muli'] = "'Muli'";
    $google_fonts['Sedgwick Ave'] = "'Sedgwick Ave'";
    $google_fonts['Indie Flower'] = "'Indie Flower'";
    $google_fonts['Mada'] = "'Mada'";
    $google_fonts['PT Sans Narrow'] = "'PT Sans Narrow'";
    $google_fonts['Noto Serif'] = "'Noto Serif'";
    $google_fonts['Bitter'] = "'Bitter'";
    $google_fonts['Dosis'] = "'Dosis'";
    $google_fonts['Josefin Sans'] = "'Josefin Sans'";
    $google_fonts['Inconsolata'] = "'Inconsolata', monospace";
    $google_fonts['Bowlby One SC'] = "'Bowlby One SC'";
    $google_fonts['Oxygen'] = "'Oxygen'";
    $google_fonts['Arvo'] = "'Arvo'";
    $google_fonts['Hind'] = "'Hind'";
    $google_fonts['Cabin'] = "'Cabin'";
    $google_fonts['Fjalla One'] = "'Fjalla One'";
    $google_fonts['Anton'] = "'Anton'";
    $google_fonts['Cairo'] = "'Cairo'";
    $google_fonts['Playfair Display'] = "'Playfair Display'";
    $google_fonts['Droid Serif'] = "'Droid Serif'";
    $google_fonts['Noto Sans'] = "'Noto Sans'";
    $google_fonts['PT Serif'] = "'PT Serif'";
    $google_fonts['Droid Sans'] = "'Droid Sans'";
    $google_fonts['Arimo'] = "'Arimo'";
    $google_fonts['Poppins'] = "'Poppins'";
    $google_fonts['Sedgwick Ave Display'] = "'Sedgwick Ave Display'";
    $google_fonts['Titillium Web'] = "'Titillium Web'";
    $google_fonts['Muli'] = "'Muli'";
    $google_fonts['Sedgwick Ave'] = "'Sedgwick Ave'";
    $google_fonts['Indie Flower'] = "'Indie Flower'";
    $google_fonts['Mada'] = "'Mada'";
    $google_fonts['PT Sans Narrow'] = "'PT Sans Narrow'";
    $google_fonts['Noto Serif'] = "'Noto Serif'";
    $google_fonts['Bitter'] = "'Bitter'";
    $google_fonts['Dosis'] = "'Dosis'";
    $google_fonts['Josefin Sans'] = "'Josefin Sans'";
    $google_fonts['Inconsolata'] = "'Inconsolata', monospace";
    $google_fonts['Bowlby One SC'] = "'Bowlby One SC'";
    $google_fonts['Oxygen'] = "'Oxygen'";
    $google_fonts['Arvo'] = "'Arvo'";
    $google_fonts['Hind'] = "'Hind'";
    $google_fonts['Cabin'] = "'Cabin'";
    $google_fonts['Fjalla One'] = "'Fjalla One'";
    $google_fonts['Anton'] = "'Anton'";
    $google_fonts['Acme'] = "'Acme'";
    $google_fonts['Archivo Narrow'] = "'Archivo Narrow'";
    $google_fonts['Mukta Vaani'] = "'Mukta Vaani'";
    $google_fonts['Play'] = "'Play'";
    $google_fonts['Cuprum'] = "'Cuprum'";
    $google_fonts['Maven Pro'] = "'Maven Pro'";
    $google_fonts['EB Garamond'] = "'EB Garamond'";
    $google_fonts['Passion One'] = "'Passion One'";
    $google_fonts['Ropa Sans'] = "'Ropa Sans'";
    $google_fonts['Francois One'] = "'Francois One'";
    $google_fonts['Archivo Black'] = "'Archivo Black'";
    $google_fonts['Pathway Gothic One'] = "'Pathway Gothic One'";
    $google_fonts['Exo'] = "'Exo'";
    $google_fonts['Vollkorn'] = "'Vollkorn'";
    $google_fonts['Libre Franklin'] = "'Libre Franklin'";
    $google_fonts['Crete Round'] = "'Crete Round'";
    $google_fonts['Alegreya'] = "'Alegreya'";
    $google_fonts['PT Sans Caption'] = "'PT Sans Caption'";
    $google_fonts['Alegreya Sans'] = "'Alegreya Sans'";
    $google_fonts['Source Code Pro'] = "'Source Code Pro', monospace";

    return $google_fonts;
}

$args = array(
    // Get all posts
    'posts_per_page' => 20,
    // Order by post date
    'orderby' => array(
        'date' => 'DESC',
    ));

$context = $timber->get_context();

$id = (isset($_GET['id'])) ? $_GET['id'] : 0;

$context['id'] = $id;
$context['designer_wrapper'] = cunifymax_load_designer_wrapper($designer_url, $id);
$context['design'] = $timber->get_post($context['id']);
$context['terms'] = get_the_terms($context['id'], 'post_tag');
$context['cx_user_id'] = get_current_user_id();
$context['google_fonts'] = cunifymax_get_google_fonts();

$context['cx_can_editor'] = current_user_can('editor');
$context['cx_can_admin'] = current_user_can('administrator');
$context['frameworks'] = cunifymax_get_frameworks();
$context['plugin_path'] = cunifymax_plugin_path();
$context['apps_arr'] = cunifymax_fetch_cunifymax_apps();

if ($context['design']->post_type == 'designer_themes') {

    $parsed_pages = array();
    $themepages_args = array_merge($args, array('post_type' => 'designer_themepages'));

    $tmp_pages = $timber->get_posts($themepages_args);

    foreach ($tmp_pages as $key => $tmp_page) {
        $parsed_pages[] = cunifymax_prepare_post_json($tmp_page);
    }

    $context['design']->active_page = (!empty($parsed_pages)) ? $parsed_pages[0]->ID : 0;
    $context['design']->pages = $parsed_pages;
    $context['design']->pages_json = (!empty($parsed_pages)) ? json_encode($parsed_pages, JSON_PRETTY_PRINT) : '{}';
} else {
    $context['design']->pages_json = '{}';
}

$tags = array();
foreach ($context['terms'] as $key => $term) {
    $tags[] = $term->name;
}

$context['design']->tags = $tags;
$context['design']->tags_str = implode(',', $tags);
$context['design']->framework_arr = $context['design']->frameworks;

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

    $term_content = (isset($context['design']->custom[$term])) ? $context['design']->custom[$term] : '{}';

    if (is_array($term_content)) {
        $term_content = json_encode($term_content);
    }

    $context['design']->$term = $context['design']->custom[$term] = (!cunifymax_json_validator($term_content)) ? '{}' : json_encode(json_decode($term_content), JSON_PRETTY_PRINT);
}

if ($context['id']) {
    $context['design']->apps = json_encode($context['apps_arr']);
    $context['design']->custom['json_arr'] = json_decode($context['design']->custom['json'], true);
}

$timber->render(array('design.twig'), $context);
