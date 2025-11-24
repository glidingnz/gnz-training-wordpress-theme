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
        </article>

    <?php endwhile; else : ?>
        <p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
