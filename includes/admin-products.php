<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current action
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// Get product data if editing
$product_data = null;
if ($action === 'edit' && $product_id) {
    $product = get_post($product_id);
    if ($product && $product->post_type === 'pmcpt_product') {
        $buttons = get_post_meta($product_id, '_pmcpt_product_buttons', true);
        $shortcode = get_post_meta($product_id, '_pmcpt_product_shortcode', true);
        $featured_image_id = get_post_thumbnail_id($product_id);
        
        $product_data = array(
            'id' => $product_id,
            'name' => $product->post_title,
            'description' => $product->post_content,
            'image_id' => $featured_image_id,
            'image_url' => $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'medium') : '',
            'buttons' => $buttons ? json_decode($buttons, true) : array(),
            'shortcode' => $shortcode
        );
    }
}

// Get all products for listing
$products = get_posts(array(
    'post_type' => 'pmcpt_product',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC'
));
?>

<div class="wrap pmcpt-admin-wrap">
    <div class="pmcpt-header">
        <h1 class="wp-heading-inline">
            <?php 
            if ($action === 'add') {
                echo __('Add New Product', 'product-manager-cpt');
            } elseif ($action === 'edit') {
                echo __('Edit Product', 'product-manager-cpt');
            } else {
                echo __('Products', 'product-manager-cpt');
            }
            ?>
        </h1>
        
        <?php if ($action === 'list'): ?>
            <a href="<?php echo admin_url('admin.php?page=product-manager-cpt&action=add'); ?>" class="page-title-action">
                <?php _e('Add New Product', 'product-manager-cpt'); ?>
            </a>
        <?php else: ?>
            <a href="<?php echo admin_url('admin.php?page=product-manager-cpt'); ?>" class="page-title-action">
                <?php _e('Back to Products', 'product-manager-cpt'); ?>
            </a>
        <?php endif; ?>
    </div>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Product Form -->
        <div class="pmcpt-form-container">
            <form id="pmcpt-product-form" class="pmcpt-product-form">
                <input type="hidden" id="product_id" name="product_id" value="<?php echo $product_data ? $product_data['id'] : 0; ?>">
                
                <div class="pmcpt-form-grid">
                    <!-- Left Column -->
                    <div class="pmcpt-form-column pmcpt-form-main">
                        <div class="pmcpt-form-section">
                            <h3><?php _e('Product Information', 'product-manager-cpt'); ?></h3>
                            
                            <div class="pmcpt-field">
                                <label for="product_name"><?php _e('Product Name', 'product-manager-cpt'); ?> <span class="required">*</span></label>
                                <input type="text" id="product_name" name="product_name" value="<?php echo $product_data ? esc_attr($product_data['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="pmcpt-field">
                                <label for="product_description"><?php _e('Product Description', 'product-manager-cpt'); ?></label>
                                <?php
                                $content = $product_data ? $product_data['description'] : '';
                                wp_editor($content, 'product_description', array(
                                    'textarea_name' => 'product_description',
                                    'textarea_rows' => 10,
                                    'media_buttons' => true,
                                    'teeny' => false,
                                    'tinymce' => array(
                                        'toolbar1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,blockquote,|,link,unlink,|,undo,redo',
                                        'toolbar2' => 'formatselect,|,forecolor,backcolor,|,alignleft,aligncenter,alignright,alignjustify,|,outdent,indent,|,wp_more',
                                    ),
                                ));
                                ?>
                            </div>
                        </div>
                        
                        <div class="pmcpt-form-section">
                            <h3><?php _e('Product Buttons', 'product-manager-cpt'); ?></h3>
                            <div id="product-buttons-container">
                                <?php
                                $buttons = $product_data ? $product_data['buttons'] : array();
                                if (empty($buttons)) {
                                    $buttons = array(array('name' => '', 'url' => ''));
                                }
                                foreach ($buttons as $index => $button):
                                ?>
                                <div class="pmcpt-button-row" data-index="<?php echo $index; ?>">
                                    <div class="pmcpt-button-fields">
                                        <div class="pmcpt-field pmcpt-button-name">
                                            <label><?php _e('Button Name', 'product-manager-cpt'); ?></label>
                                            <input type="text" name="button_name[]" value="<?php echo esc_attr($button['name']); ?>" placeholder="<?php _e('Enter button name', 'product-manager-cpt'); ?>">
                                        </div>
                                        <div class="pmcpt-field pmcpt-button-url">
                                            <label><?php _e('Button URL', 'product-manager-cpt'); ?></label>
                                            <input type="url" name="button_url[]" value="<?php echo esc_attr($button['url']); ?>" placeholder="<?php _e('Enter button URL', 'product-manager-cpt'); ?>">
                                        </div>
                                        <div class="pmcpt-button-actions">
                                            <button type="button" class="pmcpt-btn pmcpt-btn-remove" onclick="removeButtonRow(this)">
                                                <span class="dashicons dashicons-trash"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="add-button-row" class="pmcpt-btn pmcpt-btn-secondary">
                                <span class="dashicons dashicons-plus-alt"></span>
                                <?php _e('Add Button', 'product-manager-cpt'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="pmcpt-form-column pmcpt-form-sidebar">
                        <div class="pmcpt-form-section">
                            <h3><?php _e('Product Image', 'product-manager-cpt'); ?></h3>
                            <div class="pmcpt-image-upload">
                                <div id="product-image-container" class="pmcpt-image-container">
                                    <?php if ($product_data && $product_data['image_url']): ?>
                                        <img id="product-image-preview" src="<?php echo esc_url($product_data['image_url']); ?>" alt="<?php _e('Product Image', 'product-manager-cpt'); ?>">
                                        <button type="button" id="remove-image-btn" class="pmcpt-remove-image">
                                            <span class="dashicons dashicons-no-alt"></span>
                                        </button>
                                    <?php else: ?>
                                        <div id="product-image-placeholder" class="pmcpt-image-placeholder">
                                            <span class="dashicons dashicons-camera"></span>
                                            <p><?php _e('Click to upload image', 'product-manager-cpt'); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" id="product_image_id" name="product_image_id" value="<?php echo $product_data ? $product_data['image_id'] : ''; ?>">
                                <button type="button" id="upload-image-btn" class="pmcpt-btn pmcpt-btn-secondary pmcpt-upload-btn">
                                    <span class="dashicons dashicons-upload"></span>
                                    <?php _e('Upload Image', 'product-manager-cpt'); ?>
                                </button>
                            </div>
                        </div>
                        
                        <?php if ($product_data && $product_data['shortcode']): ?>
                        <div class="pmcpt-form-section">
                            <h3><?php _e('Shortcode', 'product-manager-cpt'); ?></h3>
                            <div class="pmcpt-shortcode-display">
                                <input type="text" id="product-shortcode" value="<?php echo esc_attr($product_data['shortcode']); ?>" readonly>
                                <button type="button" id="copy-shortcode-btn" class="pmcpt-btn pmcpt-btn-secondary">
                                    <span class="dashicons dashicons-admin-page"></span>
                                    <?php _e('Copy', 'product-manager-cpt'); ?>
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="pmcpt-form-actions">
                            <button type="submit" class="pmcpt-btn pmcpt-btn-primary" id="save-product-btn">
                                <span class="dashicons dashicons-yes"></span>
                                <?php echo $product_data ? __('Update Product', 'product-manager-cpt') : __('Save Product', 'product-manager-cpt'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    
    <?php else: ?>
        <!-- Products List -->
        <div class="pmcpt-products-container">
            <div class="pmcpt-list-header">
                <div class="pmcpt-search-container">
                    <input type="text" id="product-search" placeholder="<?php _e('Search products...', 'product-manager-cpt'); ?>">
                    <span class="dashicons dashicons-search"></span>
                </div>
                <div class="pmcpt-list-info">
                    <span id="products-count"><?php echo count($products); ?></span> <?php _e('products found', 'product-manager-cpt'); ?>
                </div>
            </div>
            
            <div class="pmcpt-products-grid" id="products-grid">
                <?php foreach ($products as $product): 
                    $buttons = get_post_meta($product->ID, '_pmcpt_product_buttons', true);
                    $shortcode = get_post_meta($product->ID, '_pmcpt_product_shortcode', true);
                    $featured_image = get_the_post_thumbnail_url($product->ID, 'thumbnail');
                    $buttons_data = $buttons ? json_decode($buttons, true) : array();
                ?>
                <div class="pmcpt-product-card" data-product-id="<?php echo $product->ID; ?>">
                    <div class="pmcpt-product-card-header">
                        <?php if ($featured_image): ?>
                            <div class="pmcpt-product-thumbnail">
                                <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($product->post_title); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="pmcpt-product-info">
                            <h3 class="pmcpt-product-title"><?php echo esc_html($product->post_title); ?></h3>
                            <div class="pmcpt-product-meta">
                                <span class="pmcpt-product-date"><?php echo get_the_date('M j, Y', $product->ID); ?></span>
                                <?php if (!empty($buttons_data)): ?>
                                    <span class="pmcpt-product-buttons-count"><?php echo count($buttons_data); ?> <?php _e('buttons', 'product-manager-cpt'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="pmcpt-product-card-body">
                        <?php if ($product->post_content): ?>
                            <div class="pmcpt-product-excerpt">
                                <?php echo wp_trim_words(strip_tags($product->post_content), 20, '...'); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($shortcode): ?>
                            <div class="pmcpt-shortcode-row">
                                <input type="text" class="pmcpt-shortcode-input" value="<?php echo esc_attr($shortcode); ?>" readonly>
                                <button type="button" class="pmcpt-copy-shortcode-btn pmcpt-btn pmcpt-btn-sm" data-shortcode="<?php echo esc_attr($shortcode); ?>">
                                    <span class="dashicons dashicons-admin-page"></span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="pmcpt-product-card-footer">
                        <div class="pmcpt-product-actions">
                            <a href="<?php echo admin_url('admin.php?page=product-manager-cpt&action=edit&product_id=' . $product->ID); ?>" class="pmcpt-btn pmcpt-btn-sm pmcpt-btn-primary">
                                <span class="dashicons dashicons-edit"></span>
                                <?php _e('Edit', 'product-manager-cpt'); ?>
                            </a>
                            <button type="button" class="pmcpt-btn pmcpt-btn-sm pmcpt-btn-danger pmcpt-delete-product" data-product-id="<?php echo $product->ID; ?>">
                                <span class="dashicons dashicons-trash"></span>
                                <?php _e('Delete', 'product-manager-cpt'); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($products)): ?>
                <div class="pmcpt-empty-state">
                    <div class="pmcpt-empty-icon">
                        <span class="dashicons dashicons-store"></span>
                    </div>
                    <h3><?php _e('No products found', 'product-manager-cpt'); ?></h3>
                    <p><?php _e('Get started by creating your first product.', 'product-manager-cpt'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=product-manager-cpt&action=add'); ?>" class="pmcpt-btn pmcpt-btn-primary">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php _e('Add New Product', 'product-manager-cpt'); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Loading overlay -->
    <div id="pmcpt-loading-overlay" class="pmcpt-loading-overlay" style="display: none;">
        <div class="pmcpt-loading-spinner">
            <div class="pmcpt-spinner"></div>
            <p><?php _e('Processing...', 'product-manager-cpt'); ?></p>
        </div>
    </div>
    
    <!-- Success/Error Messages -->
    <div id="pmcpt-messages" class="pmcpt-messages"></div>
</div>