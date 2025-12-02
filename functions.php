<?php
require_once get_template_directory() . '/inc/class-gnz-syllabus-menu-walker.php';

function gnz_enqueue_scripts() {
    // Bootstrap 5
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2');
    wp_enqueue_script('bootstrap-bundle', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array(), '5.3.2', true);

    // Google Fonts: Inter
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap', array(), null);

    // Theme Styles
    wp_enqueue_style('gnz-style', get_template_directory_uri() . '/assets/css/custom.css', array('bootstrap'), '1.0.0');
    wp_enqueue_style('gnz-main-style', get_stylesheet_uri(), array('gnz-style'), '1.0.0');

    // Theme Scripts
    wp_enqueue_script('gnz-script', get_template_directory_uri() . '/assets/js/main.js', array('bootstrap-bundle'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'gnz_enqueue_scripts');

function gnz_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    register_nav_menus(array(
        'sidebar-menu' => __('Sidebar Syllabus Menu', 'gliding-nz-training'),
    ));
}
add_action('after_setup_theme', 'gnz_setup');

// Add active class to menu items
function gnz_add_active_class($classes, $item) {
    if (in_array('current-menu-item', $classes) || in_array('current-menu-ancestor', $classes)) {
        $classes[] = 'active';
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'gnz_add_active_class', 10, 2);

function gnz_get_highlight_terms() {
    if (!isset($_GET['highlight'])) {
        return array();
    }

    $raw_value = wp_unslash((string) $_GET['highlight']);
    $normalized_value = preg_replace('/\s+/u', ' ', $raw_value);

    if (null === $normalized_value) {
        $normalized_value = $raw_value;
    }

    $normalized = trim($normalized_value);

    if ('' === $normalized) {
        return array();
    }

    $parts = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);

    if (false === $parts || empty($parts)) {
        return array();
    }

    $parts = array_map('sanitize_text_field', $parts);
    $parts = array_filter(array_map('trim', $parts));

    return array_values(array_unique($parts));
}

function gnz_highlight_terms_in_content($content) {
    if (is_admin() || !is_singular() || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $terms = gnz_get_highlight_terms();

    if (empty($terms) || '' === $content) {
        return $content;
    }

    $escaped_terms = array();

    foreach ($terms as $term) {
        $term = preg_quote($term, '/');

        if ('' !== $term) {
            $escaped_terms[] = $term;
        }
    }

    if (empty($escaped_terms)) {
        return $content;
    }

    $pattern = '/(' . implode('|', $escaped_terms) . ')/iu';
    $segments = preg_split('/(<[^>]+>)/u', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

    if (false === $segments || empty($segments)) {
        return $content;
    }

    $skip_stack = array();
    $highlighted_output = '';

    foreach ($segments as $segment) {
        if ('' === $segment) {
            continue;
        }

        if ('<' === $segment[0]) {
            if (preg_match('/^<\s*\/(script|style|mark)\b/i', $segment)) {
                if (!empty($skip_stack)) {
                    array_pop($skip_stack);
                }
            } elseif (preg_match('/^<\s*(script|style|mark)\b/i', $segment)) {
                $skip_stack[] = true;
            }

            $highlighted_output .= $segment;
            continue;
        }

        if (!empty($skip_stack)) {
            $highlighted_output .= $segment;
            continue;
        }

        $replaced = preg_replace($pattern, '<mark class="search-highlight">$1</mark>', $segment);

        if (null === $replaced) {
            $highlighted_output .= $segment;
        } else {
            $highlighted_output .= $replaced;
        }
    }

    return $highlighted_output;
}
add_filter('the_content', 'gnz_highlight_terms_in_content', 20);
