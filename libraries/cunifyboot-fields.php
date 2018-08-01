<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function cunifymax_meta_save($post_id) {

    // Checks save status
    $post_type = get_post_type();
    $is_autosave = wp_is_post_autosave($post_id);
    $is_revision = wp_is_post_revision($post_id);
    $is_valid_nonce = (isset($_POST['cunifymax_' . $post_type . '_nonce']) && wp_verify_nonce($_POST['cunifymax_' . $post_type . '_nonce'], basename(__DIR__))) ? true : false;

    // Exits script depending on save status
    if ($is_autosave || $is_revision || !$is_valid_nonce) {
        return;
    }

    $post_type_arr = explode('_', $post_type);
    $clr_table_path = cunifymax_plugin_path() . 'apps/' . $post_type_arr[0] . '/tables/' . $post_type_arr[1] . '.json';

    $structure = json_decode(file_get_contents($clr_table_path), true);

    foreach ($structure['fields'] as $key => $field) {
        $field_name = $field['name'];
        $field_input = $field['input'];

        switch ($field_input) {
            case 'editor':
            case 'textarea':
                if ($_POST[$field_name] != '') {
                    update_post_meta($post_id, $field_name, wp_kses_post($_POST[$field_name]));
                }
                break;
            case 'map':
                if ($_POST['latitude'] != '') {
                    update_post_meta($post_id, 'latitude', sanitize_text_field($_POST['latitude']));
                }
                if ($_POST['longitude'] != '') {
                    update_post_meta($post_id, 'longitude', sanitize_text_field($_POST['longitude']));
                }
                break;
            case 'gallery':
                for ($x = 1; $x <= 10; $x++) {
                    if ($_POST[$field_name . '_' . $x] != '') {
                        update_post_meta($post_id, $field_name . '_' . $x, sanitize_text_field($_POST[$field_name . '_' . $x]));
                    }
                }
                break;

            default:
                if ($_POST[$field_name] != '') {
                    update_post_meta($post_id, $field_name, sanitize_text_field($_POST[$field_name]));
                }
                break;
        }
    }

    if ($_POST['title'] == '' && $_POST['name'] != '') {
        update_post_meta($post_id, 'title', sanitize_text_field($_POST['name']));
    }
}

add_action('save_post', 'cunifymax_meta_save');

// func that is going to set our title of our customer magically
function cunifymax_customers_set_title($data, $postarr) {

    $post_type = get_post_type();

    $is_valid_nonce = (isset($_POST['cunifymax_' . $post_type . '_nonce']) && wp_verify_nonce($_POST['cunifymax_' . $post_type . '_nonce'], basename(__DIR__))) ? true : false;

    // Exits script depending on save status
    if (!$is_valid_nonce) {
        return $data;
    }

    $data['post_title'] = ($postarr['title'] == '' && $postarr['name'] != '') ? sanitize_text_field($postarr['name']) : sanitize_text_field($postarr['title']); // Updated title
    $data['post_content'] = ($postarr['description'] != '' ) ? sanitize_text_field($postarr['description']) : ''; // Updated content
    $data['post_name'] = sanitize_title(sanitize_title_with_dashes($data['post_title'], '', 'save'));

    return $data;
}

//add_filter('wp_insert_post_data', 'cunifymax_customers_set_title', '99', 2);

function cunifymax_get_field_text($field, $identifier, $cunifymax_stored_meta) {
    ?>

    <div class="meta-row">
        <div class="meta-th">
            <label for="<?php echo $field['name'] ?>" class="cunifymax-row-title"><?php _e($field['title'], $identifier) ?></label>
        </div>
        <div class="meta-td">
            <input type="text" class="cunifymax-row-content" name="<?php echo $field['name'] ?>" id="<?php echo $field['name'] ?>" value="<?php
            if (!empty($cunifymax_stored_meta[$field['name']])) {
                echo esc_attr($cunifymax_stored_meta[$field['name']][0]);
            }
            ?>"/>
        </div>
    </div>

    <?php
}

function cunifymax_get_field_textarea($field, $identifier, $cunifymax_stored_meta) {
    ?>

    <div class = "meta-row">
        <div class = "meta-th">
            <label for = "<?php echo $field['name'] ?>" class = "cunifymax-row-title"><?php _e($field['title'], $identifier); ?></label>
        </div>
        <div class="meta-td">
            <textarea name="<?php echo $field['name'] ?>" id="<?php echo $field['name'] ?>"><?php
                if (!empty($cunifymax_stored_meta[$field['name']])) {
                    echo esc_attr($cunifymax_stored_meta[$field['name']][0]);
                }
                ?>
            </textarea>
        </div>
    </div>
    <?php
}

function cunifymax_get_field_editor($field, $identifier, $cunifymax_stored_meta) {
    ?>

    <div>

        <div class = "meta-th">
            <label for = "<?php echo $field['name'] ?>" class = "cunifymax-row-title"><?php _e($field['title'], $identifier); ?></label>
        </div>

        <div class="clearfix"></div>
        <div class="meta-editor"></div>
        <?php
        wp_enqueue_media();
        $content = $cunifymax_stored_meta[$field['name']][0]; // get_post_meta($post->ID, $field['name'], true);
        $editor = $field['name'];
        $settings = array(
            'textarea_rows' => 8,
            'media_buttons' => true,
        );
        $settings = array(
            'wpautop' => true, // use wpautop?
            'media_buttons' => true, // show insert/upload button(s)
            'textarea_name' => $editor, // set the textarea name to something different, square brackets [] can be used here
            'textarea_rows' => get_option('default_post_edit_rows', 10), // rows="..."
            'tabindex' => '',
            'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
            'editor_class' => '', // add extra class(es) to the editor textarea
            'teeny' => false, // output the minimal editor config used in Press This
            'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
            'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
            'quicktags' => true, // load Quicktags, can be used to pass settings directly to Quicktags using an array()
        );
        wp_editor($content, $editor, $settings);
        ?>
    </div>

    <?php
}

function cunifymax_get_field_gallery($field, $identifier, $cunifymax_stored_meta) {
    $field_name = $field['name'];
    ?>

    <div>

        <div class="meta-row">

            <input id = "upload_<?php echo $field_name ?>s_button"
                   type = "button"
                   value = "Add Gallery Photos">
            <div>



                <script type = "text/javascript">

                    // Uploading files
                    var file_frame;
                    jQuery('#upload_<?php echo $field_name ?>s_button').live('click', function (podcast) {
                        button_name = 'images';
                        podcast.preventDefault();

                        // If the media frame already exists, reopen it.
                        if (file_frame) {
                            file_frame.open();
                            return;
                        }

                        // Create the media frame.
                        file_frame = wp.media.frames.file_frame = wp.media({
                            title: jQuery(this).data('uploader_title'),
                            button: {
                                text: jQuery(this).data('uploader_button_text'),
                            },
                            multiple: true // Set to true to allow multiple files to be selected
                        });

                        // When a file is selected, run a callback.
                        file_frame.on('select', function () {
                            // We set multiple to false so only get one image from the uploader
                            attachment = file_frame.state().get('selection').first().toJSON();

                            var url = attachment.url;

                            setImagePicked(url);

                        });

                        // Finally, open the modal
                        file_frame.open();



                    });

                    jQuery(document).ready(function ($) {
                        jQuery('.delete-image').click(function () {
                            removePhoto(jQuery(this));
                        });
                    });

                    function removePhoto(this_element) {

                        var counter = 1;
                        var confirming = confirm('Are you sure you want to delete?');
                        if (confirming) {
                            this_element.closest('li').remove();

                            jQuery(".business-<?php echo $field_name ?>s li").each(function () {
                                jQuery(this).find('input').attr("name", '<?php echo $field_name ?>_' + counter);

                                counter++;
                            });

                        }
                    }

                    function setImagePicked(url) {

                        counter = jQuery('.business-<?php echo $field_name ?>s li').length;
                        counter++;

                        html = ' <li>';
                        html += '<img src="' + url + '" height="100px">';
                        html += ' <input type = "hidden"';
                        html += ' name = "<?php echo $field_name ?>_' + counter + '"';
                        html += 'id = "<?php echo $field_name ?>s"';
                        html += 'size = "70"';
                        html += 'value = "' + url + '" />';
                        html += '<a class="delete-image">';
                        html += 'Delete';
                        html += '</a>';
                        html += '</li>';

                        jquery_html = jQuery(html);

                        jquery_html.find('.delete-image').click(function () {
                            removePhoto(jQuery(this));
                        });

                        jQuery('.business-<?php echo $field_name ?>s').append(jquery_html);
                    }

                </script>



                <div>
                    <ul class="business-<?php echo $field_name ?>s">

                        <?php
//for ($x >= 1; $x <= 10; $x++) {
                        for ($x = 1; $x <= 10; $x++) {

                            $strFile = $cunifymax_stored_meta[$field_name . '_' . $x][0];

                            if ($strFile != '') {
                                ?>

                                <li>
                                    <img src="<?php echo $strFile; ?>" height="100px">
                                    <input type = "hidden"
                                           name = "<?php echo $field_name ?>_<?php echo $x; ?>"
                                           id = "<?php echo $field_name ?>s"
                                           size = "70"
                                           value = "<?php echo $strFile; ?>" />
                                    <a class="delete-image">
                                        Delete
                                    </a>
                                </li>

                                <?php
                            }
                        }
                        ?>
                    </ul>
                    <div class="clearfix"></div>
                </div>

            </div>
        </div>


    </div>


    <?php
}

function cunifymax_get_field_map($field, $identifier, $cunifymax_stored_meta) {
    ?>

    <div>

        <div class="meta">
            <div class="meta-th">
                <label for="map" class="cunifymax-row-title"><?php _e('Map', 'cunifymax-business-listing') ?></label>
            </div>
        </div>

        <div id="map-canvas" style="width: 100%; height: 200px;"></div>
        <input id="latitude" type="hidden" class="cunifymax-row-content" name="latitude" id="latitude" value="<?php
        if (!empty($cunifymax_stored_meta['latitude'])) {
            echo esc_attr($cunifymax_stored_meta['latitude'][0]);
        }
        ?>"/>
        <input id="longitude" type="hidden" class="cunifymax-row-content" name="longitude" id="phone" value="<?php
        if (!empty($cunifymax_stored_meta['longitude'])) {
            echo esc_attr($cunifymax_stored_meta['longitude'][0]);
        }
        ?>"/>
        <div class="map-latitude-longitude">

            <?php if ($cunifymax_stored_meta['latitude'][0] != '') { ?>
                Lat: <?php echo esc_attr($cunifymax_stored_meta['latitude'][0]); ?> - Lon: <?php echo esc_attr($cunifymax_stored_meta['longitude'][0]); ?>
            <?php } ?>
        </div>

    </div>


    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBM7aNBTgvwJyUwTqkbwAYa8mC7gZRItPs"></script>

    <?php
    $cunifymax_stored_meta['latitude'][0] = ($cunifymax_stored_meta['latitude'][0] != '') ? $cunifymax_stored_meta['latitude'][0] : '0.0236';
    $cunifymax_stored_meta['longitude'][0] = ($cunifymax_stored_meta['longitude'][0] != '') ? $cunifymax_stored_meta['longitude'][0] : '37.9062';
    ?>

    <script>

                    var marker;
                    var map;
                    function initialize() {
                        var mapCanvas = document.getElementById('map-canvas');
                        var myLatlng = new google.maps.LatLng(<?php echo esc_attr($cunifymax_stored_meta['latitude'][0]); ?>, <?php echo esc_attr($cunifymax_stored_meta['longitude'][0]); ?>);
                        var mapOptions = {
                            center: myLatlng,
                            zoom: 6,
                            mapTypeId: google.maps.MapTypeId.ROADMAP
                        }

                        map = new google.maps.Map(mapCanvas, mapOptions);
                        marker = new google.maps.Marker({
                            position: myLatlng,
                            map: map
                        });
                        google.maps.event.addListener(map, "click", function (event) {
                            // get lat/lon of click
                            var clickLat = event.latLng.lat();
                            var clickLon = event.latLng.lng();
                            // show in input box
                            document.getElementById("latitude").value = clickLat.toFixed(5);
                            document.getElementById("longitude").value = clickLon.toFixed(5);
                            marker.setPosition(event.latLng);
                            map.setCenter(event.latLng);
                            setDescription(clickLat, clickLon);
                        });
                    }

                    google.maps.event.addDomListener(window, 'load', initialize);

                    function setDescription(latitude, longitude) {
                        jQuery('.map-latitude-longitude').html('Lat:' + latitude + ' - ' + 'Lon:' + longitude);
                    }


    </script>


    <?php
}
