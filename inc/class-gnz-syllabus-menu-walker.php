<?php
/**
 * Custom walker to render the syllabus sidebar navigation.
 *
 * @package Gliding_NZ_Training
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GNZ_Syllabus_Menu_Walker extends Walker_Nav_Menu {
    /**
     * Counter for numbering syllabus stages.
     *
     * @var int
     */
    private $stage_counter = 0;

    /**
     * Tracks the submenu id for the current stage.
     *
     * @var string
     */
    private $current_submenu_id = '';

    /**
     * Whether the current stage should render expanded.
     *
     * @var bool
     */
    private $current_stage_open = false;

    /**
     * Whether the current root item should display stage numbering.
     *
     * @var bool
     */
    private $current_stage_numbered = false;

    /**
     * Cache of headings indexed by post ID.
     *
     * @var array<int, array<int, array<string, string>>>
     */
    private $headings_cache = array();

    /**
     * Extracts h1 headings from a post for use as anchor links.
     *
     * @param int $post_id Post ID to inspect.
     *
     * @return array<int, array<string, string>>
     */
    private function get_topic_headings( $post_id ) {
        if ( isset( $this->headings_cache[ $post_id ] ) ) {
            return $this->headings_cache[ $post_id ];
        }

        $headings = array();
        $post     = get_post( $post_id );

        if ( ! ( $post instanceof WP_Post ) ) {
            $this->headings_cache[ $post_id ] = $headings;
            return $headings;
        }

        if ( ! class_exists( 'DOMDocument' ) ) {
            $this->headings_cache[ $post_id ] = $headings;
            return $headings;
        }

        $content = apply_filters( 'the_content', $post->post_content );

        if ( '' === trim( wp_strip_all_tags( $content ) ) ) {
            $this->headings_cache[ $post_id ] = $headings;
            return $headings;
        }

        $libxml_previous = libxml_use_internal_errors( true );

        $dom      = new DOMDocument();
        $html     = '<!DOCTYPE html><html><body>' . $content . '</body></html>';
        $encoding = function_exists( 'mb_convert_encoding' ) ? mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) : $html;

        $dom->loadHTML( $encoding );

        libxml_clear_errors();
        libxml_use_internal_errors( $libxml_previous );

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

        $this->headings_cache[ $post_id ] = $headings;

        return $headings;
    }

    /**
     * Starts the list before the elements are added.
     *
     * @param string   $output Used to append additional content (passed by reference).
     * @param int      $depth  Depth of menu item. Used for padding.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     */
    public function start_lvl( &$output, $depth = 0, $args = null ) {
        if ( 0 === $depth ) {
            $output .= "\n<div class=\"stages-container mt-3\">\n";
        } elseif ( 1 === $depth ) {
            $classes  = 'ms-4 mt-1 stage-submenu';
            $classes .= $this->current_stage_open ? '' : ' d-none';

            $output .= sprintf(
                "\n<div id=\"%s\" class=\"%s\">\n",
                esc_attr( $this->current_submenu_id ),
                esc_attr( $classes )
            );
        }
    }

    /**
     * Ends the nested list of after the elements are added.
     *
     * @param string   $output Used to append additional content (passed by reference).
     * @param int      $depth  Depth of menu item. Used for padding.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     */
    public function end_lvl( &$output, $depth = 0, $args = null ) {
        if ( 0 === $depth || 1 === $depth ) {
            $output .= "</div>\n";
        }

        if ( 0 === $depth ) {
            $output .= "<hr />\n";
        }
    }

    /**
     * Starts the element output.
     *
     * @param string   $output Used to append additional content (passed by reference).
     * @param WP_Post  $item   Menu item data object.
     * @param int      $depth  Depth of menu item. Used for padding.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     * @param int      $id     Current item ID.
     */
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $item_classes = is_array( $item->classes ) ? $item->classes : array();

        if ( 0 === $depth ) {
            $this->stage_counter = 0;

            $this->current_stage_numbered = in_array( 'enable-stage-numbers', $item_classes, true );
            $this->current_stage_numbered = apply_filters( 'gnz_syllabus_stage_numbering_enabled', $this->current_stage_numbered, $item, $args );

            $is_active = in_array( 'current-menu-item', $item_classes, true ) || in_array( 'current-menu-ancestor', $item_classes, true );
            $classes = 'text-uppercase text-muted small fw-bold mt-5 px-2 text-decoration-none';
            $classes .= $is_active ? ' primary-text' : ' text-muted';

            $tag   = ! empty( $item->url ) ? 'a' : 'span';
            $attrs = '';

            if ( 'a' === $tag ) {
                $attrs = sprintf( ' href="%s"', esc_url( $item->url ) );
            }

            $output .= '<div class="menu-root">';
            $output .= sprintf(
                '<%1$s%2$s class="%3$s">%4$s</%1$s>',
                $tag,
                $attrs,
                esc_attr( $classes ),
                esc_html( $item->title )
            );
            $output .= "</div>\n";

            return;
        }

        if ( 1 === $depth ) {
            if ( $this->current_stage_numbered ) {
                $this->stage_counter++;
            }

            $submenu_id = 'submenu-' . $item->ID;
            $is_open    = in_array( 'current-menu-item', $item_classes, true ) || in_array( 'current-menu-ancestor', $item_classes, true );

            $this->current_submenu_id = $submenu_id;
            $this->current_stage_open = $is_open;

            $button_classes = 'stage-toggle w-100 d-flex align-items-center justify-content-between btn btn-link text-decoration-none px-2 py-2 primary-text';
            $number_classes = 'stage-number d-inline-flex align-items-center justify-content-center me-3 flex-shrink-0';

            if ( ! $this->current_stage_numbered ) {
                $button_classes .= ' no-stage-number';
            }

            if ( $is_open ) {
                $button_classes .= ' is-open';
                $number_classes .= ' text-white accent-bg';
            }

            $output .= "<div class=\"mb-2 menu-item-stage\">\n";
            $output .= sprintf(
                '<button type="button" class="%1$s" data-target="%2$s" aria-expanded="%3$s">',
                esc_attr( $button_classes ),
                esc_attr( $submenu_id ),
                $is_open ? 'true' : 'false'
            );

            $output .= '<span class="d-flex align-items-center text-start w-100">';
            if ( $this->current_stage_numbered ) {
                $stage_number = apply_filters( 'gnz_syllabus_stage_number', $this->stage_counter, $item, $args );

                $output .= sprintf(
                    '<span class="%1$s">%2$s</span>',
                    esc_attr( $number_classes ),
                    esc_html( (string) $stage_number )
                );
            }
            $output .= sprintf(
                '<span class="lh-sm flex-grow-1">%s</span>',
                esc_html( $item->title )
            );
            $output .= '</span>';

            $output .= sprintf(
                '<svg class="chevron-icon text-muted %1$s" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>',
                $is_open ? 'rotate-90' : ''
            );
            $output .= "</button>\n";
        } else {
            $is_active = in_array( 'current-menu-item', $item_classes, true );

            $headings = array();

            if ( 'page' === $item->object && ! empty( $item->object_id ) ) {
                $headings = $this->get_topic_headings( (int) $item->object_id );
            }

            $has_headings = ! empty( $headings );
            $submenu_id   = 'topic-submenu-' . $item->ID;

            $link_classes = 'topic-link d-block text-decoration-none';

            if ( $is_active ) {
                $link_classes .= ' link-active-bg fw-bold';
            }

            $output .= '<div class="topic-link-wrapper">';

            if ( $has_headings ) {
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
                    esc_html( $item->title )
                );
                $output .= '</span>';

                $output .= sprintf(
                    '<svg class="chevron-icon text-muted %1$s" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>',
                    $is_active ? 'rotate-90' : ''
                );
                $output .= '</button>';

                $container_classes = 'topic-headings mt-2 ps-3 border-start';

                if ( ! $is_active ) {
                    $container_classes .= ' d-none';
                }

                $output .= sprintf(
                    '<div id="%1$s" class="%2$s">',
                    esc_attr( $submenu_id ),
                    esc_attr( $container_classes )
                );

                $output .= sprintf(
                    '<a href="%1$s" class="topic-heading-link d-block small text-decoration-none py-1 text-muted">%2$s</a>',
                    esc_url( $item->url ),
                    esc_html__( 'Overview', 'gliding-nz-training' )
                );

                foreach ( $headings as $heading ) {
                    $output .= sprintf(
                        '<a href="%1$s" class="topic-heading-link d-block small text-decoration-none py-1 text-muted">%2$s</a>',
                        esc_url( $item->url . '#' . $heading['id'] ),
                        esc_html( $heading['text'] )
                    );
                }

                $output .= '</div>';
            } else {
                $output .= sprintf(
                    '<a href="%1$s" class="%2$s">%3$s</a>',
                    esc_url( $item->url ),
                    esc_attr( $link_classes ),
                    esc_html( $item->title )
                );
            }

            $output .= '</div>';
        }
    }

    /**
     * Ends the element output, if needed.
     *
     * @param string   $output Used to append additional content (passed by reference).
     * @param WP_Post  $item   Page data object. Not used.
     * @param int      $depth  Depth of page. Not Used.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     * @param int      $id     Current item ID.
     */
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        if ( 1 === $depth ) {
            $output .= "</div>\n";
        }
    }
}
