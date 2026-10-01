/*
 * Alamarte Admin Menu Core
 * Version: 1.4.1
 * GNU General Public License version 3 or later
 * https://www.gnu.org/licenses/gpl-3.0.html
 */

(() => {
    'use strict';


    const updateScrollableLists = (menu) => {
        menu.querySelectorAll('.alm-adminmenu__links').forEach((list) => {
            const items = [...list.children].filter((item) => item.matches('li'));
            const visibleCount = Number.parseInt(list.dataset.almScrollAfter || '6', 10);
            const shouldScroll = Number.isInteger(visibleCount)
                && visibleCount > 0
                && items.length > visibleCount;

            list.classList.toggle('alm-adminmenu__links--scrollable', shouldScroll);

            if (!shouldScroll) {
                list.style.removeProperty('--alm-links-scroll-height');

                return;
            }

            const lastVisible = items[visibleCount - 1];
            const listBox = list.getBoundingClientRect();
            const itemBox = lastVisible.getBoundingClientRect();

            // A hidden Bootstrap dropdown has no measurable dimensions. The
            // ResizeObserver and shown.bs.dropdown handlers repeat this work
            // immediately after the panel becomes visible.
            if (listBox.width <= 0 || itemBox.height <= 0) {
                return;
            }

            const computed = window.getComputedStyle(list);
            const paddingBottom = Number.parseFloat(computed.paddingBottom) || 0;
            const height = Math.ceil(itemBox.bottom - listBox.top + paddingBottom);
            const value = `${height}px`;

            if (list.style.getPropertyValue('--alm-links-scroll-height') !== value) {
                list.style.setProperty('--alm-links-scroll-height', value);
            }
        });
    };

    const initialiseScrolling = (menu) => {
        const toggle = menu.querySelector('[data-bs-toggle="dropdown"]');
        const panel = menu.querySelector('.alm-adminmenu__panel');
        let frame = 0;

        const refresh = () => {
            window.cancelAnimationFrame(frame);
            frame = window.requestAnimationFrame(() => {
                frame = window.requestAnimationFrame(() => updateScrollableLists(menu));
            });
        };

        toggle?.addEventListener('show.bs.dropdown', refresh);
        toggle?.addEventListener('shown.bs.dropdown', refresh);
        toggle?.addEventListener('click', refresh);
        menu.addEventListener('shown.bs.dropdown', refresh);
        window.addEventListener('resize', refresh, {passive: true});

        if ('ResizeObserver' in window && panel) {
            const resizeObserver = new ResizeObserver(refresh);
            resizeObserver.observe(panel);
        }

        if (document.fonts?.ready) {
            document.fonts.ready.then(refresh).catch(() => {});
        }

        refresh();
    };

    const initialise = (menu) => {
        if (menu.dataset.almStylesInitialised === 'true') {
            return;
        }

        menu.dataset.almStylesInitialised = 'true';
        initialiseScrolling(menu);

    };

    const initialiseAll = (root = document) => {
        root.querySelectorAll('[data-alm-adminmenu]').forEach(initialise);
    };

    document.addEventListener('DOMContentLoaded', () => initialiseAll());
    document.addEventListener('joomla:updated', (event) => initialiseAll(event.target || document));

    initialiseAll();
})();
