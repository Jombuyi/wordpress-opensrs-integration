# WordPress OpenSRS Integration

A WordPress plugin that adds a responsive form to the sidebar with AJAX submission capabilities to the OpenSRS API.

## Features

- Gutenberg block integration for easy form placement
- Configurable form position (left or right sidebar)
- AJAX form submission with loading spinner
- Success/error feedback for form submissions
- Responsive design that works well in sidebars
- Integration with OpenSRS API for form processing

## Installation

1. Upload the `wordpress-opensrs-integration` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the 'OpenSRS Form' block to your post or page using the Gutenberg editor

## Usage

### Gutenberg Block

1. Edit a post or page using the Gutenberg editor
2. Search for the 'OpenSRS Form' block in the block inserter
3. Configure the form settings in the block sidebar:
   - Toggle form visibility with the 'Enable Form' option
   - Select the sidebar position (left or right)

### Shortcode

Alternatively, you can use the shortcode to insert the form:

```
[opensrs_form position="right" enabled="true"]
```

Parameters:

- `position`: Can be 'left' or 'right'. Default is 'right'.
- `enabled`: Can be 'true' or 'false'. Default is 'true'.

## API Integration

By default, the plugin is configured to send form data to a placeholder OpenSRS API endpoint. To use your OpenSRS API:

1. Go to Settings > OpenSRS Integration Settings
2. Enter your API endpoint URL and API key
3. Save changes

## Styling

The form is styled to work well in sidebars, but you can customize the appearance by:

1. Adding custom CSS to your theme
2. Modifying the included CSS files in the plugin's `assets/css` directory

## Changelog

### 1.0.0

- Initial release

## License

This plugin is licensed under the GPL v2 or later.
