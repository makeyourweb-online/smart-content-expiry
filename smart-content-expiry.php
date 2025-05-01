<?php
/**
 * Plugin Name: Smart Content Expiry
 * Description: Schedule content expiration and control what happens after expiry — hide, replace, redirect or display a custom message.
 * Version: 1.0
 * Author: Make Your Web
 * Author URI: https://makeyourweb.online
 * Text Domain: smart-content-expiry
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

// Load plugin textdomain
add_action('plugins_loaded', function () {
    load_plugin_textdomain('smart-content-expiry', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Register meta boxes for expiry settings in post editor
add_action('add_meta_boxes', function () {
    add_meta_box(
        'sce_expiry_meta',
        __('Content Expiry Settings', 'smart-content-expiry'),
        'sce_render_meta_box',
        ['post', 'page'],
        'side'
    );
});

function sce_render_meta_box($post)
{
    wp_nonce_field('sce_save_meta_box', 'sce_meta_box_nonce');

    $expiry_date = get_post_meta($post->ID, '_sce_expiry_date', true);
    $expiry_action = get_post_meta($post->ID, '_sce_expiry_action', true);
    $expiry_message = get_post_meta($post->ID, '_sce_expiry_message', true);
    $expiry_redirect = get_post_meta($post->ID, '_sce_expiry_redirect', true);

    echo '<p><label for="sce_expiry_date">' . __('Expiry Date/Time:', 'smart-content-expiry') . '</label><br />';
    echo '<input type="datetime-local" name="sce_expiry_date" value="' . esc_attr($expiry_date) . '" class="widefat" /></p>';

    echo '<p><label for="sce_expiry_action">' . __('Action After Expiry:', 'smart-content-expiry') . '</label><br />';
    echo '<select name="sce_expiry_action" class="widefat">';
    echo '<option value="hide"' . selected($expiry_action, 'hide', false) . '>' . __('Hide content', 'smart-content-expiry') . '</option>';
    echo '<option value="replace"' . selected($expiry_action, 'replace', false) . '>' . __('Replace with message', 'smart-content-expiry') . '</option>';
    echo '<option value="redirect"' . selected($expiry_action, 'redirect', false) . '>' . __('Redirect to URL', 'smart-content-expiry') . '</option>';
    echo '</select></p>';

    echo '<p><label for="sce_expiry_message">' . __('Expiry Message (if replacing):', 'smart-content-expiry') . '</label><br />';
    echo '<textarea name="sce_expiry_message" class="widefat">' . esc_textarea($expiry_message) . '</textarea></p>';

    echo '<p><label for="sce_expiry_redirect">' . __('Redirect URL (if redirecting):', 'smart-content-expiry') . '</label><br />';
    echo '<input type="url" name="sce_expiry_redirect" value="' . esc_attr($expiry_redirect) . '" class="widefat" /></p>';
}

add_action('save_post', function ($post_id) {
    if (!isset($_POST['sce_meta_box_nonce']) || !wp_verify_nonce($_POST['sce_meta_box_nonce'], 'sce_save_meta_box')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    update_post_meta($post_id, '_sce_expiry_date', sanitize_text_field($_POST['sce_expiry_date'] ?? ''));
    update_post_meta($post_id, '_sce_expiry_action', sanitize_text_field($_POST['sce_expiry_action'] ?? ''));
    update_post_meta($post_id, '_sce_expiry_message', sanitize_textarea_field($_POST['sce_expiry_message'] ?? ''));
    update_post_meta($post_id, '_sce_expiry_redirect', esc_url_raw($_POST['sce_expiry_redirect'] ?? ''));
});

// Modify content on frontend
add_filter('the_content', 'sce_filter_content');
function sce_filter_content($content)
{
    if (is_admin() || !is_singular()) return $content;
    global $post;

    if (!$post) return $content;

    $expiry_date = get_post_meta($post->ID, '_sce_expiry_date', true);
    $expiry_action = get_post_meta($post->ID, '_sce_expiry_action', true);
    $expiry_message = get_post_meta($post->ID, '_sce_expiry_message', true);
    $expiry_redirect = get_post_meta($post->ID, '_sce_expiry_redirect', true);

    if ($expiry_date && strtotime(current_time('mysql')) >= strtotime($expiry_date)) {
        switch ($expiry_action) {
            case 'hide':
                return '';
            case 'replace':
                return '<div class="sce-expired-message">' . wp_kses_post($expiry_message) . '</div>';
            case 'redirect':
                wp_redirect(esc_url_raw($expiry_redirect));
                exit;
        }
    }

    return $content;
}

// Shortcode: [smart_expire expires="YYYY-MM-DD HH:MM"]...[/smart_expire]
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
            case 'hide':
                return '';
            case 'replace':
                return '<div class="sce-expired-message">' . esc_html($a['message']) . '</div>';
            case 'redirect':
                wp_redirect(esc_url_raw($a['redirect']));
                exit;
        }
    }
    return do_shortcode($content);
});

// Admin submenu page for listing expiring content
add_action('admin_menu', function () {
    add_submenu_page(
        'tools.php',
        __('Expiring Content', 'smart-content-expiry'),
        __('Expiring Content', 'smart-content-expiry'),
        'manage_options',
        'sce-expiring-content',
        'sce_render_expiry_list_page'
    );
});

function sce_render_expiry_list_page()
{
    echo '<div class="wrap"><h1>' . __('Expiring Content', 'smart-content-expiry') . '</h1>';

    $args = [
        'post_type' => ['post', 'page'],
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => '_sce_expiry_date',
                'compare' => '!=',
                'value' => ''
            ]
        ]
    ];

    $posts = get_posts($args);
    if (empty($posts)) {
        echo '<p>' . __('No posts with expiry set.', 'smart-content-expiry') . '</p></div>';
        return;
    }

    echo '<table class="widefat"><thead><tr><th>' . __('Title', 'smart-content-expiry') . '</th><th>' . __('Expiry Date', 'smart-content-expiry') . '</th></tr></thead><tbody>';

    foreach ($posts as $post) {
        $expiry = get_post_meta($post->ID, '_sce_expiry_date', true);
        echo '<tr><td><a href="' . get_edit_post_link($post->ID) . '">' . esc_html(get_the_title($post)) . '</a></td>';
        echo '<td>' . esc_html($expiry) . '</td></tr>';
    }
    echo '</tbody></table></div>';
}