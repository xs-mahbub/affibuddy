# Product Manager CPT - WordPress Plugin

A comprehensive WordPress plugin for managing products with custom post types, featuring a modern UI, AJAX functionality, and easy shortcode integration.

## Features

### 🚀 Core Features
- **Custom Post Type**: Dedicated product management system
- **Modern Admin Interface**: Clean, responsive design with intuitive navigation
- **AJAX-Powered**: Seamless user experience without page reloads
- **Image Management**: Easy product image upload and management
- **Rich Text Editor**: WordPress classic editor support for product descriptions
- **Dynamic Buttons**: Add multiple action buttons with custom names and URLs
- **Auto-Generated Shortcodes**: Automatic shortcode creation for each product
- **Search & Filter**: Real-time product search functionality
- **Copy to Clipboard**: One-click shortcode copying

### 📱 User Interface
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile
- **Card-Based Layout**: Modern product cards with hover effects
- **Loading States**: Professional loading animations and progress indicators
- **Success/Error Messages**: Toast-style notifications with auto-dismiss
- **Empty States**: Helpful guidance when no products exist

### ⚡ Technical Features
- **Security**: Proper nonce verification and user capability checks
- **Sanitization**: All input data properly sanitized and validated
- **WordPress Standards**: Follows WordPress coding standards and best practices
- **Translation Ready**: Internationalization support with text domain
- **Optimized Performance**: Efficient database queries and asset loading

## Installation

1. **Upload the Plugin**
   ```
   Upload the entire plugin folder to `/wp-content/plugins/`
   ```

2. **Activate the Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Product Manager CPT"
   - Click "Activate"

3. **Access the Plugin**
   - Navigate to "Product Manager" in your WordPress admin menu

## Usage

### Adding a New Product

1. **Go to Product Manager → Products**
2. **Click "Add New Product"**
3. **Fill in Product Information:**
   - **Product Name** (required)
   - **Product Description** (optional, supports rich text)
   
4. **Upload Product Image:**
   - Click the image upload area or "Upload Image" button
   - Select an image from your media library
   - Remove image with the X button if needed

5. **Add Product Buttons:**
   - Enter button name and URL
   - Click "Add Button" to add more buttons
   - Remove buttons with the trash icon
   - At least one button row is always maintained

6. **Save the Product:**
   - Click "Save Product" or "Update Product"
   - Shortcode is automatically generated

### Managing Products

#### Product List View
- **Search**: Use the search box to find products by name or description
- **Edit**: Click the "Edit" button on any product card
- **Delete**: Click the "Delete" button (with confirmation)
- **Copy Shortcode**: Click the copy icon next to the shortcode

#### Product Cards Display
Each product card shows:
- Product thumbnail (if image exists)
- Product name and creation date
- Number of buttons configured
- Description excerpt
- Generated shortcode with copy button
- Edit and Delete actions

### Using Shortcodes

#### Automatic Generation
Each product automatically gets a shortcode in the format:
```
[pmcpt_product id="123"]
```

#### Frontend Display
The shortcode renders a product display with:
- Product image (if available)
- Product title
- Product description
- Action buttons with links

#### Copy Shortcodes
- Copy from the product edit page
- Copy from the product list page
- Click the copy icon for instant clipboard copy

## File Structure

```
product-manager-cpt/
├── product-manager-cpt.php          # Main plugin file
├── includes/
│   ├── admin-products.php           # Products admin page
│   └── admin-shortcodes.php         # Shortcodes admin page
├── assets/
│   ├── css/
│   │   └── admin.css               # Admin styles
│   └── js/
│       └── admin.js                # Admin JavaScript
└── README.md                       # Documentation
```

## Technical Details

### Custom Post Type
- **Post Type**: `pmcpt_product`
- **Visibility**: Hidden from public, managed through admin interface
- **Supports**: Title, editor, thumbnail

### Meta Fields
- `_pmcpt_product_buttons`: JSON-encoded array of button data
- `_pmcpt_product_shortcode`: Auto-generated shortcode for the product

### AJAX Actions
- `pmcpt_save_product`: Save/update product data
- `pmcpt_delete_product`: Delete a product
- `pmcpt_search_products`: Search products (for future enhancement)

### WordPress Hooks
- `init`: Register post type and meta fields
- `admin_menu`: Add admin menu pages
- `admin_enqueue_scripts`: Load admin assets
- `wp_ajax_*`: Handle AJAX requests

## Customization

### Styling
The plugin uses CSS custom properties and follows WordPress admin design patterns. You can customize:

- **Colors**: Modify CSS variables in `admin.css`
- **Layout**: Adjust grid systems and spacing
- **Components**: Customize button styles, card layouts, etc.

### Functionality
- **Button Validation**: Modify validation rules in JavaScript
- **Field Types**: Add new meta fields in the main plugin file
- **UI Components**: Extend the admin interface

## Browser Support

- **Modern Browsers**: Chrome, Firefox, Safari, Edge (latest versions)
- **Mobile Browsers**: iOS Safari, Chrome Mobile
- **Responsive**: Fully responsive design for all screen sizes

## Security Features

- **Nonce Verification**: All AJAX requests verified with nonces
- **Capability Checks**: User permissions validated
- **Data Sanitization**: All input data properly sanitized
- **SQL Injection Prevention**: Using WordPress APIs for database operations

## Performance

- **Lazy Loading**: Assets loaded only on plugin pages
- **Optimized Queries**: Efficient database operations
- **Minified Assets**: Compressed CSS and JavaScript (for production)
- **Caching Friendly**: Compatible with WordPress caching plugins

## Troubleshooting

### Common Issues

1. **Images not uploading**
   - Check WordPress media upload permissions
   - Verify file size limits
   - Ensure proper WordPress media library access

2. **AJAX not working**
   - Check browser console for JavaScript errors
   - Verify WordPress AJAX URL is accessible
   - Ensure proper nonce generation

3. **Shortcodes not displaying**
   - Verify the product exists and is published
   - Check shortcode syntax: `[pmcpt_product id="123"]`
   - Ensure proper WordPress post type registration

### Debug Mode
Enable WordPress debug mode for detailed error information:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Contributing

This plugin follows WordPress coding standards. When contributing:

1. Follow WordPress PHP coding standards
2. Use proper sanitization and validation
3. Test across different WordPress versions
4. Ensure responsive design compatibility
5. Document any new features or changes

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support and feature requests, please refer to the plugin documentation or contact the developer.

---

**Version**: 1.0.0  
**Requires WordPress**: 5.0 or higher  
**Tested up to**: WordPress 6.4  
**PHP Version**: 7.4 or higher