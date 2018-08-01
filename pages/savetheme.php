<?php

global $wp;
if (!is_user_logged_in() && !cunifymax_is_login_page()) {
    wp_redirect(wp_login_url(site_url($wp->request)));
    exit;
}


$id = $_POST['id'];

echo "Setting Progress..."; exit;

http_response_code(200);
header('Content-Type: application/json');
echo json_encode($return_data);
exit;
