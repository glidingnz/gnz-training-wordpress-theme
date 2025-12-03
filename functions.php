<?php
require_once get_template_directory() . '/inc/class-gnz-syllabus-sidebar.php';

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
}
add_action('after_setup_theme', 'gnz_setup');

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

function gnz_get_placeholder_page_ids($post_type = 'page') {
    static $cache = array();

    if (isset($cache[$post_type])) {
        return $cache[$post_type];
    }

    if (!post_type_exists($post_type)) {
        $cache[$post_type] = array();
        return $cache[$post_type];
    }

    $page_ids = get_pages(array(
        'fields'      => 'ids',
        'post_type'   => $post_type,
        'post_status' => array('publish'),
        'hierarchical'=> false,
    ));

    if (empty($page_ids)) {
        $cache[$post_type] = array();
        return $cache[$post_type];
    }

    $placeholder_ids = array();

    foreach ($page_ids as $page_id) {
        $depth = count(get_post_ancestors($page_id));

        if ($depth <= 1) {
            $placeholder_ids[] = (int) $page_id;
        }
    }

    $cache[$post_type] = $placeholder_ids;

    return $cache[$post_type];
}

function gnz_is_placeholder_page($post) {
    $post_obj = get_post($post);

    if (!$post_obj instanceof WP_Post) {
        return false;
    }

    $placeholders = gnz_get_placeholder_page_ids($post_obj->post_type);

    if (empty($placeholders)) {
        return false;
    }

    return in_array((int) $post_obj->ID, $placeholders, true);
}

function gnz_exclude_placeholder_pages_from_search($query) {
    if (!($query instanceof WP_Query)) {
        return;
    }

    if (is_admin() || !$query->is_main_query() || !$query->is_search()) {
        return;
    }

    $placeholders = gnz_get_placeholder_page_ids('page');

    if (empty($placeholders)) {
        return;
    }

    $existing = $query->get('post__not_in');

    if (!is_array($existing)) {
        $existing = array();
    }

    $query->set('post__not_in', array_values(array_unique(array_merge($existing, $placeholders))));
}
add_action('pre_get_posts', 'gnz_exclude_placeholder_pages_from_search');

function gnz_register_sidebar_meta_box() {
    add_meta_box(
        'gnz-sidebar-options',
        __('Sidebar Options', 'gliding-nz-training'),
        'gnz_render_sidebar_meta_box',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'gnz_register_sidebar_meta_box');

function gnz_render_sidebar_meta_box($post) {
    wp_nonce_field('gnz_sidebar_meta_box', 'gnz_sidebar_meta_box_nonce');

    $has_parent = wp_get_post_parent_id($post->ID);

    if ($has_parent) {
        echo '<p class="description">' . esc_html__( 'Stage numbering can only be managed on top-level pages.', 'gliding-nz-training' ) . '</p>';
        return;
    }

    $disabled = get_post_meta($post->ID, '_gnz_disable_stage_numbers', true);
    ?>
    <p>
        <label>
            <input type="checkbox" name="gnz_disable_stage_numbers" value="1" <?php checked('1', $disabled); ?> />
            <?php esc_html_e('Hide stage numbering for this section in the sidebar', 'gliding-nz-training'); ?>
        </label>
    </p>
    <?php
}

function gnz_save_sidebar_meta_box($post_id) {
    if (!isset($_POST['gnz_sidebar_meta_box_nonce']) || !wp_verify_nonce($_POST['gnz_sidebar_meta_box_nonce'], 'gnz_sidebar_meta_box')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    $is_top_level = !wp_get_post_parent_id($post_id);

    if (!$is_top_level) {
        delete_post_meta($post_id, '_gnz_disable_stage_numbers');
        return;
    }

    $value = isset($_POST['gnz_disable_stage_numbers']) ? '1' : '';

    if ('' === $value) {
        delete_post_meta($post_id, '_gnz_disable_stage_numbers');
    } else {
        update_post_meta($post_id, '_gnz_disable_stage_numbers', '1');
    }
}
add_action('save_post_page', 'gnz_save_sidebar_meta_box');

function gnz_disable_nav_menu_screens() {
    if (!apply_filters('gnz_disable_nav_menu_screen', true)) {
        return;
    }

    remove_menu_page('nav-menus.php');
}
add_action('admin_menu', 'gnz_disable_nav_menu_screens', 999);

function gnz_disable_nav_menu_admin_bar($wp_admin_bar) {
    if (!apply_filters('gnz_disable_nav_menu_screen', true)) {
        return;
    }

    $wp_admin_bar->remove_menu('menus');
}
add_action('admin_bar_menu', 'gnz_disable_nav_menu_admin_bar', 999);

function gnz_disable_nav_menu_customizer($wp_customize) {
    if (!apply_filters('gnz_disable_nav_menu_screen', true)) {
        return;
    }

    if (isset($wp_customize->nav_menus)) {
        $wp_customize->remove_panel('nav_menus');
    }
}
add_action('customize_register', 'gnz_disable_nav_menu_customizer', 20);
