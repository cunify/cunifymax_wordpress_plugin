<?php

global $wp;
if (!is_user_logged_in() && !cunifymax_is_login_page()) {
    wp_redirect(wp_login_url(site_url($wp->request)));
    exit;
}
error_reporting(E_ALL);
$context = $timber->get_context();

$id = (isset($_POST['id'])) ? $_POST['id'] : 0;
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$limit = (isset($_POST['limit'])) ? $_POST['limit'] : 10;
$keyword = (isset($_POST['keyword'])) ? $_POST['keyword'] : '';

$fields_string = '';
$fields = array(
    'post_type' => 'designer_themes',
    'format' => 'json',
    'id' => urlencode($id),
    'page' => urlencode($page),
    'limit' => urlencode($limit),
    'keyword' => urlencode($keyword),
);

//url-ify the data for the POST
foreach ($fields as $key => $value) {
    $fields_string .= $key . '=' . $value . '&';
}

rtrim($fields_string, '&');

// Get cURL resource
$curl = curl_init();

// Set some options - we are passing in a useragent too here
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $api_url,
    CURLOPT_USERAGENT => 'Codular Sample cURL Request',
    CURLOPT_VERBOSE => true,
    CURLOPT_POST => count($fields),
    CURLOPT_POSTFIELDS => $fields_string,
));

// Send the request & save response to $resp
$resp = curl_exec($curl);

// Close request to clear up some resources
curl_close($curl);

$resp_arr = json_decode($resp, true);

$context = array_merge($context, $resp_arr);

$context['preview_url'] = $preview_url;

$timber->render(array('themeselection.twig'), $context);
