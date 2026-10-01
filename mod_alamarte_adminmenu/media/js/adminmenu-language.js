/*
 * Alamarte Admin Menu Core
 * Version: 1.4.1
 * GNU General Public License version 3 or later
 * https://www.gnu.org/licenses/gpl-3.0.html
 */

(() => {
    'use strict';

    const languagePattern = /^[a-z]{2,3}-[A-Z]{2}$/;
    const maximumResponseSize = 4096;
    const requestTimeout = 8000;

    const showError = (switcher) => {
        const message = switcher.dataset.errorMessage || 'The administrator language could not be changed.';

        if (window.Joomla && typeof window.Joomla.renderMessages === 'function') {
            window.Joomla.renderMessages({error: [message]});
            return;
        }

        window.alert(message);
    };

    const initialise = (switcher) => {
        if (switcher.dataset.almLanguageInitialised === 'true') {
            return;
        }

        switcher.dataset.almLanguageInitialised = 'true';
        const buttons = [...switcher.querySelectorAll('[data-alm-language]')];
        const status = switcher.querySelector('.alm-adminmenu__language-status');
        let pending = false;


        buttons.forEach((button) => {
            button.addEventListener('click', async (event) => {
                event.preventDefault();
                event.stopPropagation();

                const language = button.dataset.almLanguage || '';
                const endpoint = switcher.dataset.endpoint || '';
                const token = switcher.dataset.token || '';
                const moduleId = Number.parseInt(switcher.dataset.moduleId || '0', 10);

                if (pending
                    || button.getAttribute('aria-pressed') === 'true'
                    || !languagePattern.test(language)
                    || endpoint === ''
                    || token === ''
                    || !Number.isInteger(moduleId)
                    || moduleId < 1
                ) {
                    return;
                }

                pending = true;
                switcher.classList.add('is-loading');
                buttons.forEach((item) => { item.disabled = true; });

                if (status) {
                    status.textContent = language;
                }

                const controller = new AbortController();
                const timeout = window.setTimeout(() => controller.abort(), requestTimeout);
                const body = new URLSearchParams();
                body.set(token, '1');
                body.set('module_id', String(moduleId));
                body.set('language', language);

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        credentials: 'same-origin',
                        cache: 'no-store',
                        redirect: 'error',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: body.toString(),
                        signal: controller.signal,
                    });
                    const responseText = await response.text();

                    if (!response.ok || responseText.length > maximumResponseSize) {
                        throw new Error('Invalid language-switch response');
                    }

                    const responseObject = JSON.parse(responseText);
                    const payload = responseObject && responseObject.success === true
                        ? responseObject.data
                        : null;

                    if (!payload
                        || payload.changed !== true
                        || typeof payload.language !== 'string'
                        || payload.language !== language
                    ) {
                        throw new Error('Unexpected language-switch schema');
                    }

                    window.location.reload();
                } catch (error) {
                    window.clearTimeout(timeout);
                    pending = false;
                    switcher.classList.remove('is-loading');
                    buttons.forEach((item) => {
                        item.disabled = item.getAttribute('aria-pressed') === 'true';
                    });

                    if (status) {
                        status.textContent = '';
                    }

                    showError(switcher);
                } finally {
                    window.clearTimeout(timeout);
                }
            });
        });
    };

    const initialiseAll = (root = document) => {
        root.querySelectorAll('[data-alm-language-switcher]').forEach(initialise);
    };

    document.addEventListener('DOMContentLoaded', () => initialiseAll());
    document.addEventListener('joomla:updated', (event) => initialiseAll(event.target || document));

    initialiseAll();
})();
