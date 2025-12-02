document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const backdrop = document.getElementById('mobile-backdrop');
    const stageToggles = document.querySelectorAll('.stage-toggle');
    const topicToggles = document.querySelectorAll('.topic-toggle');
    const topicHeadingLinks = document.querySelectorAll('.topic-heading-link');

    const setActiveTopicHeadingById = headingId => {
        const normalizedId = (headingId || '').replace(/^#/, '');
        let matched = false;

        topicHeadingLinks.forEach(link => {
            const linkId = link.dataset.headingId || '';
            const container = link.closest('.topic-headings');
            const containerHidden = container && container.classList.contains('d-none');
            const wrapper = link.closest('.topic-link-wrapper');
            const toggle = wrapper ? wrapper.querySelector('.topic-toggle') : null;
            const belongsToActiveTopic = !toggle || toggle.classList.contains('link-active-bg');

            if (containerHidden || !belongsToActiveTopic) {
                link.classList.remove('topic-heading-link-active');
                return;
            }

            const isMatch = (normalizedId === '' && linkId === 'overview') || (normalizedId !== '' && linkId === normalizedId);

            link.classList.toggle('topic-heading-link-active', isMatch);

            if (isMatch) {
                matched = true;
            }
        });

        if (!matched && normalizedId !== '') {
            const fallback = Array.from(topicHeadingLinks).find(link => {
                if ((link.dataset.headingId || '') !== 'overview') {
                    return false;
                }

                const container = link.closest('.topic-headings');
                const wrapper = link.closest('.topic-link-wrapper');
                const toggle = wrapper ? wrapper.querySelector('.topic-toggle') : null;

                return container && !container.classList.contains('d-none') && toggle && toggle.classList.contains('link-active-bg');
            });

            if (fallback) {
                fallback.classList.add('topic-heading-link-active');
            }
        }
    };

    const syncActiveTopicHeading = () => {
        const hash = decodeURIComponent(window.location.hash || '');
        const headingId = hash.replace(/^#/, '');
        setActiveTopicHeadingById(headingId);
    };

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

            if (shouldOpen) {
                // Collapse any other open stage accordions when opening a new one
                stageToggles.forEach(otherButton => {
                    if (otherButton === button) {
                        return;
                    }

                    const otherTargetId = otherButton.getAttribute('data-target');
                    const otherMenu = document.getElementById(otherTargetId);

                    if (!otherMenu || otherMenu.classList.contains('d-none')) {
                        return;
                    }

                    const otherChevron = otherButton.querySelector('.chevron-icon');
                    const otherStageNumber = otherButton.querySelector('.stage-number');

                    otherMenu.classList.add('d-none');
                    otherButton.classList.remove('is-open');
                    otherButton.setAttribute('aria-expanded', 'false');

                    if (otherChevron) {
                        otherChevron.classList.remove('rotate-90');
                    }

                    if (otherStageNumber) {
                        otherStageNumber.classList.remove('accent-bg');
                        otherStageNumber.classList.remove('text-white');
                    }
                });
            }

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

    // Topic Toggles
    topicToggles.forEach(button => {
        button.addEventListener('click', event => {
            event.preventDefault();

            const targetId = button.getAttribute('data-target');
            const targetMenu = document.getElementById(targetId);
            const chevron = button.querySelector('.chevron-icon');

            if (!targetMenu) {
                return;
            }

            const shouldOpen = targetMenu.classList.contains('d-none');

            if (shouldOpen) {
                const wrapper = button.closest('.topic-link-wrapper');
                const container = wrapper ? wrapper.parentElement : null;
                const scopeToggles = container ? container.querySelectorAll('.topic-toggle') : topicToggles;

                scopeToggles.forEach(otherButton => {
                    if (otherButton === button) {
                        return;
                    }

                    const otherTargetId = otherButton.getAttribute('data-target');
                    const otherMenu = document.getElementById(otherTargetId);

                    if (!otherMenu || otherMenu.classList.contains('d-none')) {
                        return;
                    }

                    const otherChevron = otherButton.querySelector('.chevron-icon');

                    otherMenu.classList.add('d-none');
                    otherButton.classList.remove('is-open');
                    otherButton.setAttribute('aria-expanded', 'false');

                    if (!otherButton.classList.contains('link-active-bg')) {
                        otherButton.classList.remove('fw-bold');
                    }

                    if (otherChevron) {
                        otherChevron.classList.remove('rotate-90');
                    }
                });
            }

            targetMenu.classList.toggle('d-none', !shouldOpen);
            button.classList.toggle('is-open', shouldOpen);
            button.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');

            if (!button.classList.contains('link-active-bg')) {
                button.classList.toggle('fw-bold', shouldOpen);
            }

            if (chevron) {
                chevron.classList.toggle('rotate-90', shouldOpen);
            }
        });
    });

    // Close sidebar after selecting in-page anchor on smaller viewports
    topicHeadingLinks.forEach(link => {
        link.addEventListener('click', () => {
            const headingId = link.dataset.headingId || '';
            setActiveTopicHeadingById(headingId === 'overview' ? '' : headingId);

            if (!sidebar) {
                return;
            }

            const shouldCollapse = window.matchMedia('(max-width: 991px)').matches && sidebar.classList.contains('sidebar-expanded');

            if (shouldCollapse) {
                toggleSidebar();
            }
        });
    });

    syncActiveTopicHeading();
    window.addEventListener('hashchange', syncActiveTopicHeading);
});
