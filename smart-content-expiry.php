<?php
/**
 * Plugin Name: Smart Content Expiry
 * Description: Schedule content expiration and control what happens after expiry — hide, replace, redirect or display a custom message.
 * Version: 1.0.3
 * Author: MakeYourWeb
 * Author URI: https://plugins.makeyourweb.online/
 * Text Domain: smart-content-expiry
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) exit;

add_action('plugins_loaded', function () {
    load_plugin_textdomain('smart-content-expiry', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

add_action('add_meta_boxes', function () {
    add_meta_box(
        'smcoex_myw_expiry_meta',
        esc_html__('Content Expiry Settings', 'smart-content-expiry'),
        'smcoex_myw_render_meta_box',
        ['post', 'page'],
        'side'
    );
});

function smcoex_myw_render_meta_box($post) {
    wp_nonce_field('smcoex_myw_save_meta_box', 'smcoex_myw_meta_box_nonce');

    $expiry_date = get_post_meta($post->ID, '_smcoex_myw_expiry_date', true);
    $expiry_action = get_post_meta($post->ID, '_smcoex_myw_expiry_action', true);
    $expiry_message = get_post_meta($post->ID, '_smcoex_myw_expiry_message', true);
    $expiry_redirect = get_post_meta($post->ID, '_smcoex_myw_expiry_redirect', true);

    echo '<p><label for="smcoex_myw_expiry_date">' . esc_html__('Expiry Date/Time:', 'smart-content-expiry') . '</label><br />';
    echo '<input type="datetime-local" name="smcoex_myw_expiry_date" value="' . esc_attr($expiry_date) . '" class="widefat" /></p>';

    echo '<p><label for="smcoex_myw_expiry_action">' . esc_html__('Action After Expiry:', 'smart-content-expiry') . '</label><br />';
    echo '<select name="smcoex_myw_expiry_action" class="widefat">';
    echo '<option value="hide"' . selected($expiry_action, 'hide', false) . '>' . esc_html__('Hide content', 'smart-content-expiry') . '</option>';
    echo '<option value="replace"' . selected($expiry_action, 'replace', false) . '>' . esc_html__('Replace with message', 'smart-content-expiry') . '</option>';
    echo '<option value="redirect"' . selected($expiry_action, 'redirect', false) . '>' . esc_html__('Redirect to URL', 'smart-content-expiry') . '</option>';
    echo '</select></p>';

    echo '<p><label for="smcoex_myw_expiry_message">' . esc_html__('Expiry Message (if replacing):', 'smart-content-expiry') . '</label><br />';
    echo '<textarea name="smcoex_myw_expiry_message" class="widefat">' . esc_textarea($expiry_message) . '</textarea></p>';

    echo '<p><label for="smcoex_myw_expiry_redirect">' . esc_html__('Redirect URL (if redirecting):', 'smart-content-expiry') . '</label><br />';
    echo '<input type="url" name="smcoex_myw_expiry_redirect" value="' . esc_attr($expiry_redirect) . '" class="widefat" /></p>';
}

add_action('save_post', function ($post_id) {
    if (!isset($_POST['smcoex_myw_meta_box_nonce'])) return;
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['smcoex_myw_meta_box_nonce'])), 'smcoex_myw_save_meta_box')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['smcoex_myw_expiry_date'])) {
        update_post_meta($post_id, '_smcoex_myw_expiry_date', sanitize_text_field(wp_unslash($_POST['smcoex_myw_expiry_date'])));
    }
    if (isset($_POST['smcoex_myw_expiry_action'])) {
        update_post_meta($post_id, '_smcoex_myw_expiry_action', sanitize_text_field(wp_unslash($_POST['smcoex_myw_expiry_action'])));
    }
    if (isset($_POST['smcoex_myw_expiry_message'])) {
        update_post_meta($post_id, '_smcoex_myw_expiry_message', sanitize_textarea_field(wp_unslash($_POST['smcoex_myw_expiry_message'])));
    }
    if (isset($_POST['smcoex_myw_expiry_redirect'])) {
        update_post_meta($post_id, '_smcoex_myw_expiry_redirect', esc_url_raw(wp_unslash($_POST['smcoex_myw_expiry_redirect'])));
    }
});

add_filter('the_content', function ($content) {
    if (is_admin() || !is_singular()) return $content;
    global $post;

    $expiry_date = get_post_meta($post->ID, '_smcoex_myw_expiry_date', true);
    $action = get_post_meta($post->ID, '_smcoex_myw_expiry_action', true);
    $message = get_post_meta($post->ID, '_smcoex_myw_expiry_message', true);
    $redirect = get_post_meta($post->ID, '_smcoex_myw_expiry_redirect', true);

    if ($expiry_date && strtotime(current_time('mysql')) >= strtotime($expiry_date)) {
        switch ($action) {
            case 'hide': return '';
            case 'replace': return '<div class="sce-expired-message">' . wp_kses_post($message) . '</div>';
            case 'redirect': wp_redirect(esc_url_raw($redirect)); exit;
        }
    }
    return $content;
});

add_shortcode('smart_expire', function ($atts, $content = null) {
    $a = shortcode_atts([
        'expires' => '',
        'action' => 'hide',
        'message' => '',
        'redirect' => ''
    ], $atts);

    $expiry_time = strtotime($a['expires']);
    $now = time();

    if ($expiry_time && $now >= $expiry_time) {
        switch ($a['action']) {
            case 'hide': return '';
            case 'replace': return '<div class="sce-expired-message">' . esc_html($a['message']) . '</div>';
            case 'redirect': wp_redirect(esc_url_raw($a['redirect'])); exit;
        }
    }

    return do_shortcode($content);
});

add_action('admin_menu', function () {
    add_menu_page(
        esc_html__('Expiring Content', 'smart-content-expiry'),
        esc_html__('Expiring Content', 'smart-content-expiry'),
        'manage_options',
        'sce-expiring-content',
        'smcoex_myw_render_expiry_list_page',
        'dashicons-clock',
        80
    );
});

function smcoex_myw_render_expiry_list_page() {
    echo '<div class="wrap"><h1>' . esc_html__('Expiring Content', 'smart-content-expiry') . '</h1><br />';

    $posts = get_posts([
        'post_type' => ['post', 'page'],
        'meta_key' => '_smcoex_myw_expiry_date',
        'meta_compare' => '!=',
        'meta_value' => '',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'posts_per_page' => 100
    ]);

    if (empty($posts)) {
        echo '<p>' . esc_html__('No posts with expiry set.', 'smart-content-expiry') . '</p></div>';
        return;
    }

    echo '<table class="widefat"><thead><tr><th>' . esc_html__('Title', 'smart-content-expiry') . '</th><th>' . esc_html__('Expiry Date', 'smart-content-expiry') . '</th></tr></thead><tbody>';

    foreach ($posts as $post) {
        $expiry = get_post_meta($post->ID, '_smcoex_myw_expiry_date', true);
        echo '<tr><td><a href="' . esc_url(get_edit_post_link($post->ID)) . '">' . esc_html(get_the_title($post)) . '</a></td>';
        echo '<td>' . esc_html($expiry) . '</td></tr>';
    }

    echo '</tbody></table></div>';
}

// Add Settings and Donate links on plugins list
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=sce-expiring-content') . '">Settings</a>';
    $donate_link = '<a href="https://buymeacoffee.com/makeyourweb" target="_blank">★ Donate</a>';

    array_unshift($links, $settings_link, $donate_link);

    return $links;
});