<?php

/**
 * Plugin Name: Hoarder
 * Version:     1.0
 * 
 */

defined('ABSPATH') or die('No script please, kiddies!');

define('HOARDER_VERSION', '1.0');
define('HOARDER_URL', 'http://13.233.42.233:8080/push');

function hoarder_activate()
{
}

register_activation_hook(__FILE__, 'hoarder_activate');

function hoarder_uninstall()
{
    delete_option('hoarder_enabled');
    delete_option('hoarder_token');
}

register_uninstall_hook(__FILE__, 'hoarder_uninstall');

function hoarder_settings_page_content()
{ ?>
    <div class="wrap">
        <h2>Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('hoarder_fields');
            do_settings_sections('hoarder_fields');
            submit_button();
            ?>
        </form>
    </div>
<?php
}

function hoarder_create_plugin_settings_page()
{
    $page_title = 'Hoarder settings';
    $menu_title = 'Hoarder';
    $capability = 'manage_options';
    $slug = 'hoarder_fields';
    $callback = 'hoarder_settings_page_content';

    add_submenu_page('options-general.php', $page_title, $menu_title, $capability, $slug, $callback);
}

add_action('admin_menu', 'hoarder_create_plugin_settings_page');

function hoarder_setup_settings_sections($args)
{
    add_settings_section('hoarder_section', 'Common settings', false, 'hoarder_fields');
}

add_action('admin_init', 'hoarder_setup_settings_sections');

function hoarder_enabled_callback($args)
{
    $checked = get_option('hoarder_enabled');
    echo "<input name='hoarder_enabled' id='hoarder_enabled' type='checkbox' value='1'" . checked(1, $checked, false) . " />";
}

function hoarder_token_callback($args)
{
    $token = get_option('hoarder_token');
    echo '<input name="hoarder_token" id="hoarder_token" type="text" value="' . esc_attr($token) . '" />';
}

function hoarder_setup_fields()
{
    add_settings_field('hoarder_enabled', 'Enabled', 'hoarder_enabled_callback', 'hoarder_fields', 'hoarder_section');
    register_setting('hoarder_fields', 'hoarder_enabled');
    if (!get_option('hoarder_token')) {
        add_settings_field('hoarder_token', 'Token', 'hoarder_token_callback', 'hoarder_fields', 'hoarder_section');
        register_setting('hoarder_fields', 'hoarder_token');
    }
}

add_action('admin_init', 'hoarder_setup_fields');

function hoarder_add_settings_link($links)
{
    $links[] = '<a href="' . admin_url('options-general.php?page=hoarder_fields') . '">' . __('Settings') . '</a>';
    return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'hoarder_add_settings_link');

function hoarder_send_notification($user_id, $new_role, $old_roles)
{
    $user_info = get_userdata($user_id);
    if (isset($user_info->user_email) && $user_info->user_email) {
        $json_object = array();
        $json_object['actions'] = array('EMAIL');
        $json_object['metaData'] = array();
        $json_object['metaData']['email_subject'] = 'role change notification';
        $json_object['metaData']['email_to'] = $user_info->user_email;
        $json_object['metaData']['email_text'] = 'site url: ' . get_site_url() . PHP_EOL;
        $json_object['metaData']['email_text'] .= 'user login: ' . $user_info->user_login . PHP_EOL;
        $json_object['metaData']['email_text'] .= 'old roles: ' . $old_roles . ' , new role: ' . $new_role . PHP_EOL;
        $json_object['metaData']['email_text'] .= 'datetime:' . date('d/M/Y H:i:s');
        $json_object['metaData'] = (object) $json_object['metaData'];
        $json_object = (object) $json_object;

        $json = json_encode($json_object);
        $headers = array(
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . get_option('hoarder_token')
        );
        $args = array(
            'body' => $json,
            'headers' => $headers,
            'data_format' => 'body'
        );
        if (function_exists('wp_remote_post')) {
            wp_remote_post(HOARDER_URL, $args);
        }
    }
}

function hoarder_user_role_update($user_id, $new_role, $old_roles = array())
{
    $hoarder_enabled = get_option('hoarder_enabled');
    $hoarder_token = get_option('hoarder_token');
    if ($hoarder_enabled && !empty($hoarder_token)) {
        hoarder_send_notification($user_id, $new_role ? $new_role : "empty", is_array($old_roles) ? join($old_roles) : $old_roles);
    }
}

add_action('set_user_role', 'hoarder_user_role_update', 10, 3);
