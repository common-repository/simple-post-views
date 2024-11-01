<?php
/*
	* Plugin Name: 		Simple Post Views
	* Plugin URI: 		https://softclever.com/downloads/
	* Description: 		A simple plugin to count unique post views and display the view count.
	
	* Author: 			Md Maruf Adnan Sami
	* Author URI: 		https://www.mdmarufadnansami.com
	* Version: 			1.0
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to count unique post views
function spvc_count_post_views() {
    if (is_single()) {
        $post_id = get_the_ID();
        $count_key = 'post_views_count';
        $count = get_post_meta($post_id, $count_key, true);
        
        if ($count === '') {
            $count = 0;
            delete_post_meta($post_id, $count_key);
            add_post_meta($post_id, $count_key, '0');
        } else {
            $count++;
            update_post_meta($post_id, $count_key, $count);
        }
    }
}
add_action('wp_head', 'spvc_count_post_views');

// Function to add 'Views' column in post columns
function spvc_add_views_column($columns) {
    $columns['post_views'] = 'Views';
    return $columns;
}
add_filter('manage_posts_columns', 'spvc_add_views_column');

// Function to display view count in 'Views' column
function spvc_display_views_column($column_name, $post_id) {
    if ($column_name === 'post_views') {
        $count_key = 'post_views_count';
        $count = get_post_meta($post_id, $count_key, true);
        echo esc_html($count);
    }
}
add_action('manage_posts_custom_column', 'spvc_display_views_column', 10, 2);

// Function to display view count after post content
function spvc_display_views_after_content($content) {
    if (is_single() && get_option('spvc_show_views_after_content', '1') === '1') {
        $post_id = get_the_ID();
        $count_key = 'post_views_count';
        $count = get_post_meta($post_id, $count_key, true);
        $formatted_count = spvc_format_view_count($count);
        $content .= '<p><b>Views:</b> ' . $formatted_count . '</p>';
    }
    return $content;
}
add_filter('the_content', 'spvc_display_views_after_content');

// Function to display view count before post content
function spvc_display_views_before_content($content) {
    if (is_single() && get_option('spvc_show_views_before_content', '1') === '1') {
        $post_id = get_the_ID();
        $count_key = 'post_views_count';
        $count = get_post_meta($post_id, $count_key, true);
        $formatted_count = spvc_format_view_count($count);
        $content = '<p><b>Views:</b> ' . $formatted_count . '</p>' . $content;
    }
    return $content;
}
add_filter('the_content', 'spvc_display_views_before_content', 1);

// Function to format view count
function spvc_format_view_count($count) {
    if ($count >= 1000000000) {
        $formatted_count = round($count / 1000000000, 1) . 'B';
    } elseif ($count >= 1000000) {
        $formatted_count = round($count / 1000000, 1) . 'M';
    } elseif ($count >= 1000) {
        $formatted_count = round($count / 1000, 1) . 'K';
    } else {
        $formatted_count = $count;
    }
    return $formatted_count;
}

// Function to add settings page
function spvc_add_settings_page() {
    add_options_page(
        'Simple Post Views Settings',
        'Post Views',
        'manage_options',
        'spvc-settings',
        'spvc_render_settings_page'
    );
}
add_action('admin_menu', 'spvc_add_settings_page');

// Function to render settings page
function spvc_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Simple Post Views Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('spvc_settings_group'); ?>
            <?php do_settings_sections('spvc-settings'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Function to register settings and fields
function spvc_register_settings() {
    register_setting('spvc_settings_group', 'spvc_show_views_after_content');
    register_setting('spvc_settings_group', 'spvc_show_views_before_content');
    
    add_settings_section(
        'spvc_settings_section',
        'Display Options',
        'spvc_render_settings_section',
        'spvc-settings'
    );
    
    add_settings_field(
        'spvc_show_views_after_content',
        'Show Views After Content',
        'spvc_render_show_views_after_content_field',
        'spvc-settings',
        'spvc_settings_section'
    );
    
    add_settings_field(
        'spvc_show_views_before_content',
        'Show Views Before Content',
        'spvc_render_show_views_before_content_field',
        'spvc-settings',
        'spvc_settings_section'
    );
}
add_action('admin_init', 'spvc_register_settings');

// Function to render settings section
function spvc_render_settings_section() {
    echo '<p>' . esc_html__('Select the options to control the display of post content views:', 'simple-post-views') . '</p>';
}

// Function to render "Show Views After Content" field
function spvc_render_show_views_after_content_field() {
    $show_after_content = get_option('spvc_show_views_after_content', '1');
    ?>
    <label>
        <input type="checkbox" name="spvc_show_views_after_content" value="1" <?php checked('1', $show_after_content); ?> />
        Display views count after post content
    </label>
    <?php
}

// Function to render "Show Views Before Content" field
function spvc_render_show_views_before_content_field() {
    $show_before_content = get_option('spvc_show_views_before_content', '1');
    ?>
    <label>
        <input type="checkbox" name="spvc_show_views_before_content" value="1" <?php checked('1', $show_before_content); ?> />
        Display views count before post content
    </label>
    <?php
}
?>