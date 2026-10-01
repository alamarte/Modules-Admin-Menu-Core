/*
 * Alamarte Admin Menu Core
 * Version: 1.4.1
 * GNU General Public License version 3 or later
 * https://www.gnu.org/licenses/gpl-3.0.html
 */

(() => {
    'use strict';

    const options = window.Joomla?.getOptions('mod_alamarte_adminmenu.iconPicker', {}) || {};
    const icons = Array.isArray(options.icons) ? options.icons : [];
    const labels = options.labels || {};
    let dialog = null;
    let searchInput = null;
    let grid = null;
    let emptyMessage = null;
    let activePicker = null;
    let lastTrigger = null;

    const applyIconClass = (element, value) => {
        if (!element || (value !== 'none' && !/^(?:fa-solid|fa-regular|fa-brands) fa-[a-z0-9-]+$/.test(value))) {
            return;
        }

        [...element.classList]
            .filter((className) => className === 'fa-solid'
                || className === 'fa-regular'
                || className === 'fa-brands'
                || className.startsWith('fa-'))
            .forEach((className) => element.classList.remove(className));

        element.classList.add('alm-fa-icon');

        if (value !== 'none') {
            element.classList.add(...value.split(' '));
        }

        element.classList.toggle('is-none', value === 'none');
        element.dataset.icon = value;
    };

    const closeDialog = () => {
        if (!dialog) {
            return;
        }

        if (typeof dialog.close === 'function' && dialog.open) {
            dialog.close();
        } else {
            dialog.removeAttribute('open');
        }
    };

    const visibleOptions = () => grid
        ? [...grid.querySelectorAll('[data-alm-icon-option]:not([hidden])')]
        : [];

    const filterOptions = () => {
        if (!searchInput || !grid) {
            return;
        }

        const term = searchInput.value.trim().toLocaleLowerCase();
        let visible = 0;

        grid.querySelectorAll('[data-alm-icon-option]').forEach((button) => {
            const matches = term === '' || (button.dataset.search || '').includes(term);
            button.hidden = !matches;
            visible += matches ? 1 : 0;
        });

        if (emptyMessage) {
            emptyMessage.hidden = visible !== 0;
        }
    };

    const selectIcon = (button) => {
        if (!activePicker) {
            return;
        }

        const input = activePicker.querySelector('[data-alm-icon-input]');
        const preview = activePicker.querySelector('[data-alm-icon-preview]');
        const selected = activePicker.querySelector('[data-alm-icon-selected]');
        const value = button.dataset.value || 'fa-solid fa-link';

        if (!input || !preview || !selected) {
            return;
        }

        input.value = value;
        applyIconClass(preview, value);
        selected.value = button.dataset.label || value;
        input.dispatchEvent(new Event('input', {bubbles: true}));
        input.dispatchEvent(new Event('change', {bubbles: true}));
        closeDialog();
    };

    const createDialog = () => {
        if (dialog || icons.length === 0) {
            return dialog;
        }

        dialog = document.createElement('dialog');
        dialog.className = 'alm-icon-dialog';
        dialog.setAttribute('aria-labelledby', 'alm-icon-dialog-title');

        const header = document.createElement('div');
        header.className = 'alm-icon-dialog__header px-2 py-2';

        const title = document.createElement('h2');
        title.id = 'alm-icon-dialog-title';
        title.textContent = labels.title || '';

        const close = document.createElement('button');
        close.className = 'alm-icon-dialog__close';
        close.type = 'button';
        close.textContent = '×';
        close.setAttribute('aria-label', labels.close || '');
        close.addEventListener('click', closeDialog);

        header.append(title, close);

        const searchLabel = document.createElement('label');
        searchLabel.className = 'visually-hidden';
        searchLabel.htmlFor = 'alm-icon-dialog-search';
        searchLabel.textContent = labels.search || '';

        searchInput = document.createElement('input');
        searchInput.id = 'alm-icon-dialog-search';
        searchInput.className = 'form-control alm-icon-dialog__search mx-2 my-2';
        searchInput.type = 'search';
        searchInput.placeholder = labels.search || '';
        searchInput.addEventListener('input', filterOptions);

        grid = document.createElement('div');
        grid.className = 'alm-icon-dialog__grid px-2 pb-2';
        grid.setAttribute('role', 'listbox');

        icons.forEach((icon) => {
            const button = document.createElement('button');
            button.className = 'alm-icon-dialog__option';
            button.type = 'button';
            button.dataset.almIconOption = 'true';
            button.dataset.value = icon.value;
            button.dataset.label = icon.label;
            button.dataset.search = `${icon.label} ${icon.value} ${icon.aliases || ''}`.toLocaleLowerCase();
            button.setAttribute('role', 'option');
            button.setAttribute('aria-selected', 'false');

            const glyph = document.createElement('span');
            glyph.className = 'alm-icon-dialog__glyph';
            applyIconClass(glyph, icon.value);
            glyph.setAttribute('aria-hidden', 'true');

            const label = document.createElement('span');
            label.className = 'alm-icon-dialog__label';
            label.textContent = icon.label;

            const value = document.createElement('span');
            value.className = 'alm-icon-dialog__value';
            value.textContent = icon.value;

            button.append(glyph, label, value);
            button.addEventListener('click', () => selectIcon(button));
            grid.append(button);
        });

        grid.addEventListener('keydown', (event) => {
            const choices = visibleOptions();
            const current = choices.indexOf(document.activeElement);

            if (current < 0 || !['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(event.key)) {
                return;
            }

            event.preventDefault();

            const columns = Math.max(1, getComputedStyle(grid).gridTemplateColumns.split(' ').length);
            const offsets = {ArrowLeft: -1, ArrowRight: 1, ArrowUp: -columns, ArrowDown: columns};
            let next = current;

            if (event.key === 'Home') {
                next = 0;
            } else if (event.key === 'End') {
                next = choices.length - 1;
            } else {
                next = Math.max(0, Math.min(choices.length - 1, current + offsets[event.key]));
            }

            choices[next]?.focus();
        });

        emptyMessage = document.createElement('p');
        emptyMessage.className = 'alm-icon-dialog__empty';
        emptyMessage.textContent = labels.empty || '';
        emptyMessage.hidden = true;

        dialog.append(header, searchLabel, searchInput, grid, emptyMessage);
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                closeDialog();
            }
        });
        dialog.addEventListener('close', () => {
            lastTrigger?.focus();
            activePicker = null;
            lastTrigger = null;
        });

        document.body.append(dialog);

        return dialog;
    };

    const openPicker = (picker, trigger) => {
        const pickerDialog = createDialog();

        if (!pickerDialog || !searchInput || !grid) {
            return;
        }

        activePicker = picker;
        lastTrigger = trigger;
        searchInput.value = '';
        filterOptions();

        const current = picker.querySelector('[data-alm-icon-input]')?.value || 'fa-solid fa-link';

        grid.querySelectorAll('[data-alm-icon-option]').forEach((button) => {
            button.setAttribute('aria-selected', button.dataset.value === current ? 'true' : 'false');
        });

        if (typeof pickerDialog.showModal === 'function') {
            pickerDialog.showModal();
        } else {
            pickerDialog.setAttribute('open', '');
        }

        searchInput.focus();
    };

    const initialisePicker = (picker) => {
        if (picker.dataset.almIconInitialised === 'true') {
            return;
        }

        const trigger = picker.querySelector('[data-alm-icon-trigger]');

        if (!trigger) {
            return;
        }

        picker.dataset.almIconInitialised = 'true';
        trigger.addEventListener('click', () => openPicker(picker, trigger));
    };

    const initialiseAll = (root = document) => {
        root.querySelectorAll('[data-alm-icon-picker]').forEach(initialisePicker);
    };

    document.addEventListener('DOMContentLoaded', () => initialiseAll());
    document.addEventListener('joomla:updated', (event) => initialiseAll(event.target || document));

    initialiseAll();
})();
