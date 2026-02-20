# WP EQ Toolbox

Custom WordPress plugin developed to practice hooks, filters, Settings API and shortcodes.

## Features

- Automatically appends a configurable CTA to single post content.
- Admin configuration via Settings → Reading.
- Shortcode `[eq_cta]` for manual placement.
- Prevents duplication when shortcode is present.
- Conditional style enqueue using `wp_enqueue_scripts`.

## Technical Implementation

### Hooks Used
- `add_action('admin_init')` → Register settings fields.
- `register_setting()` / `add_settings_field()` → Store and render admin options.
- `add_filter('the_content')` → Modify post content dynamically.
- `add_action('wp_enqueue_scripts')` → Conditionally load styles.
- `add_shortcode('eq_cta')` → Render reusable CTA block.
- `has_shortcode()` → Prevent duplicated injection.

### Best Practices Applied
- `is_singular()` and `is_main_query()` checks
- Sanitization using `wp_kses_post()`
- Conditional rendering logic
- Class-based plugin structure

## Installation

1. Copy folder `eq-toolbox` to `wp-content/plugins/`
2. Activate plugin
3. Configure CTA in Settings → Reading

## Author

María Emilia Quiroga
