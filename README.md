# Product Manager CPT - WordPress Plugin

A lightweight WordPress plugin for managing products with custom post types and shortcode functionality.

## Features

### 🚀 Core Features
- **Custom Post Type**: Dedicated product management system (`pmcpt_product`)
- **Shortcode Support**: Display products anywhere using `[pmcpt_product id="123"]`
- **Meta Fields**: Support for product buttons and shortcode storage
- **Security**: Proper WordPress standards implementation

### ⚡ Technical Features
- **WordPress Standards**: Follows WordPress coding standards and best practices
- **Translation Ready**: Internationalization support with text domain
- **Lightweight**: Minimal footprint, no admin interface
- **Database Efficient**: Clean post type and meta field registration

## Installation

1. **Upload the Plugin**
   ```
   Upload the entire plugin folder to `/wp-content/plugins/`
   ```

2. **Activate the Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Product Manager CPT"
   - Click "Activate"

## Usage

### Creating Products Programmatically

Since this plugin doesn't include an admin interface, products need to be created programmatically:

```php
// Create a new product
$product_id = wp_insert_post(array(
    'post_title' => 'My Product',
    'post_content' => 'Product description here',
    'post_type' => 'pmcpt_product',
    'post_status' => 'publish'
));

// Add product buttons
$buttons = array(
    array('name' => 'Buy Now', 'url' => 'https://example.com/buy'),
    array('name' => 'Learn More', 'url' => 'https://example.com/info')
);
update_post_meta($product_id, '_pmcpt_product_buttons', json_encode($buttons));

// Generate shortcode
$shortcode = '[pmcpt_product id="' . $product_id . '"]';
update_post_meta($product_id, '_pmcpt_product_shortcode', $shortcode);
```

### Using Shortcodes

#### Display Products
Use the shortcode to display products anywhere in your content:
```
[pmcpt_product id="123"]
```

#### Frontend Display
The shortcode renders a product display with:
- Product image (if available)
- Product title
- Product description
- Action buttons with links

## File Structure

```
product-manager-cpt/
├── product-manager-cpt.php          # Main plugin file
└── README.md                        # Documentation
```

## Technical Details

### Custom Post Type
- **Post Type**: `pmcpt_product`
- **Visibility**: Hidden from public, not searchable
- **Supports**: Title, editor, thumbnail

### Meta Fields
- `_pmcpt_product_buttons`: JSON-encoded array of button data
- `_pmcpt_product_shortcode`: Auto-generated shortcode for the product

### WordPress Hooks
- `init`: Register post type and meta fields
- Activation/Deactivation hooks for cleanup

## Customization

### Styling
The shortcode output uses CSS classes that you can style:
```css
.pmcpt-product-display { /* Main container */ }
.pmcpt-product-image { /* Image wrapper */ }
.pmcpt-product-content { /* Content wrapper */ }
.pmcpt-product-title { /* Product title */ }
.pmcpt-product-description { /* Product description */ }
.pmcpt-product-buttons { /* Buttons container */ }
.pmcpt-product-button { /* Individual button */ }
```

### Extending Functionality
- **Add Meta Fields**: Extend the `register_meta_fields()` method
- **Modify Shortcode**: Customize the `pmcpt_product_shortcode()` function
- **Add Hooks**: Use WordPress actions and filters

## Browser Support

- **All Modern Browsers**: Works with any browser that supports standard HTML/CSS
- **No JavaScript Dependencies**: Pure server-side rendering

## Security Features

- **WordPress Standards**: Uses WordPress APIs for all database operations
- **Sanitization**: All data properly sanitized when stored
- **Capabilities**: Respects WordPress user capabilities

## Performance

- **Lightweight**: Minimal code footprint
- **Efficient Queries**: Uses WordPress standard functions
- **No Admin Assets**: No CSS/JavaScript loading in admin
- **Caching Friendly**: Compatible with WordPress caching plugins

## Troubleshooting

### Common Issues

1. **Shortcodes not displaying**
   - Verify the product exists: `get_post($product_id)`
   - Check shortcode syntax: `[pmcpt_product id="123"]`
   - Ensure post type is registered properly

2. **Missing product data**
   - Check meta fields exist: `get_post_meta($product_id, '_pmcpt_product_buttons', true)`
   - Verify JSON encoding of button data
   - Ensure product is published status

### Debug Mode
Enable WordPress debug mode for detailed error information:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## API Reference

### Functions

#### `pmcpt_product_shortcode($atts)`
Renders a product display based on provided ID.

**Parameters:**
- `$atts['id']` (int): Product ID to display

**Returns:** HTML string of product display

### Hooks

#### Actions
- `pmcpt_after_register_post_type`: Fired after post type registration
- `pmcpt_after_register_meta_fields`: Fired after meta field registration

#### Filters
- `pmcpt_product_shortcode_output`: Filter shortcode output HTML
- `pmcpt_product_button_html`: Filter individual button HTML

## Contributing

This plugin follows WordPress coding standards. When contributing:

1. Follow WordPress PHP coding standards
2. Use proper sanitization and validation
3. Test across different WordPress versions
4. Document any new features or changes

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support and feature requests, please refer to the plugin documentation.

---

**Version**: 1.0.0  
**Requires WordPress**: 5.0 or higher  
**Tested up to**: WordPress 6.4  
**PHP Version**: 7.4 or higher