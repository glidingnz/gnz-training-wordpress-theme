document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const backdrop = document.getElementById('mobile-backdrop');
    const stageToggles = document.querySelectorAll('.stage-toggle');

    // Mobile Sidebar Toggle
    const toggleSidebar = () => {
        const isExpanded = sidebar.classList.contains('sidebar-expanded');
        if (isExpanded) {
            sidebar.classList.remove('sidebar-expanded');
            sidebar.classList.add('sidebar-collapsed');
            backdrop.classList.remove('show');
        } else {
            sidebar.classList.remove('sidebar-collapsed');
            sidebar.classList.add('sidebar-expanded');
            backdrop.classList.add('show');
        }
    };

    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', toggleSidebar);
    }
    if (backdrop) {
        backdrop.addEventListener('click', toggleSidebar);
    }

    // Accordion Logic
    stageToggles.forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-target');
            const targetMenu = document.getElementById(targetId);
            const chevron = button.querySelector('.chevron-icon');
            const stageNumber = button.querySelector('.stage-number');

            if (!targetMenu) {
                return;
            }

            const shouldOpen = targetMenu.classList.contains('d-none');

            targetMenu.classList.toggle('d-none', !shouldOpen);
            button.classList.toggle('is-open', shouldOpen);
            button.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');

            if (chevron) {
                chevron.classList.toggle('rotate-90', shouldOpen);
            }

            if (stageNumber) {
                stageNumber.classList.toggle('accent-bg', shouldOpen);
                stageNumber.classList.toggle('text-white', shouldOpen);
            }
        });
    });
});
