<?php
/**
 * Plugin Name: AffiBuddy
 * Description: A plugin to create custom products with shortcodes and buttons.
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Register Custom Post Type (CPT) for Products
function affibuddy_create_product_cpt() {
    $args = array(
        'label' => 'Products',
        'public' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-cart', // Custom icon for the CPT
        'show_in_rest' => true, // Support for the block editor (optional)
    );
    register_post_type('product', $args);
}
add_action('init', 'affibuddy_create_product_cpt');

// Add Custom Fields for Product CPT
function affibuddy_add_custom_fields() {
    add_meta_box(
        'affibuddy_product_fields',
        'Product Details',
        'affibuddy_product_fields_callback',
        'product',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'affibuddy_add_custom_fields');

function affibuddy_product_fields_callback($post) {
    wp_nonce_field('affibuddy_save_product_fields', 'affibuddy_product_fields_nonce');

    $product_image = get_post_meta($post->ID, '_affibuddy_product_image', true);
    $product_buttons = get_post_meta($post->ID, '_affibuddy_product_buttons', true);

    echo '<label for="product_image">Product Image:</label>';
    echo '<input type="text" name="product_image" id="product_image" value="' . esc_attr($product_image) . '" />';
    echo '<button type="button" class="upload_image_button">Upload Image</button>';

    // Classic Editor for Short Description
    echo '<label for="product_description">Product Description:</label>';
    wp_editor(get_post_meta($post->ID, '_affibuddy_product_description', true), 'product_description', array('textarea_name' => 'product_description'));

    echo '<div id="product_buttons_container">';
    echo '<label>Product Buttons:</label>';
    if ($product_buttons) {
        foreach ($product_buttons as $button) {
            echo '<input type="text" name="product_buttons[]" value="' . esc_attr($button['name']) . '" placeholder="Button Name" />';
            echo '<input type="text" name="product_button_urls[]" value="' . esc_url($button['url']) . '" placeholder="Button URL" />';
        }
    }
    echo '</div>';
    echo '<button type="button" id="add_button">+ Add Button</button>';
    
    echo '<p><strong>Generated Shortcode:</strong> [product id="' . $post->ID . '"]</p>';
}

// Save Custom Fields Data
function affibuddy_save_product_fields($post_id) {
    if (!isset($_POST['affibuddy_product_fields_nonce'])) {
        return $post_id;
    }
    if (!wp_verify_nonce($_POST['affibuddy_product_fields_nonce'], 'affibuddy_save_product_fields')) {
        return $post_id;
    }

    // Save Product Image URL
    if (isset($_POST['product_image'])) {
        update_post_meta($post_id, '_affibuddy_product_image', sanitize_text_field($_POST['product_image']));
    }

    // Save Product Description
    if (isset($_POST['product_description'])) {
        update_post_meta($post_id, '_affibuddy_product_description', wp_kses_post($_POST['product_description']));
    }

    // Save Product Buttons
    if (isset($_POST['product_buttons']) && isset($_POST['product_button_urls'])) {
        $buttons = array();
        foreach ($_POST['product_buttons'] as $key => $button) {
            if (!empty($button)) {
                $buttons[] = array(
                    'name' => sanitize_text_field($button),
                    'url' => esc_url_raw($_POST['product_button_urls'][$key]),
                );
            }
        }
        update_post_meta($post_id, '_affibuddy_product_buttons', $buttons);
    }
}
add_action('save_post', 'affibuddy_save_product_fields');

// JavaScript for handling the dynamic buttons in admin panel
function affibuddy_admin_scripts($hook) {
    if ('post.php' !== $hook && 'post-new.php' !== $hook) {
        return;
    }

    wp_enqueue_script('affibuddy-admin-scripts', plugin_dir_url(__FILE__) . 'admin-scripts.js', array('jquery'), null, true);
    wp_localize_script('affibuddy-admin-scripts', 'affibuddy_admin', array('nonce' => wp_create_nonce('affibuddy_admin_nonce')));
}
add_action('admin_enqueue_scripts', 'affibuddy_admin_scripts');

?>
