<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<?php get_header(); ?>

<div class="container-lg px-3 px-md-4 px-lg-5 d-flex flex-column justify-content-center">
    <div class="hero-section text-center mt-3 mt-lg-5">
        <!-- Logo & Title -->
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-decoration-none d-inline-flex flex-column align-items-center flex-lg-row align-items-lg-end gap-lg-3 mb-5">
            <img
                src="<?php echo esc_url( get_theme_file_uri( '/assets/img/gnz-logo-text-optimised.webp' ) ); ?>"
                alt="<?php esc_attr_e( 'Gliding NZ Logo', 'gliding-nz-training' ); ?>"
                style="max-height: 80px;"
                class="img-fluid"
            >
            <span class="sidebar-program fs-3 mt-2 mt-lg-0"><?php esc_html_e( 'Pilot Training Program', 'gliding-nz-training' ); ?></span>
        </a>
        <!-- Search Bar -->
        <?php get_template_part( 'template-parts/search-zone', null, array( 'mb_class' => 'mb-5' ) ); ?>
    </div>

    <!-- Info Grid -->
    <div class="row gy-5 gx-lg-5 pb-5">
        <div class="col-12">
            <h2 class="h3 fw-bold primary-text border-bottom border-danger border-2 pb-2 d-inline-block mb-3">How to Use This Site</h2>
            <ul class="list-unstyled text-secondary d-flex flex-column gap-3">
                <li class="d-flex align-items-start">
                    <span class="badge rounded-circle primary-bg text-white d-flex align-items-center justify-content-center me-3 mt-1" style="width: 24px; height: 24px;">1</span>
                    <span><strong>Learn how the program works:</strong> Read <a href="/pilot/before/how/">Before You Begin</a> to learn how to get the most from your instructors.</span>
                </li>
                <li class="d-flex align-items-start">
                    <span class="badge rounded-circle primary-bg text-white d-flex align-items-center justify-content-center me-3 mt-1" style="width: 24px; height: 24px;">2</span>
                    <span><strong>Navigate by Stage:</strong> Jump back in using the menu to browse the syllabus chronologically. It's divided into 5 key stages, from Solo to Alpine Pilot.</span>
                </li>
                <li class="d-flex align-items-start">
                    <span class="badge rounded-circle primary-bg text-white d-flex align-items-center justify-content-center me-3 mt-1" style="width: 24px; height: 24px;">3</span>
                    <span><strong>Search Instantly:</strong> Looking for a specific procedure or rule? Use the search bar above to find pages across all syllabus levels instantly.</span>
                </li>
                <li class="d-flex align-items-start">
                    <span class="badge rounded-circle primary-bg text-white d-flex align-items-center justify-content-center me-3 mt-1" style="width: 24px; height: 24px;">4</span>
                    <span><strong>Send Feedback:</strong> Found something missing or incorrect? Email <a href="mailto:webmaster@gliding.co.nz">webmaster@gliding.co.nz</a> with your feedback. The old PTP site at <a href="http://training.gliding.co.nz">training.gliding.co.nz</a> will remain available until the end of June 2026.</span>
                </li>
            </ul>
        </div>
        <!-- Soaring journey image -->
        <div class="col-12 col-lg-6">
            <div class="overflow-hidden rounded-4">
                <img
                    src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/soaring-journey-optimised.webp' ); ?>"
                    class="img-fluid w-100 object-fit-cover"
                    alt="Glider soaring above New Zealand landscape"
                    loading="eager"
                >
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <h2 class="h3 fw-bold primary-text border-bottom border-danger border-2 pb-2 d-inline-block mb-3">About the Program</h2>
            <p class="text-secondary lh-lg">
                The Gliding New Zealand (GNZ) training program is designed to take you from your very first trial flight all the way to becoming an expert cross-country, competition, or alpine pilot.
            </p>
            <p class="text-secondary lh-lg">
                Our syllabus is competency-based, meaning you progress at your own pace as you master each skill. Whether you are learning to fly solo or refining advanced soaring techniques, this portal serves as your comprehensive reference guide.
            </p>
        </div>
    </div>
</div>

<?php get_footer(); ?>
