<?php get_header(); ?>

<div class="container-lg py-4 px-3 px-md-4 px-lg-5">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1 class="display-5 fw-bold primary-text mb-4"><?php the_title(); ?></h1>
            <div class="entry-content text-secondary lh-lg">
                <?php the_content(); ?>
            </div>
        </article>

    <?php endwhile; else : ?>
        <p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
