<?php
get_header();
?>

<div class="container-lg py-5 px-3 px-md-4 px-lg-5">
    <header class="mb-5 text-center text-md-start">
        <p class="text-uppercase text-secondary fw-semibold small mb-2"><?php esc_html_e( 'Search', 'gliding-nz-training' ); ?></p>
        <h1 class="display-6 fw-bold primary-text mb-3">
            <?php printf(
                /* translators: %s: search query */
                esc_html__( 'Results for "%s"', 'gliding-nz-training' ),
                esc_html( get_search_query() )
            ); ?>
        </h1>
        <?php
        global $wp_query;
        $total_results = (int) $wp_query->found_posts;
        if ( $total_results > 0 ) :
            $count_label = sprintf(
                /* translators: %s: total number of results */
                _n( '%s result found', '%s results found', $total_results, 'gliding-nz-training' ),
                number_format_i18n( $total_results )
            );
            ?>
            <p class="text-secondary mb-0"><?php echo esc_html( $count_label ); ?></p>
        <?php else : ?>
            <p class="text-secondary mb-0"><?php esc_html_e( 'No matching pages were found. Try another search term below.', 'gliding-nz-training' ); ?></p>
        <?php endif; ?>
    </header>

    <?php
    $search_terms = array();

    if ( ! empty( $wp_query->query_vars['search_terms'] ) && is_array( $wp_query->query_vars['search_terms'] ) ) {
        $search_terms = $wp_query->query_vars['search_terms'];
    } else {
        $fallback_term = trim( get_search_query() );
        if ( '' !== $fallback_term ) {
            $search_terms = preg_split( '/\s+/', $fallback_term );
        }
    }

    if ( ! is_array( $search_terms ) ) {
        $search_terms = array();
    }

    $search_terms = array_filter( array_map( 'trim', $search_terms ) );
    $search_terms = array_unique( $search_terms );

    $highlight_text = static function ( $text ) use ( $search_terms ) {
        $escaped_text = esc_html( $text );

        if ( empty( $search_terms ) || '' === $escaped_text ) {
            return $escaped_text;
        }

        $escaped_terms = array();

        foreach ( $search_terms as $term ) {
            $term = trim( wp_strip_all_tags( $term ) );

            if ( '' === $term ) {
                continue;
            }

            $escaped_terms[] = preg_quote( esc_html( $term ), '/' );
        }

        if ( empty( $escaped_terms ) ) {
            return $escaped_text;
        }

        $pattern = '/(' . implode( '|', $escaped_terms ) . ')/iu';
        $highlighted = preg_replace( $pattern, '<mark class="search-highlight">$1</mark>', $escaped_text );

        if ( null === $highlighted ) {
            return $escaped_text;
        }

        return wp_kses( $highlighted, array( 'mark' => array( 'class' => array() ) ) );
    };

    $collect_snippets = static function ( $text, $terms, $limit = 3 ) {
        if ( empty( $terms ) ) {
            return array();
        }

        $plain_text = wp_strip_all_tags( (string) $text );

        if ( '' === $plain_text ) {
            return array();
        }

        $normalized = preg_replace( '/\s+/u', ' ', $plain_text );
        if ( null === $normalized ) {
            $normalized = $plain_text;
        }

        $sentences = preg_split( '/(?<=[.!?])\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY );
        if ( false === $sentences || empty( $sentences ) ) {
            $sentences = array( $normalized );
        }

        $snippets_map = array();

        foreach ( $sentences as $index => $sentence ) {
            $sentence = trim( (string) $sentence );

            if ( '' === $sentence ) {
                continue;
            }

            $lower_sentence = function_exists( 'mb_strtolower' ) ? mb_strtolower( $sentence ) : strtolower( $sentence );
            $score          = 0;

            foreach ( $terms as $term ) {
                $term = trim( (string) $term );

                if ( '' === $term ) {
                    continue;
                }

                $lower_term = function_exists( 'mb_strtolower' ) ? mb_strtolower( $term ) : strtolower( $term );
                $score     += substr_count( $lower_sentence, $lower_term );
            }

            if ( $score <= 0 ) {
                continue;
            }

            $sentence_length = function_exists( 'mb_strlen' ) ? mb_strlen( $sentence ) : strlen( $sentence );

            if ( $sentence_length > 240 ) {
                $trimmed = function_exists( 'mb_substr' ) ? mb_substr( $sentence, 0, 237 ) : substr( $sentence, 0, 237 );
                $sentence = rtrim( (string) $trimmed ) . '…';
            }

            $key_base = function_exists( 'mb_strtolower' ) ? mb_strtolower( $sentence ) : strtolower( $sentence );
            $key_space_normalized = preg_replace( '/\s+/u', ' ', $key_base );

            if ( null === $key_space_normalized ) {
                $key_space_normalized = $key_base;
            }

            $key = md5( $key_space_normalized );

            if ( isset( $snippets_map[ $key ] ) ) {
                $existing = $snippets_map[ $key ];

                if ( $score > $existing['score'] || ( $score === $existing['score'] && $index < $existing['index'] ) ) {
                    $snippets_map[ $key ] = array(
                        'text'  => $sentence,
                        'score' => $score,
                        'index' => $index,
                    );
                }

                continue;
            }

            $snippets_map[ $key ] = array(
                'text'  => $sentence,
                'score' => $score,
                'index' => $index,
            );
        }

        if ( empty( $snippets_map ) ) {
            return array();
        }

        $snippets = array_values( $snippets_map );

        usort(
            $snippets,
            static function ( $a, $b ) {
                if ( $a['score'] === $b['score'] ) {
                    return $a['index'] <=> $b['index'];
                }

                return $b['score'] <=> $a['score'];
            }
        );

        $snippets = array_slice( $snippets, 0, max( 1, (int) $limit ) );

        $snippets = array_map(
            static function ( $entry ) {
                return $entry['text'];
            },
            $snippets
        );

        return array_values( array_unique( $snippets ) );
    };

    if ( have_posts() ) :
        ?>
        <div class="list-group mb-4">
            <?php
            while ( have_posts() ) :
                the_post();

                $post_id     = get_the_ID();
                $permalink   = get_permalink( $post_id );
                $raw_content = get_post_field( 'post_content', $post_id );
                $excerpt     = get_the_excerpt( $post_id );

                $combined_source = $excerpt ? $excerpt . ' ' . $raw_content : $raw_content;
                $snippets        = $collect_snippets( $combined_source, $search_terms, 3 );

                if ( empty( $snippets ) ) {
                    if ( empty( $excerpt ) ) {
                        $excerpt = wp_trim_words( wp_strip_all_tags( $raw_content ), 35, '…' );
                    }

                    $plain_excerpt = wp_strip_all_tags( (string) $excerpt );

                    if ( '' !== $plain_excerpt ) {
                        $snippets = array( $plain_excerpt );
                    }
                }

                if ( empty( $snippets ) ) {
                    $fallback_summary = wp_trim_words( wp_strip_all_tags( get_the_content( null, false, $post_id ) ), 20, '…' );

                    if ( '' !== $fallback_summary ) {
                        $snippets = array( $fallback_summary );
                    }
                }

                if ( ! empty( $snippets ) ) {
                    $snippets = array_values( array_unique( $snippets ) );
                }

                $snippet_count = count( $snippets );
                $classes       = implode( ' ', get_post_class( 'list-group-item list-group-item-action flex-column align-items-start py-4 px-4 mb-3 rounded-4 shadow-sm border-0 d-block position-relative', $post_id ) );
                $anchor_id     = 'post-' . $post_id;

                $highlight_value = '';
                if ( ! empty( $search_terms ) ) {
                    $joined_terms = implode( ' ', array_map( 'sanitize_text_field', $search_terms ) );
                    $normalized_terms = preg_replace( '/\s+/', ' ', $joined_terms );
                    if ( null === $normalized_terms ) {
                        $normalized_terms = $joined_terms;
                    }
                    $highlight_value = trim( $normalized_terms );
                }

                $permalink_for_card = $permalink;
                if ( '' !== $highlight_value ) {
                    $permalink_for_card = add_query_arg( 'highlight', $highlight_value, $permalink_for_card );
                }
                ?>
                <a href="<?php echo esc_url( $permalink_for_card ); ?>" class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $anchor_id ); ?>" role="article">
                    <div class="w-100">
                        <h2 class="h4 fw-bold primary-text mb-3"><?php echo esc_html( get_the_title( $post_id ) ); ?></h2>
                        <?php foreach ( $snippets as $index => $snippet ) :
                            $paragraph_class = $index === ( $snippet_count - 1 ) ? ' mb-0' : ' mb-2';
                            ?>
                            <p class="text-secondary<?php echo esc_attr( $paragraph_class ); ?>"><?php echo $highlight_text( $snippet ); ?></p>
                        <?php endforeach; ?>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>

        <?php the_posts_pagination( array(
            'prev_text' => esc_html__( 'Previous', 'gliding-nz-training' ),
            'next_text' => esc_html__( 'Next', 'gliding-nz-training' ),
            'class'     => 'pagination justify-content-center'
        ) ); ?>
    <?php else : ?>
        <div class="bg-white p-4 rounded-4 shadow-sm">
            <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <div class="search-container position-relative bg-white rounded-4 shadow-sm">
                    <input
                        type="search"
                        class="search-input form-control border-0 rounded-4 py-3 ps-5 fs-5"
                        placeholder='<?php echo esc_attr__( 'Try "spins," "HASELL," or "thermal entry"...', 'gliding-nz-training' ); ?>'
                        value="<?php echo esc_attr( get_search_query() ); ?>"
                        name="s"
                        autofocus
                    />
                    <svg class="search-icon position-absolute" style="left: 1.5rem; top: 50%; transform: translateY(-50%); width: 1.5rem; height: 1.5rem; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
