<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap pmcpt-admin-wrap">
    <div class="pmcpt-header">
        <h1 class="wp-heading-inline"><?php _e('Shortcodes', 'product-manager-cpt'); ?></h1>
    </div>

    <div class="pmcpt-shortcodes-container">
        <div class="pmcpt-empty-state">
            <div class="pmcpt-empty-icon">
                <span class="dashicons dashicons-shortcode"></span>
            </div>
            <h3><?php _e('Shortcode Management', 'product-manager-cpt'); ?></h3>
            <p><?php _e('This section will be available in a future update. For now, you can copy shortcodes directly from the Products page.', 'product-manager-cpt'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=product-manager-cpt'); ?>" class="pmcpt-btn pmcpt-btn-primary">
                <span class="dashicons dashicons-arrow-left-alt"></span>
                <?php _e('Go to Products', 'product-manager-cpt'); ?>
            </a>
        </div>
    </div>
</div>