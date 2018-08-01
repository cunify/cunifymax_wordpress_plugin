<?php

global $wp;
if (!is_user_logged_in() && !cunifymax_is_login_page()) {
    wp_redirect(wp_login_url(site_url($wp->request)));
    exit;
}

function save_attachment($file = array()) {

    $attachment_id = '';

    require_once( ABSPATH . 'wp-admin/includes/admin.php' );

    $file_return = wp_handle_upload($file, array('test_form' => false));

    if (isset($file_return['error']) || isset($file_return['upload_error_handler'])) {

        return false;
    } else {

        $filename = $file_return['file'];

        $attachment = array(
            'post_mime_type' => $file_return['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content' => 'upload',
            'post_status' => 'inherit',
            'post_parent' => null,
            'guid' => $file_return['url']
        );

        $attachment_id = wp_insert_attachment($attachment, $file_return['url']);

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $filename);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        if ((int) $attachment_id) {
            return $attachment_id;
        }
    }

    return false;
}

$return_data['message'] = 'Saving failed.';
$return_data['status'] = 0;

if (!empty($_FILES)) {
    
    $file = $_FILES['uploaded_image'];
    
    $attachment_id = save_attachment($file);
    
    if ($attachment_id) {
        $return_data['message'] = 'Saved Successfully.';
        $return_data['status'] = 1;
    }
}

http_response_code(200);
header('Content-Type: application/json');
echo json_encode($return_data);
exit;
