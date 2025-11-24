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
