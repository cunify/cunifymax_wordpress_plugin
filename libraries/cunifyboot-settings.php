<?php
# http://kovshenin.com/2012/the-wordpress-settings-api/
# http://codex.wordpress.org/Settings_API

$section_description = array();

add_action('admin_menu', 'cunifymax_admin_menu');

function cunifymax_admin_menu() {
    add_options_page(__('Cunifymax Settings', 'cunifymax'), __('Cunifymax Settings', 'cunifymax'), 'manage_options', 'cunifymax', 'cunifymax_options_page');
}

add_action('admin_init', 'cunifymax_admin_init');

function cunifymax_admin_init() {

    register_setting('cunifymax-settings-group', 'cunifymax-settings');

    $apps_path = cunifymax_plugin_path() . 'apps/';

    $app_files = scandir($apps_path);

    foreach ($app_files as $key => $app_file) {
        if ($app_file <> '.' && $app_file <> '..' && is_dir($apps_path)) {

            $setting_path = $apps_path . $app_file . '/setting.json';

            if (file_exists($setting_path)) {

                $setting = json_decode(file_get_contents($setting_path), true);

                foreach ($setting['sections'] as $key => $section) {

                    global $section_description;

                    $section_name = $section['name'];
                    $section_description[$section_name] = $section['description'];
                    add_settings_section($section_name, __($section['title'], 'cunifymax'), 'cunifymax_section_description_callback', 'cunifymax');

                    foreach ($section['fields'] as $key => $field) {
                        switch ($field['input']) {
                            case 'textarea':
                                add_settings_field($field['name'], __($field['title'], 'cunifymax'), 'cunifymax_setting_textareafield_callback', 'cunifymax', $section_name, array($field));
                                break;
                            case 'text':
                            default:
                                add_settings_field($field['name'], __($field['title'], 'cunifymax'), 'cunifymax_setting_textfield_callback', 'cunifymax', $section_name, array($field));
                                break;
                        }
                    }
                }
            }
        }
    }
}

/*
 * THE ACTUAL PAGE 
 * */

function cunifymax_options_page() {
    ?>
    <div class="wrap">
        <h2><?php _e('My Plugin Options', 'cunifymax'); ?></h2>
        <form action="options.php" method="POST">
            <?php settings_fields('cunifymax-settings-group'); ?>
            <?php do_settings_sections('cunifymax'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/*
 * THE SECTIONS
 * Hint: You can omit using add_settings_field() and instead
 * directly put the input fields into the sections.
 * */

function cunifymax_section_description_callback($arg) {

    global $section_description;
    _e($section_description[$arg['id']], 'cunifymax');
}

/*
 * THE FIELDS
 * */

function cunifymax_setting_textfield_callback($arg) {

    $settings = (array) get_option('cunifymax-settings');

    $field = $arg[0]['name'];
    $default = $arg[0]['default'];
    $value = (esc_attr($settings[$field]) <> '') ? esc_attr($settings[$field]) : $default;

    echo "<input type='text' name='cunifymax-settings[$field]' value='$value' />";
}

function cunifymax_setting_textareafield_callback($arg) {

    $settings = (array) get_option('cunifymax-settings');

    $field = $arg[0]['name'];
    $default = $arg[0]['default'];
    $value = (esc_attr($settings[$field]) <> '') ? esc_attr($settings[$field]) : $default;

    echo "<textarea name='cunifymax-settings[$field]'>$value</textarea>";
}

/*
 * INPUT VALIDATION:
 * */

function cunifymax_settings_validate_and_sanitize($input) {

    $settings = (array) get_option('cunifymax-settings');

    // and so on for each field

    return $input;
}
