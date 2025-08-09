<?php
/**
 * Plugin Name: Product Manager CPT
 * Plugin URI: https://example.com/product-manager-cpt
 * Description: A comprehensive WordPress plugin for managing products with custom post types, featuring modern UI and AJAX functionality.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: product-manager-cpt
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PMCPT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PMCPT_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PMCPT_VERSION', '1.0.0');

// Main plugin class
class ProductManagerCPT {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_ajax_pmcpt_save_product', array($this, 'ajax_save_product'));
        add_action('wp_ajax_pmcpt_delete_product', array($this, 'ajax_delete_product'));
        add_action('wp_ajax_pmcpt_search_products', array($this, 'ajax_search_products'));
        
        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        $this->register_post_type();
        $this->register_meta_fields();
    }
    
    public function register_post_type() {
        $args = array(
            'labels' => array(
                'name' => __('Products', 'product-manager-cpt'),
                'singular_name' => __('Product', 'product-manager-cpt'),
            ),
            'public' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'supports' => array('title', 'editor', 'thumbnail'),
            'capability_type' => 'post',
            'has_archive' => false,
            'rewrite' => false,
        );
        
        register_post_type('pmcpt_product', $args);
    }
    
    public function register_meta_fields() {
        register_meta('post', '_pmcpt_product_buttons', array(
            'type' => 'string',
            'single' => true,
            'show_in_rest' => false,
        ));
        
        register_meta('post', '_pmcpt_product_shortcode', array(
            'type' => 'string',
            'single' => true,
            'show_in_rest' => false,
        ));
    }
    
    public function admin_menu() {
        // Main menu
        add_menu_page(
            __('Product Manager', 'product-manager-cpt'),
            __('Product Manager', 'product-manager-cpt'),
            'manage_options',
            'product-manager-cpt',
            array($this, 'admin_page_products'),
            'dashicons-store',
            30
        );
        
        // Products submenu
        add_submenu_page(
            'product-manager-cpt',
            __('Products', 'product-manager-cpt'),
            __('Products', 'product-manager-cpt'),
            'manage_options',
            'product-manager-cpt',
            array($this, 'admin_page_products')
        );
        
        // Shortcode submenu
        add_submenu_page(
            'product-manager-cpt',
            __('Shortcodes', 'product-manager-cpt'),
            __('Shortcodes', 'product-manager-cpt'),
            'manage_options',
            'product-manager-shortcodes',
            array($this, 'admin_page_shortcodes')
        );
    }
    
    public function admin_scripts($hook) {
        if (strpos($hook, 'product-manager') !== false) {
            wp_enqueue_media();
            wp_enqueue_editor();
            wp_enqueue_script('pmcpt-admin', PMCPT_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'wp-util'), PMCPT_VERSION, true);
            wp_enqueue_style('pmcpt-admin', PMCPT_PLUGIN_URL . 'assets/css/admin.css', array(), PMCPT_VERSION);
            
            wp_localize_script('pmcpt-admin', 'pmcpt_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pmcpt_nonce'),
                'strings' => array(
                    'confirm_delete' => __('Are you sure you want to delete this product?', 'product-manager-cpt'),
                    'shortcode_copied' => __('Shortcode copied to clipboard!', 'product-manager-cpt'),
                    'error_occurred' => __('An error occurred. Please try again.', 'product-manager-cpt'),
                )
            ));
        }
    }
    
    public function admin_page_products() {
        include PMCPT_PLUGIN_PATH . 'includes/admin-products.php';
    }
    
    public function admin_page_shortcodes() {
        include PMCPT_PLUGIN_PATH . 'includes/admin-shortcodes.php';
    }
    
    public function ajax_save_product() {
        check_ajax_referer('pmcpt_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'product-manager-cpt'));
        }
        
        $product_id = intval($_POST['product_id']);
        $product_name = sanitize_text_field($_POST['product_name']);
        $product_description = wp_kses_post($_POST['product_description']);
        $product_image = intval($_POST['product_image']);
        $product_buttons = json_decode(stripslashes($_POST['product_buttons']), true);
        
        // Sanitize buttons
        $sanitized_buttons = array();
        if (is_array($product_buttons)) {
            foreach ($product_buttons as $button) {
                $sanitized_buttons[] = array(
                    'name' => sanitize_text_field($button['name']),
                    'url' => esc_url_raw($button['url'])
                );
            }
        }
        
        $post_data = array(
            'post_title' => $product_name,
            'post_content' => $product_description,
            'post_status' => 'publish',
            'post_type' => 'pmcpt_product'
        );
        
        if ($product_id > 0) {
            $post_data['ID'] = $product_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
        }
        
        if ($result && !is_wp_error($result)) {
            // Set featured image
            if ($product_image) {
                set_post_thumbnail($result, $product_image);
            }
            
            // Save meta fields
            update_post_meta($result, '_pmcpt_product_buttons', json_encode($sanitized_buttons));
            
            // Generate shortcode
            $shortcode = '[pmcpt_product id="' . $result . '"]';
            update_post_meta($result, '_pmcpt_product_shortcode', $shortcode);
            
            wp_send_json_success(array(
                'message' => __('Product saved successfully!', 'product-manager-cpt'),
                'product_id' => $result,
                'shortcode' => $shortcode
            ));
        } else {
            wp_send_json_error(__('Failed to save product.', 'product-manager-cpt'));
        }
    }
    
    public function ajax_delete_product() {
        check_ajax_referer('pmcpt_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'product-manager-cpt'));
        }
        
        $product_id = intval($_POST['product_id']);
        
        if (wp_delete_post($product_id, true)) {
            wp_send_json_success(__('Product deleted successfully!', 'product-manager-cpt'));
        } else {
            wp_send_json_error(__('Failed to delete product.', 'product-manager-cpt'));
        }
    }
    
    public function ajax_search_products() {
        check_ajax_referer('pmcpt_nonce', 'nonce');
        
        $search_term = sanitize_text_field($_POST['search_term']);
        
        $args = array(
            'post_type' => 'pmcpt_product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            's' => $search_term
        );
        
        $products = get_posts($args);
        $results = array();
        
        foreach ($products as $product) {
            $results[] = $this->format_product_data($product);
        }
        
        wp_send_json_success($results);
    }
    
    private function format_product_data($product) {
        $buttons = get_post_meta($product->ID, '_pmcpt_product_buttons', true);
        $shortcode = get_post_meta($product->ID, '_pmcpt_product_shortcode', true);
        $featured_image = get_the_post_thumbnail_url($product->ID, 'thumbnail');
        
        return array(
            'id' => $product->ID,
            'name' => $product->post_title,
            'description' => $product->post_content,
            'image' => $featured_image ? $featured_image : '',
            'buttons' => $buttons ? json_decode($buttons, true) : array(),
            'shortcode' => $shortcode,
            'date' => get_the_date('Y-m-d H:i:s', $product->ID)
        );
    }
    
    public function activate() {
        $this->register_post_type();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize the plugin
new ProductManagerCPT();

// Shortcode for displaying products
function pmcpt_product_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
    ), $atts, 'pmcpt_product');
    
    $product_id = intval($atts['id']);
    if (!$product_id) {
        return '';
    }
    
    $product = get_post($product_id);
    if (!$product || $product->post_type !== 'pmcpt_product') {
        return '';
    }
    
    $buttons = get_post_meta($product_id, '_pmcpt_product_buttons', true);
    $buttons = $buttons ? json_decode($buttons, true) : array();
    $featured_image = get_the_post_thumbnail_url($product_id, 'medium');
    
    ob_start();
    ?>
    <div class="pmcpt-product-display">
        <?php if ($featured_image): ?>
            <div class="pmcpt-product-image">
                <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($product->post_title); ?>">
            </div>
        <?php endif; ?>
        
        <div class="pmcpt-product-content">
            <h3 class="pmcpt-product-title"><?php echo esc_html($product->post_title); ?></h3>
            <div class="pmcpt-product-description">
                <?php echo wp_kses_post($product->post_content); ?>
            </div>
            
            <?php if (!empty($buttons)): ?>
                <div class="pmcpt-product-buttons">
                    <?php foreach ($buttons as $button): ?>
                        <a href="<?php echo esc_url($button['url']); ?>" class="pmcpt-product-button" target="_blank">
                            <?php echo esc_html($button['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('pmcpt_product', 'pmcpt_product_shortcode');