# Alamarte Admin Menu Core — Changelog

## Version 1.4.1

### Changed

- Synchronized the Core public release with Alamarte Admin Menu Pro 1.4.1.
- Core has no functional behaviour changes in this release; its existing restrictions, saved-parameter preservation, themes, links, ACL validation and Joomla! Administrator visual preset remain unchanged.
- Refreshed version metadata, Web Asset Manager revision, bilingual installer information, documentation, update-server data and integrity files for release 1.4.1.

## Version 1.4.0

### Added

- Added contextual end-user explanations for Auto, Light and Dark below the Default Theme selector.
- Added contextual Panel Width explanations for Compact, Standard and Wide, plus an in-context Pro notice when Custom is selected.
- Added a permanent Styles information box explaining that Core uses the Joomla! Administrator visual preset and that additional presets and custom colours are available in Pro.

### Fixed

- Refreshed the internal Joomla Web Asset Manager cache revision so the final 1.4.0 CSS and JavaScript are requested immediately after installation or update instead of reusing an older cached 1.4.0 asset.
- Integrated the administrator trigger with Joomla's native header-item pill so the menu control no longer draws a conflicting nested pill.
- Changed the Dashboard/Home action to the explicit Font Awesome `fas fa-home` icon and gave it a distinct circular action surface with restrained hover and keyboard-focus feedback.
- Rebuilt the ES/EN language selector as a clean segmented control with one outer border and a single internal divider.
- Aligned the Joomla visual preset with the validated administrator light and dark appearances while keeping Auto synchronized with Joomla's active light/dark mode.

### Changed

- Shortened the Spanish theme option from “Automático” to “Auto” while keeping the stored value `auto` unchanged.
- Core presents Pro-only capabilities in context without filling the interface with repeated Pro labels: the custom menu title, advanced link icon/target controls and Visual Preset remain discoverable while Core behaviour stays restricted.
- Core keeps the Joomla! Administrator visual preset fixed internally, uses Compact, Standard or Wide panel widths, and treats Custom width only as a Pro feature-discovery choice.
- Core continues to display and use up to three custom links with the standard link icon and current administrator window.
- Previously saved Pro menu-title, icon, target, visual-preset, colour, custom-width and additional-link values remain preserved for a later Core → Pro round trip and are not applied by Core.
- The visible module version indicator identifies Core with a fixed-colour edition pill beside `Version: 1.4.0`; Core and Pro badges keep stable contrast in both light and dark administrator themes.
- Increased spacing and internal padding around the Core edition pill so the version indicator remains compact but no longer appears cramped in either administrator theme.
- Synchronized version metadata, bilingual interface text, documentation, update-server data and integrity files for release 1.4.0.

## Version 1.3.1

### Added

- Added the Show title switch for the theme selector, shared by Core and Pro.

### Fixed

- Corrected the English Custom Links instruction so the documented scrolling threshold matches the sixth visible link used by the runtime and Spanish interface.

### Changed

- Established the explicit Alamarte Admin Menu Core edition identity while preserving the shared technical element `mod_alamarte_adminmenu` and namespace `Alamarte\Module\AdminMenu`.
- Core displays and uses up to three custom links while preserving additional Pro rows in saved form data.
- Core custom links use the standard link icon and current administrator window; saved Pro icon and target values remain stored but are ignored by Core.
- Core uses the standard Admin Menu trigger label while preserving any saved Pro menu-title parameter.
- Theme selector, theme-title visibility, theme default, language switching, styles, colors, panel widths, section-heading controls, Joomla! Links, ACL filtering and URL validation remain shared with Pro.
- Moved the Core update and changelog endpoints to `/updates/modules/adminmenu/core/`.
- Preserved module instances, titles, positions, publication state, assignments and saved parameters during update.
- Synchronized version metadata, documentation, update server data and integrity files for release 1.3.1.

## Version 1.3.0.27

### Fixed

- Restored custom links that disappeared after the 1.3.0.26 administrator-only URL hardening.
- Normalized Joomla-routed administrator URLs before the final administrator-route and ACL checks, so valid manual and predefined custom links remain visible after `Route::_()` converts them to administrator-relative paths.
- Kept the 1.3.0.26 security boundary intact: external origins, frontend routes, HTTP addresses, credentials, fragments, alternate ports, protocol-relative URLs, and non-administrator destinations remain rejected.

### Changed

- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.26

### Security

- Restored the administrator-only boundary for manually entered custom links.
- Rejected external origins, frontend paths, HTTP addresses, subdomains, alternate ports, credentials, fragments, protocol-relative addresses, and non-administrator routes.
- Required every accepted manual destination to normalize to an internal administrator `index.php?...` route before ACL evaluation and rendering.

### Changed

- Updated the Custom Links descriptions and examples in English and Spanish so the interface no longer suggests external URLs.
- Centralized the internal URL validator used by administrator-link identity and selector-deduplication helpers.
- Escaped every Font Awesome CSS class at the template output boundary as an additional defensive measure.
- Reviewed all broad exception handlers individually; retained the fail-closed or installer-safe handlers that deliberately avoid exposing database and environment details.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.25

### Changed

- Localized each language-button help text in the target language selected by that button instead of using the currently active administrator language for both buttons.
- The Spanish button now uses the complete Spanish sentence and the English button the complete English sentence, while keeping the native language name and language tag only once.
- Loaded the module translation for each configured target language through Joomla's language service, with English as a safe fallback when the module has no translation for that language.
- Removed the browser-side tooltip reconstruction because the complete validated help text is now generated on the server.
- Kept language tags, session-only switching, routes, parameters, saved values, permissions, AJAX controls, toolbar layout, and all menu content unchanged.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.24

### Fixed

- Displayed each administrator language with its native self-name in the selector help text instead of the English package name when native locale information is available.
- Removed a repeated language-tag suffix so the help text now follows the form “Español (es-ES)”.

### Changed

- Used Joomla language metadata first, PHP Intl as a server-side fallback, and the browser Intl.DisplayNames API as a progressive enhancement.
- Kept the selector buttons, language tags, session-only switching, routes, parameters, saved values, permissions, AJAX controls, and toolbar layout unchanged.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.23

### Fixed

- Centred the Backend language selector in the real free space between the complete theme selector and the version number.
- Prevented the theme controls from wrapping at standard and compact desktop panel widths while preserving the existing responsive arrangement on narrower screens.

### Changed

- Kept the language-switching service, routes, parameters, stored values, defaults, permissions, AJAX controls, and all menu content unchanged.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.22

### Added

- Added a dedicated Languages tab with controls to show the Backend language selector, show or hide its title, customise the title text, and choose two installed administrator languages.
- Added a compact two-language selector centred in the module toolbar between the theme controls and the version number.
- Added a session-only administrator-language change through an authenticated Joomla com_ajax POST request with CSRF protection and validation against the two configured installed languages.

### Changed

- The language selector reloads the current administrator page after changing the session language and does not modify Joomla!'s default language or the user's permanent profile settings.
- Updated the bilingual descriptions, instructions, version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.21

### Changed

- Increased the visible-link threshold from five to six so the standard Content and Administration shortcut sets display without scrolling.
- Reworded the Restore Default Values guidance without references to previous releases.
- Changed the reset confirmation to a warning notice with an icon and a bold reminder to save the module.
- Kept routes, parameters, stored values, defaults, permissions, AJAX validation, and menu-building rules unchanged.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.20

### Changed

- Made the Joomla! Update hover background use “Link hover background colour” from the Styles tab.
- Made the Joomla! Update hover title and icon use “Link title hover colour” from the Styles tab.
- Kept the normal luminous orange state, right alignment, routes, parameters, saved values, defaults, permissions, reset behaviour, and menu-building rules unchanged.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.19

### Changed

- Changed only the Joomla! Update highlight to a brighter orange background with Joomla dark-blue text and icon.
- Kept the action aligned at the far right and preserved all routes, parameters, saved values, defaults, permissions, reset behaviour, and menu-building rules.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.18

### Changed

- Returned Joomla! Update to the far right of the Management and Installation row.
- Replaced the muted warning treatment with a brighter, higher-contrast orange palette derived from the administrator template colours, including an accessible hover state.
- Kept routes, parameters, saved values, defaults, permissions, reset behaviour, and menu-building rules unchanged.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.17

### Changed

- Arranged Management and Installation actions in one row and Maintenance and Updates actions in a second row in the rendered module.
- Highlighted Joomla! Update with the administrator template warning palette and accessible fallback colours.
- Kept routes, parameters, saved values, defaults, permissions, reset behaviour, and menu-building rules unchanged.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.16

### Fixed

- Corrected the two exchanged fields in the Joomla! Extensions configuration: Joomla! Update now appears in Management and Installation, while Update Sites appears in Maintenance and Updates.

### Changed

- Kept all parameter names, routes, saved values, defaults, permissions, reset behaviour, and module output unchanged.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.15

### Fixed

- Aligned the Joomla! Links configuration groups with the Content, Administration, and Joomla! Extensions sections rendered by the module.
- Moved Administrator Modules, Plugins, Site Templates, and Administrator Templates back into the Administration configuration section.
- Restored switchers for the three section-heading controls while retaining checkboxes for individual shortcuts.

### Added

- Added a Restore Default Values button that applies the exact enabled shortcut set from public version 1.2.0 and clears shortcuts introduced later.

### Changed

- Runtime fallbacks and form defaults now use the same version 1.2.0 shortcut baseline without overwriting existing saved module parameters.
- Updated bilingual interface text, version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.14

### Fixed

- Allowed every Joomla! Links checkbox label to use the full width available after the checkbox instead of inheriting Joomla administrator label width limits.

### Changed

- Kept the three-section, two-column layout and all parameters, routes, defaults, permissions, and menu-building logic unchanged.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.13

### Changed

- Replaced the sixty Joomla! Links switchers with compact semantic checkboxes while preserving every parameter name, stored value, default, and menu-building rule.
- Added an explicit hidden zero value for each checkbox so disabling a previously enabled shortcut is saved reliably.
- Aligned each checkbox at the left edge of its grouped column and retained the existing three-section responsive design.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.12

### Changed

- Redesigned the Joomla! Links tab as three full-width sections: Content Links, Administrator Links, and Joomla! Extensions.
- Arranged the existing groups in two responsive columns inside each section without changing parameters, routes, defaults, or link logic.
- Aligned every link switch at the left edge of its corresponding column.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.11

### Added

- Added twelve content shortcuts for field groups, workflows, contacts, news feeds, and Smart Search.
- Added sixteen administration shortcuts for system tools, users, privacy, languages, templates, and extension maintenance.
- Added Language Overrides (Modificaciones de idioma) as an independent administration shortcut.

### Changed

- Reorganised the Joomla! Links configuration into two responsive columns with eight functional groups.
- Kept every new shortcut disabled by default while preserving all existing parameter values and defaults.
- Made Site Templates and Installed Languages use explicit Joomla administrator routes.
- Updated bilingual interface text, version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.10

### Fixed

- Forced the three Joomla! Links section headings, labels, help icons, and nested elements to use white text on the blue background.

### Changed

- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.9

### Added

- Restored the About tab as the final configuration tab with the same localized HTML presentation used by the installer.

### Changed

- Combined Content Links, Administrator Links, and Joomla! Extension Links into one Joomla! Links tab.
- Added full-width blue section headings to separate the three link groups without changing their stored parameters or defaults.
- Updated bilingual labels, version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.8

### Fixed

- Resolved extension group labels that displayed a technical component identifier when an extension stored a literal product name instead of a language key.
- SP Page Builder entries now resolve the official COM_SPPAGEBUILDER language key and display SP Page Builder instead of com_sppagebuilder.

### Changed

- Added a generic label-resolution sequence: declared language key, component-derived language key, safe literal product name, and technical identifier only as the final fallback.
- Updated version metadata, documentation, update server data, and integrity files.

## Version 1.3.0.7

### Security

- Kept destination validation and ACL checks unchanged while adding a conservative selector-only duplicate comparison.
- Duplicate detection requires the same translated label, identical query parameters other than view, and exactly one route with an explicit view.

### Fixed

- Removed confusing duplicate Joomla administrator destinations when the database menu route omits only the explicit default view used by the predefined catalog.
- Preserved existing saved selections by mapping an equivalent historical route to the canonical predefined URL when the configuration form is opened.

### Changed

- The selector keeps the explicit predefined URL and never strips view, extension, context, client_id, layout, id, or other functional parameters.
- Different explicit views and different component contexts remain separate options.

## Version 1.3.0.6

### Security

- Restored the secure 61-destination administrator allowlist with contextual ACL validation.
- Added server-side allowlist validation for the complete Font Awesome Free icon catalog.

### Changed

- Link Source now uses standard radio options instead of a switcher.
- Internal destinations use the grouped organisation established in Alamarte Admin Menu 1.2.0.
- Custom links can select Solid, Regular, and Brands icons from the complete Font Awesome Free catalog.

### Fixed

- Restored missing predefined Joomla administrator destinations while preserving published extension menu entries.
- Replaced the limited icon list with the complete Font Awesome Free catalog and bundled fonts.

## Version 1.3.0.5

### Added

- Added an explicit Link Source selector to every custom link with Predefined and Manual options.
- Added server-side validation for the selected source and for predefined administrator destinations.

### Changed

- Only the destination field that belongs to the selected source is displayed, validated, and used.
- Manual URL AJAX validation now runs only while the row is enabled and its source is Manual.
- Reordered the three main columns as Custom Links, Content, and Administration from left to right.
- Existing rows are migrated without data loss: a saved administrator destination becomes Predefined; otherwise the row becomes Manual.
- Updated bilingual help, version metadata, update server data, and integrity files.

### Fixed

- A residual manual URL can no longer interfere with a Predefined link, and a residual predefined destination can no longer interfere with a Manual link.
- The module no longer infers the active destination from whichever field contains a value.

### Security

- Predefined destinations must belong to a published administrator menu item from an enabled component allowed by the current user's ACL.
- Invalid or manipulated source values are rejected before saving and ignored before rendering.

## Version 1.3.0.4

### Changed

- Enforced the main menu order as Administration, Content, and Custom Links from left to right in the data, template, and CSS layers.
- Applied an independent visible vertical scrollbar to every main column only when it contains more than five links.
- Updated version metadata, documentation, update server data, and integrity files.

### Fixed

- Recalculated the five-link scroll height reliably after the Bootstrap dropdown becomes visible, after resizing, and after web fonts finish loading.
- Preserved five complete links before scrolling, including labels that wrap onto more than one line.

## Version 1.3.0.3

### Changed

- Reordered the main menu columns to Administration, Content, and Custom Links from left to right.
- Added independent vertical scrolling to each of the three columns when it contains more than five links.
- Updated version metadata, documentation, update server data, and integrity files.

### Fixed

- Replaced the remaining JAMSS-sensitive URL-decoding calls in administrator query parsing with the explicit single-pass percent decoder.
- Preserved literal plus signs and the existing malformed, nested, duplicate, array, and sensitive-query rejection rules.

## Version 1.3.0.2

### Added

- Added an independent Enabled switch to every custom link so saved rows can be shown or hidden without deleting them.
- Restored secure real-time AJAX validation with accessible checking, valid, invalid, and unavailable states.
- Added vertical scrolling to the custom-links menu column and to the repeatable administrator field when their content exceeds the available height.

### Changed

- Expanded the custom-link capacity from ten to fifty stored rows.
- Restored selected administrator menu-item priority over the optional manual URL.
- Updated the bilingual interface, documentation, update server, and integrity files.

### Fixed

- Empty or null optional manual URLs no longer prevent the module from saving.
- Disabled custom-link rows no longer require a title or URL and remain stored for later use.
- Existing rows created before the Enabled switch remain enabled automatically.
- Administrator URL fragments are rejected instead of being silently removed.
- Literal and percent-encoded dot-segment traversal is rejected in administrator, HTTP, and HTTPS paths.

### Security

- AJAX requests require an authenticated administrator session, POST, a valid Joomla CSRF token, com_modules permissions, a verified module instance, same-origin request metadata, bounded input, and a bounded response.
- AJAX remains informational; Joomla repeats validation before saving and the module repeats URL and destination ACL checks before rendering.
- The JAMSS-safe URL decoding and ASCII control-character checks from version 1.3.0.1 remain unchanged.

## Version 1.3.0.1

### Fixed

- Replaced the JAMSS-sensitive raw URL decoding call with an equivalent single-pass percent decoder that preserves literal plus signs.
- Replaced hexadecimal control-character regular expressions with explicit ASCII code checks, preserving URL rejection and label sanitization without triggering JAMSS signatures.
- Revalidated custom administrator URLs, ACL filtering, installer behavior, bilingual language parity and package integrity after the JED compatibility changes.

## Version 1.3.0.0

### Added

- Consolidated the independent Alamarte Admin Menu implementation as the public 1.3.0.0 release for Joomla! 5 and Joomla! 6 with PHP 8.1 or later.
- Added namespaced service-provider and dispatcher architecture, external Web Asset Manager resources, theme selection, responsive visual presets, and the curated Font Awesome Free icon selector.
- Added ten content links, ten administration links, four Joomla! extension actions, and up to ten custom links.
- Added server-side validation for every custom URL inside the repeatable subform.
- Added secure normalization of complete administrator URLs from the same site, including installations in a subdirectory.
- Added downgrade protection with a controlled exception for consolidating internal 1.5.x development builds into public version 1.3.0.0.

### Changed

- The administrator menu selector now includes only published items from enabled components and applies the current user's ACL permissions.
- Installer database reads and writes now use bound parameters; publication, assignment, and parameter migrations use transactions with rollback.
- All PHP files now declare strict types and carry coordinated GPL and version headers.
- All visible template output, including localized labels, is escaped for its HTML context.
- Removed the Acerca de… / About tab because it repeated the installer presentation without adding a distinct configuration function.
- Consolidated the internal 1.5.x development history into this public release instead of publishing those internal version numbers.

### Fixed

- Complete administrator URLs such as `https://example.com/administrator/index.php?option=com_users&view=users` are accepted when they belong to the current site and are converted to a portable administrator route.
- Unsafe schemes, protocol-relative URLs, credentials, malformed or nested encoding, duplicate or array query parameters, and administrator `task`, `token`, or `return` parameters are rejected.
- Invalid legacy custom URLs no longer suppress a valid selected administrator menu item at runtime.
- Disabled extensions and inaccessible components no longer appear in the administrator menu-item selector.
- The installer no longer leaves partial publication, assignment, or parameter changes when a database write fails.

## Version 1.2.0

### Changed

- Consolidated the hardened legacy branch for Joomla! 5 and Joomla! 6 with PHP 8.1 or later.
- Preserved module settings, publication state, and assignments during updates.

### Security

- Strengthened URL validation, ACL checks, output escaping, installer transactions, bilingual parity, package integrity, and compatibility checks.

## Version 1.1.2

### Changed

- Prepared the previous code line for Joomla! 5 and Joomla! 6 compatibility.

## Version 1.1.1

### Fixed

- Corrected administrator menu behavior and package metadata in the previous code line.
