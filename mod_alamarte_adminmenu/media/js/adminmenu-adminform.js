/**
 * Alamarte Admin Menu administrator-form integration.
 * Version: 1.4.1
 * GNU General Public License version 3 or later.
 */

(() => {
  'use strict';

  const descriptionId = 'description';
  const coreCustomLinkLimit = 3;
  const ajaxOptions = window.Joomla && typeof Joomla.getOptions === 'function'
    ? Joomla.getOptions('mod_alamarte_adminmenu.ajax', {})
    : {};
  const fieldState = new WeakMap();
  let validationCounter = 0;

  const activateDescription = (panel) => {
    const tabSet = panel.closest('joomla-tab') || document.getElementById('myTab');

    if (tabSet && typeof tabSet.activateTab === 'function') {
      tabSet.activateTab(panel);
      return true;
    }

    const control = document.querySelector([
      '#tab-description',
      '[aria-controls="description"]',
      '[data-bs-target="#description"]',
      'a[href="#description"]',
      'button[data-target="#description"]',
    ].join(','));

    if (!control || typeof control.click !== 'function') {
      return false;
    }

    control.click();
    return true;
  };

  const bindDescriptionLink = () => {
    const panel = document.getElementById(descriptionId);

    if (!panel) {
      return;
    }

    document.querySelectorAll('.readmore > a[href="#"]').forEach((link) => {
      const inlineAction = link.getAttribute('onclick') || '';

      if (!inlineAction.includes(descriptionId) || link.dataset.almDescriptionTab === 'true') {
        return;
      }

      link.removeAttribute('onclick');
      link.setAttribute('href', `#${descriptionId}`);
      link.dataset.almDescriptionTab = 'true';

      link.addEventListener('click', (event) => {
        event.preventDefault();

        if (activateDescription(panel)) {
          return;
        }

        if (window.customElements && typeof window.customElements.whenDefined === 'function') {
          window.customElements.whenDefined('joomla-tab').then(() => activateDescription(panel));
        }
      });
    });
  };

  const rowFor = (field) => field.closest('[data-group]')
    || field.closest('.subform-repeatable-group')
    || field.closest('tr')
    || field.parentElement;

  const queryRow = (row, suffix) => row ? row.querySelector(`[name$="[${suffix}]"]`) : null;

  const rowEnabled = (row) => {
    if (!row) {
      return true;
    }

    const checked = row.querySelector('[name$="[enabled]"]:checked');

    return !checked || checked.value === '1';
  };

  const selectedLinkSource = (row) => {
    if (!row) {
      return 'predefined';
    }

    const checked = row.querySelector('[name$="[source]"]:checked');
    const sourceField = checked || queryRow(row, 'source');
    const source = sourceField && typeof sourceField.value === 'string'
      ? sourceField.value.trim().toLowerCase()
      : '';

    return source === 'manual' ? 'manual' : 'predefined';
  };

  const fieldContainer = (field) => field
    ? field.closest('.control-group, .mb-3, .form-group, .control-wrapper')
    : null;

  const setFieldVisibility = (field, visible) => {
    const container = fieldContainer(field);

    if (!container) {
      return;
    }

    container.hidden = !visible;
    container.setAttribute('aria-hidden', visible ? 'false' : 'true');
  };

  const ensureValidationUi = (field) => {
    let state = fieldState.get(field);

    if (state && state.wrapper && state.wrapper.isConnected) {
      return state;
    }

    const parent = field.parentNode;

    if (!parent) {
      return null;
    }

    const wrapper = document.createElement('div');
    const control = document.createElement('div');
    const indicator = document.createElement('span');
    const icon = document.createElement('span');
    const feedback = document.createElement('div');
    const feedbackId = `alm-adminmenu-url-feedback-${validationCounter += 1}`;

    wrapper.className = 'alm-adminmenu-url-validation';
    control.className = 'alm-adminmenu-url-control';
    indicator.className = 'alm-adminmenu-url-indicator';
    indicator.hidden = true;
    indicator.setAttribute('aria-hidden', 'true');
    icon.setAttribute('aria-hidden', 'true');
    feedback.className = 'alm-adminmenu-url-feedback';
    feedback.id = feedbackId;
    feedback.hidden = true;
    feedback.setAttribute('role', 'status');
    feedback.setAttribute('aria-live', 'polite');
    feedback.setAttribute('aria-atomic', 'true');

    parent.insertBefore(wrapper, field);
    wrapper.append(control, feedback);
    control.append(field, indicator);
    indicator.append(icon);
    const describedBy = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean);

    if (!describedBy.includes(feedbackId)) {
      describedBy.push(feedbackId);
    }

    field.setAttribute('aria-describedby', describedBy.join(' '));

    state = {
      wrapper,
      indicator,
      icon,
      feedback,
      timer: 0,
      controller: null,
      sequence: 0,
    };
    fieldState.set(field, state);

    return state;
  };

  const stopPending = (state) => {
    if (state.timer) {
      window.clearTimeout(state.timer);
      state.timer = 0;
    }

    if (state.controller) {
      state.controller.abort();
      state.controller = null;
    }
  };

  const setValidationState = (field, mode, message = '') => {
    const state = ensureValidationUi(field);

    if (!state) {
      return;
    }

    state.wrapper.classList.remove('is-pending', 'is-valid', 'is-invalid', 'is-unavailable');
    state.icon.className = '';
    field.removeAttribute('aria-invalid');

    if (mode === 'neutral') {
      state.indicator.hidden = true;
      state.feedback.hidden = true;
      state.feedback.textContent = '';
      return;
    }

    const iconClasses = {
      pending: ['fa-solid', 'fa-spinner', 'fa-spin'],
      valid: ['fa-solid', 'fa-check'],
      invalid: ['fa-solid', 'fa-times'],
      unavailable: ['fa-solid', 'fa-triangle-exclamation'],
    };

    state.wrapper.classList.add(`is-${mode}`);
    state.indicator.hidden = false;
    state.feedback.hidden = false;
    state.feedback.textContent = message;
    state.icon.classList.add(...(iconClasses[mode] || []));

    if (mode === 'invalid') {
      field.setAttribute('aria-invalid', 'true');
    }
  };

  const translated = (key) => window.Joomla
    && Joomla.Text
    && typeof Joomla.Text._ === 'function'
    ? Joomla.Text._(key)
    : '';

  const labels = () => ({
    checking: ajaxOptions.labels && ajaxOptions.labels.checking
      ? ajaxOptions.labels.checking
      : translated('MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_URL_CHECKING'),
    valid: ajaxOptions.labels && ajaxOptions.labels.valid
      ? ajaxOptions.labels.valid
      : translated('MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_URL_VALID'),
    invalid: ajaxOptions.labels && ajaxOptions.labels.invalid
      ? ajaxOptions.labels.invalid
      : translated('MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_URL_INVALID'),
    unavailable: ajaxOptions.labels && ajaxOptions.labels.unavailable
      ? ajaxOptions.labels.unavailable
      : translated('MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_URL_UNAVAILABLE'),
  });

  const hasObviousLocalError = (value) => {
    if (value.length > 2048 || value.startsWith('//') || value.includes('\\')) {
      return true;
    }

    if (/%(?![0-9a-f]{2})/i.test(value) || /%25[0-9a-f]{2}/i.test(value)) {
      return true;
    }

    for (let index = 0; index < value.length; index += 1) {
      const code = value.charCodeAt(index);

      if (code <= 32 || code === 127) {
        return true;
      }
    }

    return /^(?:javascript|data|file|vbscript):/i.test(value);
  };

  const validResponsePayload = (payload) => payload
    && typeof payload === 'object'
    && typeof payload.valid === 'boolean'
    && typeof payload.normalized === 'string'
    && typeof payload.code === 'string'
    && payload.normalized.length <= 2048
    && !payload.normalized.includes('\\');

  const currentModuleId = () => {
    const field = document.querySelector('#jform_id, [name="jform[id]"]');
    const fromField = field ? Number.parseInt(field.value, 10) : Number.NaN;

    if (Number.isInteger(fromField) && fromField >= 0) {
      return fromField;
    }

    const configured = Number.parseInt(ajaxOptions.moduleId, 10);

    return Number.isInteger(configured) && configured >= 0 ? configured : 0;
  };

  const requestValidation = async (field, value, sequence) => {
    const state = ensureValidationUi(field);

    if (!state || !ajaxOptions.endpoint || !ajaxOptions.token) {
      setValidationState(field, 'unavailable', labels().unavailable);
      return;
    }

    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), Number(ajaxOptions.timeout) || 8000);
    state.controller = controller;

    const body = new URLSearchParams();
    body.set(String(ajaxOptions.token), '1');
    body.set('source', 'manual');
    body.set('url', value);
    body.set('module_id', String(currentModuleId()));

    try {
      const response = await fetch(String(ajaxOptions.endpoint), {
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
      const maximumSize = Number(ajaxOptions.maxResponseSize) || 8192;

      if (!response.ok || responseText.length > maximumSize) {
        throw new Error('Invalid validation response');
      }

      const responseObject = JSON.parse(responseText);
      const payload = responseObject && responseObject.success === true ? responseObject.data : null;
      const row = rowFor(field);

      if (sequence !== state.sequence
        || field.value.trim() !== value
        || !rowEnabled(row)
        || selectedLinkSource(row) !== 'manual'
      ) {
        return;
      }

      if (!validResponsePayload(payload)) {
        throw new Error('Unexpected validation schema');
      }

      if (payload.valid && !hasObviousLocalError(payload.normalized)) {
        setValidationState(field, 'valid', labels().valid);
      } else {
        setValidationState(field, 'invalid', labels().invalid);
      }
    } catch (error) {
      if (error && error.name === 'AbortError') {
        return;
      }

      if (sequence === state.sequence) {
        setValidationState(field, 'unavailable', labels().unavailable);
      }
    } finally {
      window.clearTimeout(timeout);

      if (state.controller === controller) {
        state.controller = null;
      }
    }
  };

  const scheduleValidation = (field) => {
    const state = ensureValidationUi(field);

    if (!state) {
      return;
    }

    stopPending(state);
    state.sequence += 1;

    const row = rowFor(field);
    const value = field.value.trim();

    if (!rowEnabled(row) || selectedLinkSource(row) !== 'manual' || value === '') {
      setValidationState(field, 'neutral');
      return;
    }

    if (hasObviousLocalError(value)) {
      setValidationState(field, 'invalid', labels().invalid);
      return;
    }

    setValidationState(field, 'pending', labels().checking);
    const sequence = state.sequence;
    state.timer = window.setTimeout(() => {
      state.timer = 0;
      requestValidation(field, value, sequence);
    }, Number(ajaxOptions.debounce) || 600);
  };

  const syncRow = (row) => {
    if (!row) {
      return;
    }

    const enabled = rowEnabled(row);
    const source = selectedLinkSource(row);
    const title = queryRow(row, 'title');
    const menuDestination = queryRow(row, 'url');
    const customUrl = queryRow(row, 'custom_url');
    const usesPredefined = enabled && source === 'predefined';
    const usesManual = enabled && source === 'manual';

    row.classList.toggle('alm-adminmenu-custom-row--disabled', !enabled);
    row.dataset.almLinkSource = source;

    if (title) {
      if (enabled) {
        title.setAttribute('required', 'required');
      } else {
        title.removeAttribute('required');
        title.removeAttribute('aria-invalid');
      }
    }

    if (menuDestination) {
      setFieldVisibility(menuDestination, usesPredefined);

      if (usesPredefined) {
        menuDestination.setAttribute('required', 'required');
      } else {
        menuDestination.removeAttribute('required');
        menuDestination.removeAttribute('aria-invalid');
      }
    }

    if (customUrl) {
      setFieldVisibility(customUrl, usesManual);
      ensureValidationUi(customUrl);

      if (usesManual) {
        customUrl.setAttribute('required', 'required');
        scheduleValidation(customUrl);
      } else {
        customUrl.removeAttribute('required');
        customUrl.removeAttribute('aria-invalid');
        const state = fieldState.get(customUrl);

        if (state) {
          stopPending(state);
        }

        setValidationState(customUrl, 'neutral');
      }
    }
  };

  const applyCoreCustomLinkLimit = (scope = document) => {
    const subforms = [];

    if (scope instanceof Element
      && scope.matches('joomla-field-subform[name="jform[params][custom_links]"]')) {
      subforms.push(scope);
    }

    if (typeof scope.querySelectorAll === 'function') {
      subforms.push(...scope.querySelectorAll('joomla-field-subform[name="jform[params][custom_links]"]'));
    }

    [...new Set(subforms)].forEach((subform) => {
      // Joomla reads this attribute dynamically in addRow(). Keeping the XML maximum
      // at 50 lets Core preserve Pro-only rows 4..50 when the module is saved.
      subform.setAttribute('maximum', String(coreCustomLinkLimit));
      subform.dataset.almCoreCustomLinkLimit = String(coreCustomLinkLimit);

      const rows = [...subform.children].filter((child) => child.matches('.subform-repeatable-group'));

      rows.forEach((row, index) => {
        const hidden = index >= coreCustomLinkLimit;
        row.hidden = hidden;
        row.classList.toggle('alm-adminmenu-core-preserved-row', hidden);
        row.setAttribute('aria-hidden', hidden ? 'true' : 'false');
      });

      subform.querySelectorAll('.group-add').forEach((button) => {
        if (!(button instanceof HTMLButtonElement) || button.closest('joomla-field-subform') !== subform) {
          return;
        }

        const atLimit = rows.length >= coreCustomLinkLimit;
        button.disabled = atLimit;
        button.setAttribute('aria-disabled', atLimit ? 'true' : 'false');
      });
    });
  };

  const initialiseCustomLinks = (scope = document) => {
    applyCoreCustomLinkLimit(scope);
    const fields = [];

    if (scope instanceof HTMLInputElement && scope.name.endsWith('[custom_url]')) {
      fields.push(scope);
    }

    if (typeof scope.querySelectorAll === 'function') {
      fields.push(...scope.querySelectorAll('[name$="[custom_url]"]'));
    }

    [...new Set(fields)].forEach((field) => {
      const row = rowFor(field);

      if (row && row.classList.contains('alm-adminmenu-core-preserved-row')) {
        const state = fieldState.get(field);

        if (state) {
          stopPending(state);
        }

        field.removeAttribute('required');
        field.removeAttribute('aria-invalid');
        return;
      }

      const subform = field.closest('joomla-field-subform');

      if (subform) {
        subform.classList.add('alm-adminmenu-custom-links-subform');
      }

      syncRow(row);
    });

    syncProCustomLinkPreviews(scope);
  };

  const bindCustomLinks = () => {
    initialiseCustomLinks();

    document.addEventListener('input', (event) => {
      const field = event.target;

      if (field instanceof HTMLInputElement && field.name.endsWith('[custom_url]')) {
        scheduleValidation(field);
      }
    });

    document.addEventListener('change', (event) => {
      const field = event.target;

      if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement)) {
        return;
      }

      if (field.name.endsWith('[source]') || field.name.endsWith('[enabled]')) {
        syncRow(rowFor(field));
      }
    });

    document.addEventListener('subform-row-add', (event) => {
      const row = event.detail && event.detail.row ? event.detail.row : event.target;

      if (row instanceof Element) {
        initialiseCustomLinks(row);

        const subform = row.closest('joomla-field-subform[name="jform[params][custom_links]"]');

        if (subform) {
          applyCoreCustomLinkLimit(subform);
        }
      }
    });

    document.addEventListener('subform-row-remove', (event) => {
      const subform = event.target instanceof Element
        ? event.target.closest('joomla-field-subform[name="jform[params][custom_links]"]')
        : null;

      if (subform) {
        window.setTimeout(() => {
          applyCoreCustomLinkLimit(subform);
          initialiseCustomLinks(subform);
        }, 0);
      }
    });

    const observer = new MutationObserver((records) => {
      records.forEach((record) => {
        record.addedNodes.forEach((node) => {
          if (node instanceof Element) {
            initialiseCustomLinks(node);
            applyCoreCustomLinkLimit(node);
            organiseJoomlaLinksPanel();
            bindJoomlaLinksReset(node);
          }
        });
      });
    });

    observer.observe(document.body, {childList: true, subtree: true});
  };


  const joomlaLinkParameters = [
    'show_content_title',
    'show_articles',
    'show_new_article',
    'show_featured',
    'show_categories',
    'show_fields',
    'show_field_groups',
    'show_workflows',
    'show_tags',
    'show_media',
    'show_menus',
    'show_menu_items',
    'show_modules',
    'show_contacts',
    'show_contact_categories',
    'show_contact_fields',
    'show_contact_field_groups',
    'show_newsfeeds',
    'show_newsfeed_categories',
    'show_search_index',
    'show_content_maps',
    'show_search_filters',
    'show_search_terms',
    'show_admin_title',
    'show_global_config',
    'show_system_info',
    'show_user_actions_log',
    'show_scheduled_tasks',
    'show_guided_tours',
    'show_postinstall_messages',
    'show_private_messages',
    'show_mail_templates',
    'show_redirects',
    'show_checkin',
    'show_cache',
    'show_users',
    'show_user_groups',
    'show_access_levels',
    'show_user_notes',
    'show_user_note_categories',
    'show_privacy_requests',
    'show_privacy_consents',
    'show_languages',
    'show_content_languages',
    'show_language_overrides',
    'show_multilingual_associations',
    'show_modules_admin',
    'show_plugins',
    'show_templates',
    'show_admin_templates',
    'show_extensions_title',
    'show_ext_manage',
    'show_ext_update',
    'show_ext_install',
    'show_ext_update_sites',
    'show_ext_discover',
    'show_ext_database',
    'show_ext_warnings',
    'show_ext_install_languages',
    'show_joomla_update',
  ];

  const joomlaLinkDefaultOn = new Set([
    'show_content_title',
    'show_articles',
    'show_categories',
    'show_tags',
    'show_media',
    'show_menus',
    'show_modules',
    'show_admin_title',
    'show_global_config',
    'show_modules_admin',
    'show_plugins',
    'show_users',
    'show_checkin',
    'show_cache',
    'show_extensions_title',
    'show_ext_manage',
    'show_ext_update',
    'show_ext_install',
    'show_joomla_update',
  ]);

  const joomlaLinkSections = [
    {
      key: 'content',
      headingSelector: '.alm-adminmenu-links-section-heading--content',
      toggleSelector: '.alm-adminmenu-links-section-toggle--content',
      groups: [
        ['content-publishing', 'content-contacts'],
        ['content-navigation', 'content-search'],
      ],
    },
    {
      key: 'admin',
      headingSelector: '.alm-adminmenu-links-section-heading--admin',
      toggleSelector: '.alm-adminmenu-links-section-toggle--admin',
      groups: [
        ['admin-system', 'admin-languages'],
        ['admin-users', 'admin-presentation'],
      ],
    },
    {
      key: 'extensions',
      headingSelector: '.alm-adminmenu-links-section-heading--extensions',
      toggleSelector: '.alm-adminmenu-links-section-toggle--extensions',
      groups: [
        ['extensions-management'],
        ['extensions-maintenance'],
      ],
    },
  ];

  const createLinkGroupCard = (host, key) => {
    const selector = `.alm-adminmenu-links-group--${key}`;
    const nodes = [...host.querySelectorAll(selector)];

    if (nodes.length === 0) {
      return null;
    }

    const heading = nodes.find((node) => node.classList.contains('alm-adminmenu-links-group-heading'));

    if (!heading) {
      return null;
    }

    const card = document.createElement('div');
    const body = document.createElement('div');

    card.className = 'alm-adminmenu-links-group';
    body.className = 'alm-adminmenu-links-group-body';
    card.append(heading, body);
    nodes.filter((node) => node !== heading).forEach((node) => body.append(node));

    return card;
  };

  const setJoomlaLinkBoolean = (parameter, enabled) => {
    const name = `jform[params][${parameter}]`;
    const controls = [...document.getElementsByName(name)];
    const checkboxes = controls.filter((control) => control instanceof HTMLInputElement && control.type === 'checkbox');

    if (checkboxes.length > 0) {
      checkboxes.forEach((checkbox) => {
        checkbox.checked = enabled;
        checkbox.dispatchEvent(new Event('input', {bubbles: true}));
        checkbox.dispatchEvent(new Event('change', {bubbles: true}));
      });
      return;
    }

    const targetValue = enabled ? '1' : '0';
    const radios = controls.filter((control) => control instanceof HTMLInputElement && control.type === 'radio');

    radios.forEach((radio) => {
      const shouldCheck = radio.value === targetValue;

      if (radio.checked !== shouldCheck) {
        radio.checked = shouldCheck;
      }
    });

    const selected = radios.find((radio) => radio.checked);

    if (selected) {
      selected.dispatchEvent(new Event('input', {bubbles: true}));
      selected.dispatchEvent(new Event('change', {bubbles: true}));
    }
  };

  const resetJoomlaLinkDefaults = (button) => {
    joomlaLinkParameters.forEach((parameter) => {
      setJoomlaLinkBoolean(parameter, joomlaLinkDefaultOn.has(parameter));
    });

    const control = button.closest('.alm-adminmenu-reset-control');
    const status = control ? control.querySelector('[data-alm-adminmenu-reset-status]') : null;

    if (status) {
      status.hidden = false;
    }
  };

  const bindJoomlaLinksReset = (root = document) => {
    root.querySelectorAll('[data-alm-adminmenu-reset-defaults]').forEach((button) => {
      if (!(button instanceof HTMLButtonElement) || button.dataset.almAdminmenuResetBound === 'true') {
        return;
      }

      button.dataset.almAdminmenuResetBound = 'true';
      button.addEventListener('click', () => resetJoomlaLinkDefaults(button));
    });
  };

  const organiseJoomlaLinksPanel = () => {
    const headings = joomlaLinkSections.map((section) => document.querySelector(section.headingSelector));

    if (headings.some((heading) => !heading) || headings[0].closest('.alm-adminmenu-links-sections')) {
      return;
    }

    const host = headings[0].parentElement;

    if (!host || host.querySelector(':scope > .alm-adminmenu-links-sections')) {
      return;
    }

    const sectionsRoot = document.createElement('div');
    sectionsRoot.className = 'alm-adminmenu-links-sections';
    host.insertBefore(sectionsRoot, headings[0]);

    joomlaLinkSections.forEach((sectionConfig, sectionIndex) => {
      const heading = headings[sectionIndex];
      const toggle = host.querySelector(sectionConfig.toggleSelector);
      const section = document.createElement('section');
      const grid = document.createElement('div');
      const label = heading.querySelector('label, .control-label, .form-label') || heading;
      const headingId = `alm-adminmenu-${sectionConfig.key}-links-heading`;

      section.className = `alm-adminmenu-links-section alm-adminmenu-links-section--${sectionConfig.key}`;
      grid.className = 'alm-adminmenu-links-section-grid';
      label.id = headingId;
      section.setAttribute('aria-labelledby', headingId);
      section.append(heading);

      if (toggle) {
        section.append(toggle);
      }

      section.append(grid);
      sectionsRoot.append(section);

      sectionConfig.groups.forEach((columnGroupKeys) => {
        const column = document.createElement('div');
        column.className = 'alm-adminmenu-links-section-column';

        columnGroupKeys.forEach((groupKey) => {
          const card = createLinkGroupCard(host, groupKey);

          if (card) {
            column.append(card);
          }
        });

        grid.append(column);
      });
    });
  };


  const proPreviewPairs = [
    ['menu_title', 'menu_title_pro_preview'],
  ];

  const syncProPreviewValue = (source, preview) => {
    if (!(source instanceof HTMLInputElement || source instanceof HTMLSelectElement)
      || !(preview instanceof HTMLInputElement || preview instanceof HTMLSelectElement)) {
      return;
    }

    preview.value = source.value;
  };

  const syncGlobalProPreviews = () => {
    proPreviewPairs.forEach(([sourceParameter, previewParameter]) => {
      const source = document.querySelector(`[name="jform[params][${sourceParameter}]"]`);
      const preview = document.querySelector(`[name="jform[params][${previewParameter}]"]`);

      syncProPreviewValue(source, preview);
    });
  };

  const iconPickerOptions = () => window.Joomla
    && typeof Joomla.getOptions === 'function'
    ? Joomla.getOptions('mod_alamarte_adminmenu.iconPicker', {})
    : {};

  const syncDisabledIconPicker = (source, previewInput) => {
    if (!(source instanceof HTMLInputElement) || !(previewInput instanceof HTMLInputElement)) {
      return;
    }

    const value = source.value || 'fa-solid fa-link';
    const picker = previewInput.closest('[data-alm-icon-picker]');

    previewInput.value = value;

    if (!picker) {
      return;
    }

    const glyph = picker.querySelector('[data-alm-icon-preview]');
    const selected = picker.querySelector('[data-alm-icon-selected]');
    const catalog = Array.isArray(iconPickerOptions().icons) ? iconPickerOptions().icons : [];
    const definition = catalog.find((icon) => icon && icon.value === value);

    if (glyph) {
      [...glyph.classList]
        .filter((className) => className === 'fa-solid'
          || className === 'fa-regular'
          || className === 'fa-brands'
          || className.startsWith('fa-'))
        .forEach((className) => glyph.classList.remove(className));

      if (value !== 'none') {
        glyph.classList.add(...value.split(/\s+/).filter(Boolean));
      }

      glyph.classList.toggle('is-none', value === 'none');
      glyph.dataset.icon = value;
    }

    if (selected instanceof HTMLInputElement) {
      selected.value = definition && definition.label ? definition.label : value;
    }
  };

  const syncProCustomLinkPreviews = (scope = document) => {
    const rows = [];

    if (scope instanceof Element && scope.matches('.subform-repeatable-group')) {
      rows.push(scope);
    }

    if (typeof scope.querySelectorAll === 'function') {
      rows.push(...scope.querySelectorAll('.subform-repeatable-group'));
    }

    [...new Set(rows)].forEach((row) => {
      if (!row.closest('joomla-field-subform[name="jform[params][custom_links]"]')) {
        return;
      }

      const icon = row.querySelector('[name$="[icon]"]');
      const iconPreview = row.querySelector('[name$="[icon_pro_preview]"]');
      const target = row.querySelector('[name$="[target]"]');
      const targetPreview = row.querySelector('[name$="[target_pro_preview]"]');

      syncDisabledIconPicker(icon, iconPreview);
      syncProPreviewValue(target, targetPreview);
    });
  };

  const bindCorePanelWidthProxy = () => {
    const stored = document.querySelector('[name="jform[params][panel_width_preset]"]');
    const core = document.querySelector('[name="jform[params][core_panel_width_preset]"]');
    const allowed = new Set(['compact', 'standard', 'wide']);

    if (!(stored instanceof HTMLInputElement) || !(core instanceof HTMLSelectElement)) {
      return;
    }

    // Existing Core installations may have saved the common parameter directly.
    // A Pro Custom value is deliberately left untouched so it can be restored
    // when Pro is installed again. Core uses a supported width instead.
    let lastValid = allowed.has(core.value)
      ? core.value
      : (allowed.has(stored.value) ? stored.value : 'standard');

    if (core.value === 'standard' && allowed.has(stored.value) && stored.value !== 'standard') {
      core.value = stored.value;
      lastValid = stored.value;
      core.dispatchEvent(new Event('change', {bubbles: true}));
    } else if (!allowed.has(core.value)) {
      core.value = lastValid;
      core.dispatchEvent(new Event('change', {bubbles: true}));
    }

    core.addEventListener('change', () => {
      if (allowed.has(core.value)) {
        lastValid = core.value;
        stored.value = core.value;
      }
      // "custom" is intentionally left selected in the UI so Joomla's showon
      // displays the Pro notice. It is never copied to the stored Core width.
    });

    const normaliseBeforeSave = () => {
      if (allowed.has(core.value)) {
        return;
      }

      core.value = lastValid;
    };

    const form = core.closest('form');

    if (form) {
      form.addEventListener('submit', normaliseBeforeSave, true);
    }

    // Joomla administrator toolbar actions can submit the form from their own
    // click handler. Capture save/apply actions first so "custom" is never
    // persisted as an active Core width.
    document.addEventListener('click', (event) => {
      if (allowed.has(core.value)) {
        return;
      }

      const control = event.target instanceof Element
        ? event.target.closest('button, a')
        : null;

      if (!control) {
        return;
      }

      const action = [
        control.getAttribute('onclick') || '',
        control.getAttribute('data-submit-task') || '',
        control.getAttribute('data-task') || '',
        control.getAttribute('id') || '',
      ].join(' ');

      if (/module\.(?:apply|save|save2new|save2copy)|submitbutton/i.test(action)) {
        normaliseBeforeSave();
      }
    }, true);
  };

  const initialise = () => {
    bindDescriptionLink();
    syncGlobalProPreviews();
    bindCorePanelWidthProxy();
    applyCoreCustomLinkLimit();
    organiseJoomlaLinksPanel();
    bindJoomlaLinksReset();
    bindCustomLinks();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialise, {once: true});
  } else {
    initialise();
  }
})();
