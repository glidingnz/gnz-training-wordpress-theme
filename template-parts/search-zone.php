<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Search zone: label row, search form, and collapsible tips panel.
 *
 * Accepts $args:
 *   mb_class (string) — bottom margin utility class, default 'mb-4'.
 */
$mb_class = ( ! empty( $args['mb_class'] ) && is_string( $args['mb_class'] ) )
    ? sanitize_html_class( $args['mb_class'] )
    : 'mb-4';
?>
<div class="<?php echo esc_attr( $mb_class ); ?> text-start" style="max-width: 700px; margin-left: auto; margin-right: auto;">
    <div class="d-flex justify-content-between align-items-baseline mb-2 ps-2">
        <p class="h6 fw-bold primary-text mb-0"><?php esc_html_e( 'Search for any topic...', 'gliding-nz-training' ); ?></p>
        <button type="button"
                class="btn btn-link p-0 text-secondary small text-decoration-none d-inline-flex align-items-center gap-1 lh-1"
                aria-expanded="false"
                aria-controls="gnz-search-tips"
                id="gnz-search-tips-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
            </svg>
            <?php esc_html_e( 'Tips', 'gliding-nz-training' ); ?>
        </button>
    </div>
    <?php get_template_part( 'template-parts/search-bar' ); ?>
    <div class="collapse mt-2" id="gnz-search-tips">
        <div class="bg-white rounded-4 shadow-sm px-4 py-3">
            <ul class="mb-0 small text-secondary ps-3">
                <li class="mb-2"><strong><?php esc_html_e( 'Multiple words', 'gliding-nz-training' ); ?></strong> &mdash; <?php esc_html_e( 'treated as an exact phrase. For example, searching', 'gliding-nz-training' ); ?> <em><?php esc_html_e( 'spin training', 'gliding-nz-training' ); ?></em> <?php esc_html_e( 'only returns pages where those two words appear together in that order.', 'gliding-nz-training' ); ?></li>
                <li class="mb-2"><strong><?php esc_html_e( 'Single word', 'gliding-nz-training' ); ?></strong> &mdash; <?php esc_html_e( 'matched as a whole word only. Searching', 'gliding-nz-training' ); ?> <em><?php esc_html_e( 'spin', 'gliding-nz-training' ); ?></em> <?php esc_html_e( "won't return pages that merely contain the letters \"spin\" inside another word.", 'gliding-nz-training' ); ?></li>
                <li class="mb-2"><?php esc_html_e( "If you're not finding what you expect, try a shorter phrase or a single key word.", 'gliding-nz-training' ); ?></li>
                <li class="mb-0 text-body-tertiary"><?php esc_html_e( 'Occasionally a page may appear because a word matches an image filename or other embedded content rather than the visible text.', 'gliding-nz-training' ); ?></li>
            </ul>
        </div>
    </div>
</div>
