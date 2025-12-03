<?php get_header(); ?>

<div class="container-lg px-3 px-md-4 px-lg-5 d-flex flex-column min-vh-100 justify-content-center">
    <div class="hero-section py-5 text-center">
        <!-- Search Bar -->
        <div class="mx-auto my-4" style="max-width: 700px;">
            <p class="h4 fw-bold primary-text mb-3 text-start ps-2">Welcome! Search for any topic...</p>
            <form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
                <div class="search-container bg-white rounded-4 shadow-sm">
                    <span class="search-icon-wrapper">
                        <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input type="search" class="search-input form-control rounded-4 fs-5" 
                           placeholder='Try "circuit" or "spins"...' 
                           value="<?php echo get_search_query(); ?>" name="s" autofocus />
                </div>
            </form>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="row gy-5 gx-lg-5 pb-5">
        <div class="col-12 col-lg-6">
            <h2 class="h3 fw-bold primary-text border-bottom border-danger border-2 pb-2 d-inline-block mb-3">How to Use This Site</h2>
            <ul class="list-unstyled text-secondary d-flex flex-column gap-3">
                <li class="d-flex align-items-start">
                    <span class="badge rounded-circle primary-bg text-white d-flex align-items-center justify-content-center me-3 mt-1" style="width: 24px; height: 24px;">1</span>
                    <span><strong>Navigate by Stage:</strong> Use the menu to browse the syllabus chronologically. It's divided into 5 key stages, from Solo to Alpine.</span>
                </li>
                <li class="d-flex align-items-start">
                    <span class="badge rounded-circle primary-bg text-white d-flex align-items-center justify-content-center me-3 mt-1" style="width: 24px; height: 24px;">2</span>
                    <span><strong>Search Instantly:</strong> Looking for a specific procedure or rule? Use the search bar above to find pages across all syllabus levels instantly.</span>
                </li>
                <li class="d-flex align-items-start">
                    <span class="badge rounded-circle primary-bg text-white d-flex align-items-center justify-content-center me-3 mt-1" style="width: 24px; height: 24px;">3</span>
                    <span><strong>Track Progress:</strong> Consult your physical GNZ Logbook and Training Record card in conjunction with this material to track your real-world progress.</span>
                </li>
            </ul>
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
