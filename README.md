# Polylang Social Links

A WordPress plugin that enables language-specific social media links for multilingual websites using Polylang. Display different social media profiles for each language with an easy-to-use interface.

## 🚀 Features

- **Language-Specific Links**: Define different social media links for each language
- **Custom SVG Icons**: Add custom SVG icons for each social link
- **Widget & Shortcode**: Display links using a widget or shortcode `[polylang_social_links]`. Widget is called Polylang Social Links and can be called anywhere in theme and also in astra theme add a cusotm widget and then seearch for this widget.
- **Customizable Appearance**:
  - Icon color (hex code)
  - Icon width and height
  - Left/right margins between icons
- **New Tab Support**: Option to open links in a new tab
- **Drag & Drop Reordering**: Easily reorder your social links
- **Polylang Integration**: Seamless integration with Polylang for multilingual support

## ⚙️ Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- [Polylang](https://wordpress.org/plugins/polylang/) plugin (Free or Pro) - **Required**

## 📥 Installation

1. Upload the `polylang-social-links` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Settings → Polylang Social Links** to configure your social links

## 📝 Usage

### Adding Social Links

1. Go to **Settings → Polylang Social Links**
2. Select the language you want to add links for from the dropdown
3. Click "Add New Link" to add a social media profile
4. For each link:
   - **URL**: Enter the full URL to your social media profile
   - **SVG Icon**: Paste the SVG code for the social media icon
   - **Open in new tab**: Check if you want the link to open in a new tab
5. Click "Save Changes"

### Using the Widget

1. Go to **Appearance → Widgets**
2. Find the "Polylang Social Links" widget
3. Drag it to your desired widget area
4. Configure the widget settings:
   - **Title**: Optional widget title
   - **Icon Color**: Hex color code (e.g., #2f5d41)
   - **Icon Width/Height**: Size of the icons in pixels (default: 18px)
   - **Left/Right Margin**: Spacing between icons in pixels (default: 5px)
5. Click "Save"

### Using the Shortcode

Display social links anywhere using this shortcode:

```
[polylang_social_links title="Follow Us" class="custom-class"]
```

**Parameters:**
- `title`: Optional title to display above the links
- `class`: Optional CSS class to add to the container

## 🎨 Customization

### Styling

Customize the appearance with these CSS classes:

```css
.psl-social-links {
    display: flex;
    flex-wrap: wrap;
    gap: 10px; /* Adjust gap between icons */
}

.psl-social-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.2s ease;
}

.psl-social-link:hover {
    opacity: 0.8;
}
```

### For Developers

#### Filters

```php
// Modify social links before display
add_filter('psl_social_links', function($links, $language) {
    // $links contains the array of social links
    // $language is the current language code
    return $links;
}, 10, 2);

// Modify widget arguments
add_filter('psl_widget_args', function($args) {
    // $args contains the widget arguments
    return $args;
});
```

## ❓ FAQ

### The widget/shortcode isn't showing anything
- Ensure Polylang is installed and activated
- Verify you've added social links for the current language
- Check for JavaScript errors in your browser's console

### Can I use Font Awesome or other icon libraries?
Yes! Use the SVG version of icons from any library. For Font Awesome, copy the SVG code from their website.

### How do I add more languages?
Add languages in **Languages → Add New Language** in your WordPress admin. The plugin will automatically detect them.

## 📜 Changelog

### 1.0.0
* Initial release with widget and shortcode support
* Language-specific social links
* Customizable icon appearance
* Responsive design
- Responsive admin interface
- Works with any WordPress theme

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- Polylang plugin (free or Pro)

## Installation

1. Upload the `polylang-social-links` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Polylang Social Links to configure your social links

## Usage

### Adding Social Links

1. Go to Settings > Polylang Social Links
2. Select the language you want to add social links for from the tabs
3. Click "Add Social Link" to add a new social media link
4. Paste the SVG code for the social media icon in the SVG field
5. Enter the URL for the social media profile
6. Check "Open in new tab" if you want the link to open in a new tab
7. Click "Save Changes" to save your settings

### Displaying Social Links

#### Using the Widget

1. Go to Appearance > Widgets
2. Find the "Polylang Social Links" widget
3. Drag it to your desired widget area
4. Add a title (optional) and click "Save"

#### Using a Shortcode

```
[polylang_social_links]
```

#### Using PHP Code

```php
if (function_exists('polylang_social_links_display')) {
    polylang_social_links_display();
}
```

## Styling

The social links are output with the following HTML structure:

```html
<div class="psl-social-links">
    <a href="https://example.com" class="psl-social-link" target="_blank" rel="noopener noreferrer">
        <!-- SVG icon -->
    </a>
    <!-- More social links -->
</div>
```

You can style the social links by targeting the `.psl-social-links` and `.psl-social-link` classes in your theme's CSS.

## Support

For support, please open an issue on the [GitHub repository](https://github.com/codedrillinfotech/polylang-social-links).

## License

GPL v2 or later
