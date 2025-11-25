<div id="sidebar" class="d-flex flex-column bg-white border-end sidebar-collapsed">
    <div class="sidebar-inner-content d-flex flex-column flex-grow-1">
        <!-- Navigation -->
        <nav class="flex-grow-1 p-3 overflow-auto custom-scroll mt-3">
            <h6 class="text-uppercase text-muted small fw-bold mb-4 px-2">Syllabus Stages</h6>

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
