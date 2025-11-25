<div id="sidebar" class="d-flex flex-column bg-white border-end sidebar-collapsed">
    <div class="sidebar-inner-content d-flex flex-column flex-grow-1">
        <!-- Logo Area -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mt-3 p-4 border-bottom d-flex flex-column align-items-center text-decoration-none">
            <img
                src="<?php echo esc_url( get_theme_file_uri( '/assets/img/gnz-logo-text.png' ) ); ?>"
                alt="<?php esc_attr_e( 'Gliding NZ Logo', 'gliding-nz-training' ); ?>"
                class="img-fluid"
                id="sidebar-logo"
            >
            <span class="sidebar-programme fw-bold primary-text text-center mt-2"><?php esc_html_e( 'Pilot Training Programme', 'gliding-nz-training' ); ?></span>
        </a>
        <!-- Navigation -->
        <nav class="flex-grow-1 p-3 overflow-auto custom-scroll mt-3">

            <?php if ( has_nav_menu( 'sidebar-menu' ) ) : ?>
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'sidebar-menu',
                        'container'      => false,
                        'items_wrap'     => '%3$s',
                        'walker'         => new GNZ_Syllabus_Menu_Walker(),
                    )
                );
                ?>
            <?php else : ?>
                <p class="text-muted small px-2">
                    <?php esc_html_e( 'Assign the "Sidebar Syllabus Menu" under Appearance > Menus to populate the training navigation.', 'gliding-nz-training' ); ?>
                </p>
            <?php endif; ?>
        </nav>
    </div>
</div>
