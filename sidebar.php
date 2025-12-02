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
            <?php GNZ_Syllabus_Sidebar::render(); ?>
        </nav>
    </div>
</div>
