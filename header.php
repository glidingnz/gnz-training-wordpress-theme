<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <div id="mobile-backdrop"></div>

    <div class="d-flex flex-column flex-lg-row min-vh-100">
        
        <!-- Mobile Header with Toggle -->
        <div class="d-lg-none bg-white border-bottom p-3 d-flex align-items-center sticky-top gap-3" style="z-index: 1060;">
            <button id="hamburger-btn" class="btn btn-link primary-text p-0 text-decoration-none">
                <svg class="w-8 h-8" style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-decoration-none d-flex align-items-center">
            <img
                src="https://gliding.co.nz/wp-content/uploads/2020/07/GNZ-Logo-Nov-2019-02-720x623.png"
                onerror="this.onerror=null;this.src='https://placehold.co/200x80/C1272D/FFFFFF?text=GNZ';"
                alt="<?php esc_attr_e( 'Gliding NZ Logo', 'gliding-nz-training' ); ?>"
                class="me-2"
                style="max-height: 2rem;"
            >
            <span class="fw-bold primary-text">GNZ Pilot Training Program</span>
            </a>
        </div>

        <?php get_sidebar(); ?>

        <main id="main-content" class="flex-grow-1 bg-white position-relative">
