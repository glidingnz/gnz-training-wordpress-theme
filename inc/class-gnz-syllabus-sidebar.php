<?php
/**
 * Generates the syllabus sidebar navigation from the page hierarchy.
 *
 * @package Gliding_NZ_Training
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GNZ_Syllabus_Sidebar {
    /**
     * Cached headings for topic pages.
     *
     * @var array<int, array<int, array<string, string>>>
     */
    private static $headings_cache = array();

    /**
     * Render the sidebar markup.
     */
    public static function render() {
        $markup = self::generate_markup();

        if ( '' === trim( $markup ) ) {
            echo '<p class="text-muted small px-2">' . esc_html__( 'No pages available yet.', 'gliding-nz-training' ) . '</p>';
            return;
        }

        echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Data escaped during construction.
    }

    /**
     * Build the sidebar markup.
     *
     * @return string
     */
    private static function generate_markup() {
        $all_pages = get_pages( array(
            'sort_column' => 'menu_order,post_title',
            'post_status' => 'publish',
        ) );

        if ( empty( $all_pages ) ) {
            return '';
        }

        $pages_by_parent = array();

        foreach ( $all_pages as $page ) {
            $parent_id = (int) $page->post_parent;

            if ( ! isset( $pages_by_parent[ $parent_id ] ) ) {
                $pages_by_parent[ $parent_id ] = array();
            }

            $pages_by_parent[ $parent_id ][] = $page;
        }

        foreach ( $pages_by_parent as &$group ) {
            usort(
                $group,
                static function ( $a, $b ) {
                    if ( $a->menu_order === $b->menu_order ) {
                        return strcasecmp( $a->post_title, $b->post_title );
                    }

                    return $a->menu_order <=> $b->menu_order;
                }
            );
        }
        unset( $group );

        $root_pages = isset( $pages_by_parent[0] ) ? $pages_by_parent[0] : array();
        $root_pages = apply_filters( 'gnz_syllabus_root_pages', $root_pages, $pages_by_parent );

        if ( empty( $root_pages ) ) {
            return '';
        }

        $current_id = get_queried_object_id();
        $output     = '';

        foreach ( $root_pages as $root_page ) {
            $output .= self::render_root_section( $root_page, $pages_by_parent, $current_id );
        }

        return $output;
    }

    /**
     * Render a top-level section of the sidebar.
     *
     * @param WP_Post $root_page        The top-level page.
     * @param array   $pages_by_parent  Mapping of parent IDs to child pages.
     * @param int     $current_id       The current queried object ID.
     *
     * @return string
     */
    private static function render_root_section( $root_page, $pages_by_parent, $current_id ) {
        if ( ! ( $root_page instanceof WP_Post ) ) {
            return '';
        }

        $children     = isset( $pages_by_parent[ $root_page->ID ] ) ? $pages_by_parent[ $root_page->ID ] : array();
        $is_active    = self::is_in_branch( $root_page->ID, $current_id );
        $root_classes = 'text-uppercase text-muted small fw-bold mt-5 px-2 text-decoration-none';
        $root_classes .= $is_active ? ' primary-text' : ' text-muted';

        $root_link = sprintf(
            '<a href="%1$s" class="%2$s">%3$s</a>',
            esc_url( get_permalink( $root_page ) ),
            esc_attr( $root_classes ),
            esc_html( get_the_title( $root_page ) )
        );

        $output  = "<div class=\"menu-root\">{$root_link}</div>\n";
        $output .= "<div class=\"stages-container mt-3\">\n";

        $stage_counter            = 0;
        $stage_numbering_enabled  = self::is_stage_numbering_enabled( $root_page->ID, $root_page );

        foreach ( $children as $child_page ) {
            $stage_counter++;
            $output .= self::render_stage( $child_page, $pages_by_parent, $current_id, $stage_counter, $stage_numbering_enabled, $root_page );
        }

        $output .= "</div>\n<hr />\n";

        return $output;
    }

    /**
     * Render a stage section.
     *
     * @param WP_Post $stage_page              Stage page object.
     * @param array   $pages_by_parent         Parent/child mapping.
     * @param int     $current_id              Current page ID.
     * @param int     $stage_counter           Current stage position.
     * @param bool    $stage_numbering_enabled Whether numbering is enabled.
     * @param WP_Post $root_page               Root page reference.
     *
     * @return string
     */
    private static function render_stage( $stage_page, $pages_by_parent, $current_id, $stage_counter, $stage_numbering_enabled, $root_page ) {
        if ( ! ( $stage_page instanceof WP_Post ) ) {
            return '';
        }

        $stage_id     = (int) $stage_page->ID;
        $submenu_id   = 'submenu-' . $stage_id;
        $is_open      = self::is_in_branch( $stage_id, $current_id );
        $button_class = 'stage-toggle w-100 d-flex align-items-center justify-content-between btn btn-link text-decoration-none px-2 py-2 primary-text';
        $number_class = 'stage-number d-inline-flex align-items-center justify-content-center me-3 flex-shrink-0';

        if ( ! $stage_numbering_enabled ) {
            $button_class .= ' no-stage-number';
        }

        if ( $is_open ) {
            $button_class .= ' is-open';
            $number_class .= ' text-white accent-bg';
        }

        $output  = "<div class=\"mb-2 menu-item-stage\">\n";
        $output .= sprintf(
            '<button type="button" class="%1$s" data-target="%2$s" aria-expanded="%3$s">',
            esc_attr( $button_class ),
            esc_attr( $submenu_id ),
            $is_open ? 'true' : 'false'
        );
        $output .= '<span class="d-flex align-items-center text-start w-100">';

        if ( $stage_numbering_enabled ) {
            $stage_number = apply_filters( 'gnz_syllabus_stage_number', $stage_counter, $stage_page, $root_page );
            $output      .= sprintf(
                '<span class="%1$s">%2$s</span>',
                esc_attr( $number_class ),
                esc_html( (string) $stage_number )
            );
        }

        $output .= sprintf(
            '<span class="lh-sm flex-grow-1">%s</span>',
            esc_html( get_the_title( $stage_page ) )
        );
        $output .= '</span>';
        $output .= sprintf(
            '<svg class="chevron-icon text-muted %1$s" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>',
            $is_open ? 'rotate-90' : ''
        );
        $output .= '</button>';

        $topics           = isset( $pages_by_parent[ $stage_id ] ) ? $pages_by_parent[ $stage_id ] : array();
        $container_class  = 'ms-4 mt-1 stage-submenu' . ( $is_open ? '' : ' d-none' );

        $output .= sprintf(
            '<div id="%1$s" class="%2$s">',
            esc_attr( $submenu_id ),
            esc_attr( $container_class )
        );

        foreach ( $topics as $topic_page ) {
            $output .= self::render_topic( $topic_page, $current_id );
        }

        $output .= '</div>';
        $output .= "</div>\n";

        return $output;
    }

    /**
     * Render a topic entry.
     *
     * @param WP_Post $topic_page Topic page object.
     * @param int     $current_id Current page ID.
     *
     * @return string
     */
    private static function render_topic( $topic_page, $current_id ) {
        if ( ! ( $topic_page instanceof WP_Post ) ) {
            return '';
        }

        $topic_id     = (int) $topic_page->ID;
        $is_active    = (int) $current_id === $topic_id;
        $headings     = self::get_topic_headings( $topic_id );
        $output       = '<div class="topic-link-wrapper">';

        if ( ! empty( $headings ) ) {
            $submenu_id     = 'topic-submenu-' . $topic_id;
            $button_classes = 'topic-toggle w-100 d-flex align-items-center justify-content-between btn btn-link text-decoration-none px-2 py-2 primary-text';

            if ( $is_active ) {
                $button_classes .= ' link-active-bg fw-bold is-open';
            }

            $output .= sprintf(
                '<button type="button" class="%1$s" data-target="%2$s" aria-expanded="%3$s">',
                esc_attr( $button_classes ),
                esc_attr( $submenu_id ),
                $is_active ? 'true' : 'false'
            );

            $output .= '<span class="d-flex align-items-center text-start w-100">';
            $output .= sprintf(
                '<span class="lh-sm flex-grow-1 topic-toggle-label">%s</span>',
                esc_html( get_the_title( $topic_page ) )
            );
            $output .= '</span>';
            $output .= sprintf(
                '<svg class="chevron-icon text-muted %1$s" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>',
                $is_active ? 'rotate-90' : ''
            );
            $output .= '</button>';

            $container_classes = 'topic-headings mt-2 ps-3 border-start' . ( $is_active ? '' : ' d-none' );
            $permalink         = get_permalink( $topic_page );

            $output .= sprintf(
                '<div id="%1$s" class="%2$s">',
                esc_attr( $submenu_id ),
                esc_attr( $container_classes )
            );
            $output .= sprintf(
                '<a href="%1$s" class="topic-heading-link d-block small text-decoration-none py-1 text-muted" data-heading-id="overview">%2$s</a>',
                esc_url( $permalink ),
                esc_html__( 'Overview', 'gliding-nz-training' )
            );

            foreach ( $headings as $heading ) {
                $heading_id = isset( $heading['id'] ) ? $heading['id'] : '';
                $heading_id = sanitize_title( $heading_id );

                if ( '' === $heading_id ) {
                    continue;
                }

                $heading_text = isset( $heading['text'] ) ? $heading['text'] : '';

                $output .= sprintf(
                    '<a href="%1$s" class="topic-heading-link d-block small text-decoration-none py-1 text-muted" data-heading-id="%3$s">%2$s</a>',
                    esc_url( $permalink . '#' . $heading_id ),
                    esc_html( $heading_text ),
                    esc_attr( $heading_id )
                );
            }

            $output .= '</div>';
        } else {
            $link_classes = 'topic-link d-block text-decoration-none';

            if ( $is_active ) {
                $link_classes .= ' link-active-bg fw-bold';
            }

            $output .= sprintf(
                '<a href="%1$s" class="%2$s">%3$s</a>',
                esc_url( get_permalink( $topic_page ) ),
                esc_attr( $link_classes ),
                esc_html( get_the_title( $topic_page ) )
            );
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Determine whether stage numbering is enabled for a root page.
     *
     * @param int $root_id Root page ID.
     *
     * @return bool
     */
    private static function is_stage_numbering_enabled( $root_id, $root_page = null ) {
        $disabled = get_post_meta( $root_id, '_gnz_disable_stage_numbers', true ) === '1';
        $enabled  = ! $disabled;

        if ( null === $root_page ) {
            $root_page = get_post( $root_id );
        }

        return (bool) apply_filters( 'gnz_syllabus_stage_numbering_enabled', $enabled, $root_page, null );
    }

    /**
     * Check whether the current page sits within a branch.
     *
     * @param int $ancestor_id Ancestor candidate.
     * @param int $current_id  Current ID.
     *
     * @return bool
     */
    private static function is_in_branch( $ancestor_id, $current_id ) {
        if ( ! $ancestor_id || ! $current_id ) {
            return false;
        }

        if ( (int) $ancestor_id === (int) $current_id ) {
            return true;
        }

        $ancestors = get_post_ancestors( $current_id );

        return in_array( (int) $ancestor_id, array_map( 'intval', $ancestors ), true );
    }

    /**
     * Extract page headings for topic navigation.
     *
     * @param int $post_id Page ID.
     *
     * @return array<int, array<string, string>>
     */
    private static function get_topic_headings( $post_id ) {
        if ( isset( self::$headings_cache[ $post_id ] ) ) {
            return self::$headings_cache[ $post_id ];
        }

        $headings = array();
        $post     = get_post( $post_id );

        if ( ! ( $post instanceof WP_Post ) ) {
            self::$headings_cache[ $post_id ] = $headings;
            return $headings;
        }

        if ( ! class_exists( 'DOMDocument' ) ) {
            self::$headings_cache[ $post_id ] = $headings;
            return $headings;
        }

        $content = apply_filters( 'the_content', $post->post_content );

        if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
            self::$headings_cache[ $post_id ] = $headings;
            return $headings;
        }

        $previous = libxml_use_internal_errors( true );

        $dom      = new DOMDocument();
        $html     = '<!DOCTYPE html><html><body>' . $content . '</body></html>';
        $encoding = function_exists( 'mb_convert_encoding' ) ? mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) : $html;

        $dom->loadHTML( $encoding );

        libxml_clear_errors();
        libxml_use_internal_errors( $previous );

        $nodes = $dom->getElementsByTagName( 'h1' );

        foreach ( $nodes as $node ) {
            $text = trim( $node->textContent );

            if ( '' === $text ) {
                continue;
            }

            $id = $node->getAttribute( 'id' );

            if ( '' === $id ) {
                continue;
            }

            $headings[] = array(
                'id'   => $id,
                'text' => $text,
            );
        }

        $headings = apply_filters( 'gnz_syllabus_topic_headings', $headings, $post_id, $post );

        self::$headings_cache[ $post_id ] = $headings;

        return $headings;
    }
}
