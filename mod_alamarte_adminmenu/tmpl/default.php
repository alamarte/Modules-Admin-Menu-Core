<?php
/**
 * @package     Alamarte Admin Menu Core
 * @subpackage  mod_alamarte_adminmenu
 * @version     1.4.1
 * @copyright   Copyright (C) 2026 Alamarte Ingeniería Web. All rights reserved.
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @link        https://www.gnu.org/licenses/gpl-3.0.html
 */

declare(strict_types=1);

defined('_JEXEC') or die;

use Alamarte\Module\AdminMenu\Administrator\Support\IconMap;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

if ($adminMenu === null) {
    return;
}

$sectionOrder = ['custom', 'content', 'admin'];
$sections     = array_values(array_filter(
    array_map(static fn (string $key): array => $adminMenu[$key], $sectionOrder),
    static fn (array $section): bool => $section['links'] !== []
));

$columnCount = max(1, min(3, count($sections)));
$moduleId    = (int) $module->id;
$toggleId    = 'alm-adminmenu-toggle-' . $moduleId;
$menuId      = 'alm-adminmenu-panel-' . $moduleId;
$defaultTheme = $adminMenu['theme']['default'];
$initialResolvedTheme = $defaultTheme === 'dark' ? 'dark' : 'light';
$dashboardUrl = Route::_('index.php', false);
$moduleVersion = Text::_('MOD_ALAMARTE_ADMINMENU_BUILD_VERSION');
$moduleEdition = Text::_('MOD_ALAMARTE_ADMINMENU_BUILD_EDITION');
$menuTitle = $adminMenu['menuTitle']['isKey']
    ? Text::_($adminMenu['menuTitle']['value'])
    : $adminMenu['menuTitle']['value'];
$openMenuTitle = Text::sprintf('MOD_ALAMARTE_ADMINMENU_OPEN_MENU', $menuTitle);
$languageSwitchEndpoint = Route::_('index.php?option=com_ajax&module=alamarte_adminmenu&method=switchAdminLanguage&format=json', false);
$languageSwitchTitle = $adminMenu['language']['titleIsKey']
    ? Text::_($adminMenu['language']['title'])
    : $adminMenu['language']['title'];
?>
<div
    class="alm-adminmenu header-item-content dropdown <?php echo htmlspecialchars($styleConfig['className'], ENT_QUOTES, 'UTF-8'); ?>"
    data-alm-adminmenu
    data-default-theme="<?php echo htmlspecialchars($defaultTheme, ENT_QUOTES, 'UTF-8'); ?>"
    data-theme-switcher="<?php echo $adminMenu['theme']['enabled'] ? '1' : '0'; ?>"
    data-resolved-theme="<?php echo $initialResolvedTheme; ?>"
    data-columns="<?php echo $columnCount; ?>"
    <?php if ($styleConfig['customPanelWidth'] !== null) : ?>style="--alm-panel-width: <?php echo (int) $styleConfig['customPanelWidth']; ?>px;"<?php endif; ?>
>
    <a
        class="alm-adminmenu__dashboard"
        href="<?php echo htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8'); ?>"
        aria-label="<?php echo htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_DASHBOARD'), ENT_QUOTES, 'UTF-8'); ?>"
        title="<?php echo htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_DASHBOARD'), ENT_QUOTES, 'UTF-8'); ?>"
    >
        <span class="alm-adminmenu__icon alm-fa-icon fas fa-home" aria-hidden="true"></span>
    </a>

    <button
        class="alm-adminmenu__toggle dropdown-toggle"
        id="<?php echo $toggleId; ?>"
        type="button"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false"
        aria-controls="<?php echo $menuId; ?>"
        aria-haspopup="true"
        title="<?php echo htmlspecialchars($openMenuTitle, ENT_QUOTES, 'UTF-8'); ?>"
    >
        <span class="alm-adminmenu__toggle-emblem" aria-hidden="true">
            <span class="alm-adminmenu__toggle-dots"></span>
        </span>
        <span class="alm-adminmenu__toggle-label"><?php echo htmlspecialchars($menuTitle, ENT_QUOTES, 'UTF-8'); ?></span>
    </button>

    <div
        class="alm-adminmenu__panel dropdown-menu"
        id="<?php echo $menuId; ?>"
        aria-labelledby="<?php echo $toggleId; ?>"
    >
        <div class="alm-adminmenu__toolbar">
            <?php if ($adminMenu['theme']['enabled']) : ?>
                <div class="alm-adminmenu__theme" role="group" aria-label="<?php echo htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_THEME_SELECTOR'), ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if ($adminMenu['theme']['showTitle']) : ?>
                        <span class="alm-adminmenu__theme-label"><?php echo htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_THEME_LABEL'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                    <button type="button" data-alm-theme="auto" aria-pressed="false">
                        <span class="alm-adminmenu__icon <?php echo htmlspecialchars(IconMap::cssClass('circle-half-stroke'), ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                        <span><?php echo htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_THEME_AUTO'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                    <button type="button" data-alm-theme="light" aria-pressed="false">
                        <span class="alm-adminmenu__icon <?php echo htmlspecialchars(IconMap::cssClass('sun'), ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                        <span><?php echo htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_THEME_LIGHT'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                    <button type="button" data-alm-theme="dark" aria-pressed="false">
                        <span class="alm-adminmenu__icon <?php echo htmlspecialchars(IconMap::cssClass('moon'), ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                        <span><?php echo htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_THEME_DARK'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </button>
                </div>
            <?php endif; ?>


            <?php if ($adminMenu['language']['enabled']) : ?>
                <div
                    class="alm-adminmenu__language"
                    data-alm-language-switcher
                    data-endpoint="<?php echo htmlspecialchars($languageSwitchEndpoint, ENT_QUOTES, 'UTF-8'); ?>"
                    data-token="<?php echo htmlspecialchars(Session::getFormToken(), ENT_QUOTES, 'UTF-8'); ?>"
                    data-module-id="<?php echo $moduleId; ?>"
                    data-error-message="<?php echo htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_BACKEND_LANGUAGE_SWITCH_ERROR'), ENT_QUOTES, 'UTF-8'); ?>"
                    role="group"
                    aria-label="<?php echo htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_BACKEND_LANGUAGE_SELECTOR'), ENT_QUOTES, 'UTF-8'); ?>"
                >
                    <span class="alm-adminmenu__language-title<?php echo $adminMenu['language']['showTitle'] ? '' : ' visually-hidden'; ?>">
                        <?php echo htmlspecialchars($languageSwitchTitle, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="alm-adminmenu__language-options">
                        <?php foreach ($adminMenu['language']['languages'] as $language) : ?>
                            <?php
                            $isCurrentLanguage = hash_equals($adminMenu['language']['current'], $language['tag']);
                            $switchLanguageTitle = $language['switchTitle'];
                            ?>
                            <button
                                type="button"
                                data-alm-language="<?php echo htmlspecialchars($language['tag'], ENT_QUOTES, 'UTF-8'); ?>"
                                aria-pressed="<?php echo $isCurrentLanguage ? 'true' : 'false'; ?>"
                                title="<?php echo htmlspecialchars($switchLanguageTitle, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo $isCurrentLanguage ? 'disabled' : ''; ?>
                            >
                                <span aria-hidden="true"><?php echo htmlspecialchars($language['shortCode'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="visually-hidden"><?php echo htmlspecialchars($switchLanguageTitle, ENT_QUOTES, 'UTF-8'); ?></span>
                            </button>
                        <?php endforeach; ?>
                    </span>
                    <span class="alm-adminmenu__language-status visually-hidden" aria-live="polite"></span>
                </div>
            <?php endif; ?>

            <span class="alm-adminmenu__version">
                <span class="alm-adminmenu__version-text"><?php echo htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_VERSION'), ENT_QUOTES, 'UTF-8'); ?>: <strong><?php echo htmlspecialchars($moduleVersion, ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <span class="alm-adminmenu__edition-badge alm-adminmenu__edition-badge--core"><?php echo htmlspecialchars($moduleEdition, ENT_QUOTES, 'UTF-8'); ?></span>
            </span>
        </div>

        <?php if ($sections !== []) : ?>
            <div class="alm-adminmenu__grid">
                <?php foreach ($sections as $index => $section) : ?>
                    <?php $headingId = 'alm-adminmenu-heading-' . $moduleId . '-' . $index; ?>
                    <section
                        class="alm-adminmenu__section alm-adminmenu__section--<?php echo htmlspecialchars((string) ($section['key'] ?? 'section'), ENT_QUOTES, 'UTF-8'); ?>"
                        data-alm-section="<?php echo htmlspecialchars((string) ($section['key'] ?? 'section'), ENT_QUOTES, 'UTF-8'); ?>"
                        aria-labelledby="<?php echo $headingId; ?>"
                    >
                        <h3
                            class="alm-adminmenu__heading<?php echo $section['showTitle'] ? '' : ' visually-hidden'; ?>"
                            id="<?php echo $headingId; ?>"
                        >
                            <?php echo htmlspecialchars($section['titleIsKey'] ? Text::_($section['title']) : $section['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </h3>
                        <?php $isScrollable = count($section['links']) > 6; ?>
                        <ul
                            class="alm-adminmenu__links<?php echo $isScrollable ? ' alm-adminmenu__links--scrollable' : ''; ?>"
                            <?php if ($isScrollable) : ?>data-alm-scroll-after="6"<?php endif; ?>
                        >
                            <?php foreach ($section['links'] as $link) : ?>
                                <?php $linkLabel = $link['labelIsKey'] ? Text::_($link['label']) : $link['label']; ?>
                                <li>
                                    <a
                                        class="alm-adminmenu__link"
                                        href="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                        title="<?php echo htmlspecialchars($linkLabel, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php if ($link['target'] === '_blank') : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
                                    >
                                        <span class="alm-adminmenu__link-icon alm-adminmenu__icon <?php echo htmlspecialchars(IconMap::cssClass($link['icon']), ENT_QUOTES, 'UTF-8'); ?><?php echo $link['icon'] === 'none' ? ' is-none' : ''; ?>" aria-hidden="true"></span>
                                        <span class="alm-adminmenu__link-label"><?php echo htmlspecialchars($linkLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if ($link['target'] === '_blank') : ?>
                                            <span class="visually-hidden"><?php echo htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_OPENS_NEW_WINDOW'), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($adminMenu['extensions']['links'] !== []) : ?>
            <?php
            $extensionLinksByParameter = [];

            foreach ($adminMenu['extensions']['links'] as $extensionLink) {
                $parameter = (string) ($extensionLink['parameter'] ?? '');

                if ($parameter !== '') {
                    $extensionLinksByParameter[$parameter] = $extensionLink;
                }
            }

            $extensionRowParameters = [
                'management' => [
                    'show_ext_manage',
                    'show_ext_update',
                    'show_ext_install',
                    'show_joomla_update',
                ],
                'maintenance' => [
                    'show_ext_discover',
                    'show_ext_database',
                    'show_ext_warnings',
                    'show_ext_install_languages',
                    'show_ext_update_sites',
                ],
            ];
            $extensionRows = [];

            foreach ($extensionRowParameters as $rowKey => $parameters) {
                foreach ($parameters as $parameter) {
                    if (isset($extensionLinksByParameter[$parameter])) {
                        $extensionRows[$rowKey][] = $extensionLinksByParameter[$parameter];
                    }
                }
            }
            ?>
            <section class="alm-adminmenu__extensions" aria-labelledby="alm-adminmenu-extensions-<?php echo $moduleId; ?>">
                <h3
                    class="alm-adminmenu__extensions-title<?php echo $adminMenu['extensions']['showTitle'] ? '' : ' visually-hidden'; ?>"
                    id="alm-adminmenu-extensions-<?php echo $moduleId; ?>"
                >
                    <span class="alm-adminmenu__icon <?php echo htmlspecialchars(IconMap::cssClass('puzzle-piece'), ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                    <?php echo htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_JOOMLA_EXTENSIONS'), ENT_QUOTES, 'UTF-8'); ?>
                </h3>
                <div class="alm-adminmenu__extension-actions">
                    <?php foreach ($extensionRows as $rowKey => $rowLinks) : ?>
                        <div class="alm-adminmenu__extension-row alm-adminmenu__extension-row--<?php echo htmlspecialchars($rowKey, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php foreach ($rowLinks as $link) : ?>
                                <?php $linkLabel = Text::_($link['label']); ?>
                                <?php $isJoomlaUpdate = ($link['parameter'] ?? '') === 'show_joomla_update'; ?>
                                <a
                                    class="<?php echo $isJoomlaUpdate ? 'alm-adminmenu__extension-action--joomla-update' : ''; ?>"
                                    href="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>"
                                    title="<?php echo htmlspecialchars($linkLabel, ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    <span class="alm-adminmenu__icon <?php echo htmlspecialchars(IconMap::cssClass($link['icon']), ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></span>
                                    <span class="alm-adminmenu__extension-label"><?php echo htmlspecialchars($linkLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</div>
