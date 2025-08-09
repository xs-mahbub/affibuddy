jQuery(document).ready(function($) {
    'use strict';
    
    // Global variables
    let mediaUploader;
    let buttonRowIndex = 0;
    
    // Initialize
    init();
    
    function init() {
        bindEvents();
        initializeExistingButtons();
    }
    
    function bindEvents() {
        // Form submission
        $('#pmcpt-product-form').on('submit', handleFormSubmit);
        
        // Add button row
        $('#add-button-row').on('click', addButtonRow);
        
        // Image upload
        $('#upload-image-btn, #product-image-placeholder').on('click', openMediaUploader);
        
        // Remove image
        $('#remove-image-btn').on('click', removeProductImage);
        
        // Copy shortcode
        $(document).on('click', '#copy-shortcode-btn, .pmcpt-copy-shortcode-btn', copyShortcode);
        
        // Product search
        $('#product-search').on('input', debounce(searchProducts, 300));
        
        // Delete product
        $(document).on('click', '.pmcpt-delete-product', deleteProduct);
    }
    
    function initializeExistingButtons() {
        const existingButtons = $('#product-buttons-container .pmcpt-button-row');
        buttonRowIndex = existingButtons.length;
        
        // If no existing buttons, add one empty row
        if (buttonRowIndex === 0) {
            addButtonRow();
        }
    }
    
    function handleFormSubmit(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        const formData = collectFormData();
        saveProduct(formData);
    }
    
    function validateForm() {
        const productName = $('#product_name').val().trim();
        
        if (!productName) {
            showMessage('Product name is required.', 'error');
            $('#product_name').focus();
            return false;
        }
        
        return true;
    }
    
    function collectFormData() {
        const productId = $('#product_id').val();
        const productName = $('#product_name').val().trim();
        const productDescription = getEditorContent();
        const productImageId = $('#product_image_id').val();
        const buttons = collectButtonsData();
        
        return {
            product_id: productId,
            product_name: productName,
            product_description: productDescription,
            product_image: productImageId,
            product_buttons: JSON.stringify(buttons),
            action: 'pmcpt_save_product',
            nonce: pmcpt_ajax.nonce
        };
    }
    
    function getEditorContent() {
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('product_description')) {
            return tinyMCE.get('product_description').getContent();
        }
        return $('#product_description').val();
    }
    
    function collectButtonsData() {
        const buttons = [];
        
        $('.pmcpt-button-row').each(function() {
            const name = $(this).find('input[name="button_name[]"]').val().trim();
            const url = $(this).find('input[name="button_url[]"]').val().trim();
            
            if (name && url) {
                buttons.push({
                    name: name,
                    url: url
                });
            }
        });
        
        return buttons;
    }
    
    function saveProduct(formData) {
        showLoadingOverlay();
        
        $.ajax({
            url: pmcpt_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                hideLoadingOverlay();
                
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    
                    // Update shortcode display if it's a new product
                    if (response.data.shortcode && !$('#product-shortcode').length) {
                        updateShortcodeSection(response.data.shortcode);
                    }
                    
                    // Update product ID for future saves
                    if (response.data.product_id) {
                        $('#product_id').val(response.data.product_id);
                    }
                } else {
                    showMessage(response.data || pmcpt_ajax.strings.error_occurred, 'error');
                }
            },
            error: function() {
                hideLoadingOverlay();
                showMessage(pmcpt_ajax.strings.error_occurred, 'error');
            }
        });
    }
    
    function updateShortcodeSection(shortcode) {
        const shortcodeHtml = `
            <div class="pmcpt-form-section">
                <h3>Shortcode</h3>
                <div class="pmcpt-shortcode-display">
                    <input type="text" id="product-shortcode" value="${shortcode}" readonly>
                    <button type="button" id="copy-shortcode-btn" class="pmcpt-btn pmcpt-btn-secondary">
                        <span class="dashicons dashicons-admin-page"></span>
                        Copy
                    </button>
                </div>
            </div>
        `;
        
        $('.pmcpt-form-actions').before(shortcodeHtml);
    }
    
    function addButtonRow() {
        const rowHtml = `
            <div class="pmcpt-button-row" data-index="${buttonRowIndex}">
                <div class="pmcpt-button-fields">
                    <div class="pmcpt-field pmcpt-button-name">
                        <label>Button Name</label>
                        <input type="text" name="button_name[]" placeholder="Enter button name">
                    </div>
                    <div class="pmcpt-field pmcpt-button-url">
                        <label>Button URL</label>
                        <input type="url" name="button_url[]" placeholder="Enter button URL">
                    </div>
                    <div class="pmcpt-button-actions">
                        <button type="button" class="pmcpt-btn pmcpt-btn-remove" onclick="removeButtonRow(this)">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#product-buttons-container').append(rowHtml);
        buttonRowIndex++;
        
        updateRemoveButtonsVisibility();
    }
    
    window.removeButtonRow = function(button) {
        $(button).closest('.pmcpt-button-row').remove();
        updateRemoveButtonsVisibility();
    };
    
    function updateRemoveButtonsVisibility() {
        const buttonRows = $('.pmcpt-button-row');
        const removeButtons = buttonRows.find('.pmcpt-btn-remove');
        
        if (buttonRows.length <= 1) {
            removeButtons.hide();
        } else {
            removeButtons.show();
        }
    }
    
    function openMediaUploader() {
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        mediaUploader = wp.media({
            title: 'Select Product Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            setProductImage(attachment.id, attachment.url);
        });
        
        mediaUploader.open();
    }
    
    function setProductImage(imageId, imageUrl) {
        $('#product_image_id').val(imageId);
        
        const imageHtml = `
            <img id="product-image-preview" src="${imageUrl}" alt="Product Image">
            <button type="button" id="remove-image-btn" class="pmcpt-remove-image">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        `;
        
        $('#product-image-container').html(imageHtml);
    }
    
    function removeProductImage() {
        $('#product_image_id').val('');
        
        const placeholderHtml = `
            <div id="product-image-placeholder" class="pmcpt-image-placeholder">
                <span class="dashicons dashicons-camera"></span>
                <p>Click to upload image</p>
            </div>
        `;
        
        $('#product-image-container').html(placeholderHtml);
    }
    
    function copyShortcode() {
        const shortcode = $(this).data('shortcode') || $('#product-shortcode').val();
        
        if (!shortcode) {
            return;
        }
        
        // Create temporary input to copy text
        const tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(shortcode).select();
        document.execCommand('copy');
        tempInput.remove();
        
        showMessage(pmcpt_ajax.strings.shortcode_copied, 'success');
    }
    
    function searchProducts() {
        const searchTerm = $('#product-search').val().trim();
        
        if (!searchTerm) {
            // Show all products
            $('.pmcpt-product-card').show();
            updateProductsCount($('.pmcpt-product-card').length);
            return;
        }
        
        // Simple client-side search for now
        let visibleCount = 0;
        $('.pmcpt-product-card').each(function() {
            const title = $(this).find('.pmcpt-product-title').text().toLowerCase();
            const excerpt = $(this).find('.pmcpt-product-excerpt').text().toLowerCase();
            const searchLower = searchTerm.toLowerCase();
            
            if (title.includes(searchLower) || excerpt.includes(searchLower)) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        updateProductsCount(visibleCount);
    }
    
    function updateProductsCount(count) {
        $('#products-count').text(count);
    }
    
    function deleteProduct() {
        const productId = $(this).data('product-id');
        const productCard = $(this).closest('.pmcpt-product-card');
        
        if (!confirm(pmcpt_ajax.strings.confirm_delete)) {
            return;
        }
        
        showLoadingOverlay();
        
        $.ajax({
            url: pmcpt_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pmcpt_delete_product',
                product_id: productId,
                nonce: pmcpt_ajax.nonce
            },
            success: function(response) {
                hideLoadingOverlay();
                
                if (response.success) {
                    productCard.fadeOut(300, function() {
                        $(this).remove();
                        updateProductsCount($('.pmcpt-product-card:visible').length);
                        
                        // Show empty state if no products left
                        if ($('.pmcpt-product-card').length === 0) {
                            location.reload();
                        }
                    });
                    showMessage(response.data, 'success');
                } else {
                    showMessage(response.data || pmcpt_ajax.strings.error_occurred, 'error');
                }
            },
            error: function() {
                hideLoadingOverlay();
                showMessage(pmcpt_ajax.strings.error_occurred, 'error');
            }
        });
    }
    
    function showLoadingOverlay() {
        $('#pmcpt-loading-overlay').show();
    }
    
    function hideLoadingOverlay() {
        $('#pmcpt-loading-overlay').hide();
    }
    
    function showMessage(message, type) {
        const messageHtml = `
            <div class="pmcpt-message pmcpt-message-${type}">
                <span class="dashicons dashicons-${type === 'success' ? 'yes' : 'warning'}"></span>
                ${message}
                <button type="button" class="pmcpt-message-close">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
        `;
        
        const $message = $(messageHtml);
        $('#pmcpt-messages').html($message);
        
        // Auto hide after 5 seconds
        setTimeout(function() {
            $message.fadeOut();
        }, 5000);
        
        // Manual close
        $message.find('.pmcpt-message-close').on('click', function() {
            $message.fadeOut();
        });
        
        // Scroll to message
        $('html, body').animate({
            scrollTop: $('#pmcpt-messages').offset().top - 50
        }, 300);
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = function() {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Initialize remove button visibility on page load
    updateRemoveButtonsVisibility();
});