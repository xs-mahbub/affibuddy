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