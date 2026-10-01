/*
 * Alamarte Admin Menu Core
 * Version: 1.4.1
 * GNU General Public License version 3 or later
 * https://www.gnu.org/licenses/gpl-3.0.html
 */

(() => {
    'use strict';

    const validThemes = new Set(['auto', 'light', 'dark']);
    const storageKey = 'alamarte.adminmenu.theme';

    const readPreference = (fallback) => {
        try {
            const stored = window.localStorage.getItem(storageKey);

            return validThemes.has(stored) ? stored : fallback;
        } catch (error) {
            return fallback;
        }
    };

    const savePreference = (theme) => {
        try {
            window.localStorage.setItem(storageKey, theme);
        } catch (error) {
            // Storage can be unavailable in private or restricted browser contexts.
        }
    };

    const elementScheme = (element) => {
        if (!element) {
            return null;
        }

        const colorScheme = (element.getAttribute('data-color-scheme') || '').toLowerCase();

        if (colorScheme === 'light' || colorScheme === 'dark') {
            return colorScheme;
        }

        const bootstrapTheme = (element.getAttribute('data-bs-theme') || '').toLowerCase();

        return bootstrapTheme === 'light' || bootstrapTheme === 'dark' ? bootstrapTheme : null;
    };

    const administratorTheme = () => {
        const declaredTheme = elementScheme(document.documentElement) || elementScheme(document.body);

        if (declaredTheme !== null) {
            return declaredTheme;
        }

        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };

    const initialise = (menu) => {
        if (menu.dataset.almInitialised === 'true') {
            return;
        }

        menu.dataset.almInitialised = 'true';

        const switcherEnabled = menu.dataset.themeSwitcher === '1';
        const defaultTheme = validThemes.has(menu.dataset.defaultTheme) ? menu.dataset.defaultTheme : 'auto';
        const buttons = [...menu.querySelectorAll('[data-alm-theme]')];
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        let currentTheme = switcherEnabled ? readPreference(defaultTheme) : defaultTheme;

        const applyTheme = (theme) => {
            currentTheme = validThemes.has(theme) ? theme : defaultTheme;
            menu.dataset.theme = currentTheme;
            menu.dataset.resolvedTheme = currentTheme === 'auto' ? administratorTheme() : currentTheme;

            buttons.forEach((button) => {
                button.setAttribute('aria-pressed', button.dataset.almTheme === currentTheme ? 'true' : 'false');
            });
        };

        buttons.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                const theme = button.dataset.almTheme;

                if (!validThemes.has(theme)) {
                    return;
                }

                savePreference(theme);
                applyTheme(theme);
            });
        });

        mediaQuery.addEventListener('change', () => {
            if (currentTheme === 'auto') {
                applyTheme('auto');
            }
        });

        document.addEventListener('joomla:color-scheme-change', () => {
            if (currentTheme === 'auto') {
                applyTheme('auto');
            }
        });

        const observer = new MutationObserver(() => {
            if (currentTheme === 'auto') {
                applyTheme('auto');
            }
        });

        observer.observe(document.documentElement, {attributes: true, attributeFilter: ['class', 'data-bs-theme', 'data-color-scheme']});

        if (document.body) {
            observer.observe(document.body, {attributes: true, attributeFilter: ['class', 'data-bs-theme', 'data-color-scheme']});
        }

        applyTheme(currentTheme);
    };

    const initialiseAll = (root = document) => {
        root.querySelectorAll('[data-alm-adminmenu]').forEach(initialise);
    };

    document.addEventListener('DOMContentLoaded', () => initialiseAll());
    document.addEventListener('joomla:updated', (event) => initialiseAll(event.target || document));

    initialiseAll();
})();
