<?php get_header(); ?>

<div class="container-lg py-5 px-3 px-md-4 px-lg-5">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1 class="display-5 fw-bold primary-text mb-4"><?php the_title(); ?></h1>
            
            <!-- Breadcrumb-ish placeholder -->
            <p class="text-muted small border-bottom pb-3 mb-4">
                <?php 
                // Simple parent check
                $parent_id = wp_get_post_parent_id( get_the_ID() );
                if ( $parent_id ) {
                    echo get_the_title( $parent_id ) . ' > ';
                }
                the_title(); 
                ?>
            </p>

            <div class="entry-content text-secondary lh-lg">
                <?php the_content(); ?>
            </div>

            <?php
            $current_id = get_the_ID();
            $post_type  = get_post_type( $current_id );

            $all_pages = get_pages( array(
                'sort_column' => 'menu_order,post_title',
                'post_type'   => $post_type,
                'post_status' => 'publish',
            ) );

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

            $ordered_ids = array();

            $collect_hierarchy = static function ( $parent_id, $pages_by_parent, &$ordered_ids, $collect_hierarchy ) {
                if ( empty( $pages_by_parent[ $parent_id ] ) ) {
                    return;
                }

                foreach ( $pages_by_parent[ $parent_id ] as $page ) {
                    $ordered_ids[] = (int) $page->ID;
                    $collect_hierarchy( (int) $page->ID, $pages_by_parent, $ordered_ids, $collect_hierarchy );
                }
            };

            $collect_hierarchy( 0, $pages_by_parent, $ordered_ids, $collect_hierarchy );

            $ordered_ids = array_values( array_unique( $ordered_ids ) );

            $previous_page = null;
            $next_page     = null;

            $current_index = array_search( $current_id, $ordered_ids, true );

            if ( false !== $current_index ) {
                if ( $current_index > 0 && isset( $ordered_ids[ $current_index - 1 ] ) ) {
                    $previous_page = get_post( $ordered_ids[ $current_index - 1 ] );
                }

                if ( isset( $ordered_ids[ $current_index + 1 ] ) ) {
                    $next_page = get_post( $ordered_ids[ $current_index + 1 ] );
                }
            }

            if ( $previous_page || $next_page ) :
                ?>
                <nav class="mt-5 pt-4 border-top">
                    <div class="topic-nav d-flex align-items-start gap-3">
                        <?php if ( $previous_page ) : ?>
                            <a class="topic-nav-link d-flex align-items-center gap-2 text-decoration-none" href="<?php echo esc_url( get_permalink( $previous_page->ID ) ); ?>" rel="prev">
                                <span class="topic-nav-arrow" aria-hidden="true">&larr;</span>
                                <span>
                                    <span class="text-muted small text-uppercase d-block"><?php esc_html_e( 'Previous Topic', 'gliding-nz-training' ); ?></span>
                                    <span class="primary-text fw-semibold d-block"><?php echo esc_html( get_the_title( $previous_page->ID ) ); ?></span>
                                </span>
                            </a>
                        <?php endif; ?>
                        <?php if ( $next_page ) : ?>
                            <a class="topic-nav-link d-flex align-items-center gap-2 text-decoration-none ms-auto justify-content-end text-end" href="<?php echo esc_url( get_permalink( $next_page->ID ) ); ?>" rel="next">
                                <span>
                                    <span class="text-muted small text-uppercase d-block"><?php esc_html_e( 'Next Topic', 'gliding-nz-training' ); ?></span>
                                    <span class="primary-text fw-semibold d-block"><?php echo esc_html( get_the_title( $next_page->ID ) ); ?></span>
                                </span>
                                <span class="topic-nav-arrow" aria-hidden="true">&rarr;</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </nav>
            <?php endif; ?>
        </article>

    <?php endwhile; else : ?>
        <p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
