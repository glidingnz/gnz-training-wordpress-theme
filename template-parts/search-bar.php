<div class="mx-auto mb-5" style="max-width: 700px;">
    <p class="h6 fw-bold primary-text mb-2 text-start ps-2"><?php esc_html_e( 'Search for any topic...', 'gliding-nz-training' ); ?></p>
    <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <div class="search-container bg-white rounded-4 shadow-sm">
            <span class="search-icon-wrapper">
                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="search" class="search-input form-control rounded-4 fs-6"
                   placeholder='<?php esc_attr_e( 'Try "circuit" or "spins"...', 'gliding-nz-training' ); ?>'
                   value="<?php echo esc_attr( get_search_query() ); ?>" name="s" id="hero-search" />
        </div>
    </form>
</div>
