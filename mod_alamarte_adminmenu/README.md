# Alamarte Admin Menu Core 1.4.1

Alamarte Admin Menu Core is the free edition of the quick-access administrator module for Joomla! 5 and Joomla! 6. Core and Pro deliberately share the same Joomla extension identity: `mod_alamarte_adminmenu` with namespace `Alamarte\Module\AdminMenu`. Installing one edition over the other updates the same module instead of creating a second extension.

## Core 1.4.1

- Displays **Core** as a fixed-colour pill beside **Version: 1.4.1**, with stable contrast in both light and dark administrator themes.
- Uses the validated Joomla-integrated administrator trigger with a dedicated `fas fa-home` action and the native Joomla header pill.
- Provides **Auto**, **Light** and **Dark** theme choices. Auto follows the light/dark mode active in Joomla's administrator; the configuration form explains this relationship and the browser preference behaviour.
- Uses a clean segmented ES/EN administrator-language selector.
- Uses the fixed **Joomla! Administrator** visual preset in Core. The Styles tab explains this behaviour and identifies the additional Pro visual capabilities without repeating Pro labels across every hidden colour control.
- Provides **Compact**, **Standard** and **Wide** panel widths with contextual explanations. Selecting **Custom** only displays the Pro information notice; Core does not persist or apply a custom width.
- Keeps Pro-only values preserved for a later Core → Pro round trip.

Core keeps the main menu features of Pro:

- configurable Joomla! shortcuts for Content, Administration, Extensions and Joomla Update;
- Joomla ACL filtering and administrator-destination validation;
- Auto, Light and Dark theme selector, including the visible Theme-title switch;
- temporary administrator-language selector for two installed backend languages;
- fixed Joomla-based Core visual preset with light/dark appearances;
- Compact, Standard and Wide responsive panel widths;
- configurable section-heading visibility;
- responsive columns, keyboard support and bilingual English/Spanish interface;
- predefined or validated manual administrator destinations for custom links.

The explicit Core limits are:

- up to **3 custom links** are displayed and used; Pro supports up to 50;
- custom links use the standard link icon; the advanced Font Awesome picker is identified as Pro-only;
- custom links open in the current administrator window; the target selector is identified as Pro-only;
- the administrator trigger uses the standard `Admin Menu` / `Menú Admin` label; the custom title is identified as Pro-only;
- Core always applies the Joomla-based visual preset; Pro unlocks the Custom preset and seven colour controls;
- Core applies Compact, Standard or Wide panel widths. Custom width is available in Pro.

## Core ↔ Pro preservation

The technical extension identity is shared intentionally. Installing Core over Pro preserves the existing module instance, title, position, publication state, assignments and saved parameters.

Pro-only menu-title, custom-link icon, target, visual-preset, custom-colour and custom-width values remain in their original stored parameters. Core ignores Pro-only style values at runtime and always renders the Joomla preset. If a Pro configuration contains more than three custom-link rows, Core shows and uses only the first three; remaining rows stay preserved for a later return to Pro.

For panel width, Core uses a separate editable proxy for Compact, Standard and Wide while preserving the shared Pro/Core width parameter. Selecting Custom in Core only displays the Pro notice; before a save, Core restores the last valid Core width instead of persisting Custom.

Installing Pro again replaces the Core files and exposes the Pro controls and saved Pro values again.

## New installation

A fresh installation publishes the administrator module in the `status` position and assigns it to all administrator pages. The default Joomla module title is `Alamarte Admin Menu Core`.

## Compatibility

- Joomla! 5.x
- Joomla! 6.x
- PHP 8.1 or later

## Updates

Core update endpoint:

`https://alamarte.com/updates/modules/adminmenu/core/manifest.xml`

Core changelog endpoint:

`https://alamarte.com/updates/modules/adminmenu/core/changelog.xml`

The legacy public update route `/updates/modules/adminmenu/` remains the migration route for the previously public 1.2.0 line. Core releases use `/updates/modules/adminmenu/core/`.

## License

GNU General Public License version 3 or later.
