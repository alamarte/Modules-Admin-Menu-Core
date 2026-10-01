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

namespace Alamarte\Module\AdminMenu\Administrator\Service;

defined('_JEXEC') or die;

use Alamarte\Module\AdminMenu\Administrator\Support\IconMap;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

final class MenuBuilder
{
    private const CUSTOM_LINK_LIMIT = 3;

    /** @var list<string> */
    private const DEFAULT_ENABLED_PARAMETERS = [
        'show_articles',
        'show_categories',
        'show_tags',
        'show_media',
        'show_menus',
        'show_modules',
        'show_global_config',
        'show_modules_admin',
        'show_plugins',
        'show_users',
        'show_checkin',
        'show_cache',
        'show_ext_manage',
        'show_ext_update',
        'show_ext_install',
        'show_joomla_update',
    ];

    private CustomUrlValidator $urlValidator;

    private AdministratorDestinationValidator $destinationValidator;

    public function __construct(
        private User $identity,
        private Registry $params,
        ?CustomUrlValidator $urlValidator = null,
        ?AdministratorDestinationValidator $destinationValidator = null
    ) {
        $this->urlValidator         = $urlValidator ?? new CustomUrlValidator();
        $this->destinationValidator = $destinationValidator
            ?? new AdministratorDestinationValidator($identity, $this->urlValidator);
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $contentLinks   = $this->filterDefinitions($this->contentDefinitions());
        $adminLinks     = $this->filterDefinitions($this->adminDefinitions());
        $extensionLinks = $this->filterDefinitions($this->extensionDefinitions());
        $customLinks    = $this->buildCustomLinks();

        return [
            'menuTitle' => $this->menuTitle(),
            'custom' => [
                'key'        => 'custom',
                'title'      => $this->customSectionTitle(),
                'titleIsKey' => $this->cleanLabel((string) $this->params->get('custom_links_title', ''), 80) === '',
                'showTitle'  => (bool) $this->params->get('show_custom_links_title', 1),
                'links'      => $customLinks,
            ],
            'content' => [
                'key'        => 'content',
                'title'      => 'MOD_ALAMARTE_ADMINMENU_CONTENT_MANAGEMENT',
                'titleIsKey' => true,
                'showTitle'  => (bool) $this->params->get('show_content_title', 1),
                'links'      => $contentLinks,
            ],
            'admin' => [
                'key'        => 'admin',
                'title'      => 'MOD_ALAMARTE_ADMINMENU_ADMINISTRATION',
                'titleIsKey' => true,
                'showTitle'  => (bool) $this->params->get('show_admin_title', 1),
                'links'      => $adminLinks,
            ],
            'extensions' => [
                'title'      => 'MOD_ALAMARTE_ADMINMENU_JOOMLA_EXTENSIONS',
                'titleIsKey' => true,
                'showTitle'  => (bool) $this->params->get('show_extensions_title', 1),
                'links'      => $extensionLinks,
            ],
            'theme' => [
                'enabled'    => (bool) $this->params->get('enable_theme_switcher', 1),
                'showTitle'  => (bool) $this->params->get('show_theme_title', 1),
                'default'    => $this->normaliseTheme((string) $this->params->get('default_theme', 'auto')),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function contentDefinitions(): array
    {
        return [
            $this->definition('show_articles', 'MOD_ALAMARTE_ADMINMENU_ARTICLES', 'file-lines', 'index.php?option=com_content&view=articles', [['core.manage', 'com_content']]),
            $this->definition('show_new_article', 'MOD_ALAMARTE_ADMINMENU_NEW_ARTICLE', 'file-circle-plus', 'index.php?option=com_content&task=article.add', [['core.create', 'com_content']]),
            $this->definition('show_featured', 'MOD_ALAMARTE_ADMINMENU_FEATURED_ARTICLES', 'star', 'index.php?option=com_content&view=featured', [['core.manage', 'com_content']]),
            $this->definition('show_categories', 'MOD_ALAMARTE_ADMINMENU_CATEGORIES', 'folder', 'index.php?option=com_categories&view=categories&extension=com_content', [['core.manage', 'com_categories'], ['core.edit', 'com_content.category']]),
            $this->definition('show_fields', 'MOD_ALAMARTE_ADMINMENU_FIELDS', 'table-list', 'index.php?option=com_fields&view=fields&context=com_content.article', [['core.manage', 'com_fields'], ['core.manage', 'com_content']]),
            $this->definition('show_field_groups', 'MOD_ALAMARTE_ADMINMENU_FIELD_GROUPS', 'layer-group', 'index.php?option=com_fields&view=groups&context=com_content.article', [['core.manage', 'com_fields'], ['core.manage', 'com_content']], (bool) $this->params->get('show_field_groups', 0)),
            $this->definition('show_workflows', 'MOD_ALAMARTE_ADMINMENU_WORKFLOWS', 'diagram-project', 'index.php?option=com_workflow&view=workflows&extension=com_content.article', [['core.manage', 'com_content']], (bool) $this->params->get('show_workflows', 0)),
            $this->definition('show_tags', 'MOD_ALAMARTE_ADMINMENU_TAGS', 'tags', 'index.php?option=com_tags&view=tags', [['core.manage', 'com_tags']]),
            $this->definition('show_media', 'MOD_ALAMARTE_ADMINMENU_MEDIA', 'images', 'index.php?option=com_media', [['core.manage', 'com_media']]),
            $this->definition('show_menus', 'MOD_ALAMARTE_ADMINMENU_MENUS', 'bars', 'index.php?option=com_menus&view=menus', [['core.manage', 'com_menus']]),
            $this->definition('show_menu_items', 'MOD_ALAMARTE_ADMINMENU_MENU_ITEMS', 'list', 'index.php?option=com_menus&view=items', [['core.manage', 'com_menus']]),
            $this->definition('show_modules', 'MOD_ALAMARTE_ADMINMENU_MODULES_SITE', 'cube', 'index.php?option=com_modules&view=modules&client_id=0', [['core.manage', 'com_modules']]),
            $this->definition('show_contacts', 'MOD_ALAMARTE_ADMINMENU_CONTACTS', 'address-book', 'index.php?option=com_contact&view=contacts', [['core.manage', 'com_contact']], (bool) $this->params->get('show_contacts', 0)),
            $this->definition('show_contact_categories', 'MOD_ALAMARTE_ADMINMENU_CONTACT_CATEGORIES', 'folder-tree', 'index.php?option=com_categories&view=categories&extension=com_contact', [['core.manage', 'com_contact'], ['core.edit', 'com_contact.category']], (bool) $this->params->get('show_contact_categories', 0)),
            $this->definition('show_contact_fields', 'MOD_ALAMARTE_ADMINMENU_CONTACT_FIELDS', 'table-list', 'index.php?option=com_fields&view=fields&context=com_contact.contact', [['core.manage', 'com_fields'], ['core.manage', 'com_contact']], (bool) $this->params->get('show_contact_fields', 0)),
            $this->definition('show_contact_field_groups', 'MOD_ALAMARTE_ADMINMENU_CONTACT_FIELD_GROUPS', 'layer-group', 'index.php?option=com_fields&view=groups&context=com_contact.contact', [['core.manage', 'com_fields'], ['core.manage', 'com_contact']], (bool) $this->params->get('show_contact_field_groups', 0)),
            $this->definition('show_newsfeeds', 'MOD_ALAMARTE_ADMINMENU_NEWS_FEEDS', 'rss', 'index.php?option=com_newsfeeds&view=newsfeeds', [['core.manage', 'com_newsfeeds']], (bool) $this->params->get('show_newsfeeds', 0)),
            $this->definition('show_newsfeed_categories', 'MOD_ALAMARTE_ADMINMENU_NEWS_FEED_CATEGORIES', 'folder-tree', 'index.php?option=com_categories&view=categories&extension=com_newsfeeds', [['core.manage', 'com_newsfeeds'], ['core.edit', 'com_newsfeeds.category']], (bool) $this->params->get('show_newsfeed_categories', 0)),
            $this->definition('show_search_index', 'MOD_ALAMARTE_ADMINMENU_SEARCH_INDEX', 'magnifying-glass', 'index.php?option=com_finder&view=index', [['core.manage', 'com_finder']], (bool) $this->params->get('show_search_index', 0)),
            $this->definition('show_content_maps', 'MOD_ALAMARTE_ADMINMENU_CONTENT_MAPS', 'sitemap', 'index.php?option=com_finder&view=maps', [['core.manage', 'com_finder']], (bool) $this->params->get('show_content_maps', 0)),
            $this->definition('show_search_filters', 'MOD_ALAMARTE_ADMINMENU_SEARCH_FILTERS', 'filter', 'index.php?option=com_finder&view=filters', [['core.manage', 'com_finder']], (bool) $this->params->get('show_search_filters', 0)),
            $this->definition('show_search_terms', 'MOD_ALAMARTE_ADMINMENU_SEARCH_TERMS', 'list-ul', 'index.php?option=com_finder&view=searches', [['core.manage', 'com_finder']], (bool) $this->params->get('show_search_terms', 0)),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function adminDefinitions(): array
    {
        return [
            $this->definition('show_global_config', 'MOD_ALAMARTE_ADMINMENU_GLOBAL_CONFIGURATION', 'gear', 'index.php?option=com_config', [['core.admin', 'com_config'], ['core.manage', 'com_config']]),
            $this->definition('show_system_info', 'MOD_ALAMARTE_ADMINMENU_SYSTEM_INFORMATION', 'circle-info', 'index.php?option=com_admin&view=sysinfo', [['core.manage', 'com_config'], ['core.admin', 'com_config']], (bool) $this->params->get('show_system_info', 0)),
            $this->definition('show_user_actions_log', 'MOD_ALAMARTE_ADMINMENU_USER_ACTIONS_LOG', 'clipboard-list', 'index.php?option=com_actionlogs&view=actionlogs', [['core.manage', 'com_actionlogs']], (bool) $this->params->get('show_user_actions_log', 0)),
            $this->definition('show_scheduled_tasks', 'MOD_ALAMARTE_ADMINMENU_SCHEDULED_TASKS', 'clock-rotate-left', 'index.php?option=com_scheduler&view=tasks', [['core.manage', 'com_scheduler']], (bool) $this->params->get('show_scheduled_tasks', 0)),
            $this->definition('show_guided_tours', 'MOD_ALAMARTE_ADMINMENU_GUIDED_TOURS', 'route', 'index.php?option=com_guidedtours&view=tours', [['core.manage', 'com_guidedtours']], (bool) $this->params->get('show_guided_tours', 0)),
            $this->definition('show_postinstall_messages', 'MOD_ALAMARTE_ADMINMENU_POSTINSTALL_MESSAGES', 'bell', 'index.php?option=com_postinstall', [['core.manage', 'com_postinstall']], (bool) $this->params->get('show_postinstall_messages', 0)),
            $this->definition('show_private_messages', 'MOD_ALAMARTE_ADMINMENU_PRIVATE_MESSAGES', 'envelope', 'index.php?option=com_messages&view=messages', [['core.manage', 'com_messages']], (bool) $this->params->get('show_private_messages', 0)),
            $this->definition('show_mail_templates', 'MOD_ALAMARTE_ADMINMENU_MAIL_TEMPLATES', 'envelope-open-text', 'index.php?option=com_mails&view=templates', [['core.manage', 'com_mails']], (bool) $this->params->get('show_mail_templates', 0)),
            $this->definition('show_redirects', 'MOD_ALAMARTE_ADMINMENU_REDIRECTS', 'right-left', 'index.php?option=com_redirect&view=links', [['core.manage', 'com_redirect']], (bool) $this->params->get('show_redirects', 0)),
            $this->definition('show_checkin', 'MOD_ALAMARTE_ADMINMENU_GLOBAL_CHECKIN', 'check-double', 'index.php?option=com_checkin', [['core.manage', 'com_checkin']]),
            $this->definition('show_cache', 'MOD_ALAMARTE_ADMINMENU_CLEAR_CACHE', 'broom', 'index.php?option=com_cache', [['core.manage', 'com_cache']]),
            $this->definition('show_users', 'MOD_ALAMARTE_ADMINMENU_USERS', 'users', 'index.php?option=com_users&view=users', [['core.manage', 'com_users']]),
            $this->definition('show_user_groups', 'MOD_ALAMARTE_ADMINMENU_USER_GROUPS', 'user-group', 'index.php?option=com_users&view=groups', [['core.manage', 'com_users']]),
            $this->definition('show_access_levels', 'MOD_ALAMARTE_ADMINMENU_ACCESS_LEVELS', 'user-shield', 'index.php?option=com_users&view=levels', [['core.manage', 'com_users']]),
            $this->definition('show_user_notes', 'MOD_ALAMARTE_ADMINMENU_USER_NOTES', 'note-sticky', 'index.php?option=com_users&view=notes', [['core.manage', 'com_users']], (bool) $this->params->get('show_user_notes', 0)),
            $this->definition('show_user_note_categories', 'MOD_ALAMARTE_ADMINMENU_USER_NOTE_CATEGORIES', 'folder-tree', 'index.php?option=com_categories&view=categories&extension=com_users.notes', [['core.manage', 'com_users']], (bool) $this->params->get('show_user_note_categories', 0)),
            $this->definition('show_privacy_requests', 'MOD_ALAMARTE_ADMINMENU_PRIVACY_REQUESTS', 'user-lock', 'index.php?option=com_privacy&view=requests', [['core.manage', 'com_privacy']], (bool) $this->params->get('show_privacy_requests', 0)),
            $this->definition('show_privacy_consents', 'MOD_ALAMARTE_ADMINMENU_PRIVACY_CONSENTS', 'file-signature', 'index.php?option=com_privacy&view=consents', [['core.manage', 'com_privacy']], (bool) $this->params->get('show_privacy_consents', 0)),
            $this->definition('show_languages', 'MOD_ALAMARTE_ADMINMENU_INSTALLED_LANGUAGES', 'language', 'index.php?option=com_languages&view=installed', [['core.manage', 'com_languages']]),
            $this->definition('show_content_languages', 'MOD_ALAMARTE_ADMINMENU_CONTENT_LANGUAGES', 'globe', 'index.php?option=com_languages&view=languages', [['core.manage', 'com_languages']], (bool) $this->params->get('show_content_languages', 0)),
            $this->definition('show_language_overrides', 'MOD_ALAMARTE_ADMINMENU_LANGUAGE_OVERRIDES', 'pen-to-square', 'index.php?option=com_languages&view=overrides', [['core.manage', 'com_languages']], (bool) $this->params->get('show_language_overrides', 0)),
            $this->definition('show_multilingual_associations', 'MOD_ALAMARTE_ADMINMENU_MULTILINGUAL_ASSOCIATIONS', 'link', 'index.php?option=com_associations&view=associations', [['core.manage', 'com_associations']], (bool) $this->params->get('show_multilingual_associations', 0)),
            $this->definition('show_modules_admin', 'MOD_ALAMARTE_ADMINMENU_MODULES_ADMIN', 'cubes', 'index.php?option=com_modules&view=modules&client_id=1', [['core.manage', 'com_modules']]),
            $this->definition('show_plugins', 'MOD_ALAMARTE_ADMINMENU_PLUGINS', 'plug', 'index.php?option=com_plugins&view=plugins', [['core.manage', 'com_plugins']]),
            $this->definition('show_templates', 'MOD_ALAMARTE_ADMINMENU_SITE_TEMPLATES', 'palette', 'index.php?option=com_templates&view=styles&client_id=0', [['core.manage', 'com_templates']]),
            $this->definition('show_admin_templates', 'MOD_ALAMARTE_ADMINMENU_ADMIN_TEMPLATES', 'brush', 'index.php?option=com_templates&view=styles&client_id=1', [['core.manage', 'com_templates']], (bool) $this->params->get('show_admin_templates', 0)),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function extensionDefinitions(): array
    {
        $joomlaUpdateParameter = (bool) $this->params->get(
            'show_joomla_update',
            (int) $this->params->get('show_ext_joomla_update', 1)
        );

        return [
            $this->definition('show_ext_manage', 'MOD_ALAMARTE_ADMINMENU_MANAGE_EXTENSIONS', 'box-archive', 'index.php?option=com_installer&view=manage', [['core.manage', 'com_installer']]),
            $this->definition('show_ext_update', 'MOD_ALAMARTE_ADMINMENU_UPDATE_EXTENSIONS', 'arrows-rotate', 'index.php?option=com_installer&view=update', [['core.manage', 'com_installer']]),
            $this->definition('show_ext_install', 'MOD_ALAMARTE_ADMINMENU_INSTALL_EXTENSIONS', 'download', 'index.php?option=com_installer&view=install', [['core.manage', 'com_installer']]),
            $this->definition('show_ext_update_sites', 'MOD_ALAMARTE_ADMINMENU_UPDATE_SITES', 'satellite-dish', 'index.php?option=com_installer&view=updatesites', [['core.manage', 'com_installer']], (bool) $this->params->get('show_ext_update_sites', 0)),
            $this->definition('show_ext_discover', 'MOD_ALAMARTE_ADMINMENU_DISCOVER_EXTENSIONS', 'magnifying-glass-plus', 'index.php?option=com_installer&view=discover', [['core.manage', 'com_installer']], (bool) $this->params->get('show_ext_discover', 0)),
            $this->definition('show_ext_database', 'MOD_ALAMARTE_ADMINMENU_DATABASE', 'database', 'index.php?option=com_installer&view=database', [['core.manage', 'com_installer']], (bool) $this->params->get('show_ext_database', 0)),
            $this->definition('show_ext_warnings', 'MOD_ALAMARTE_ADMINMENU_WARNINGS', 'triangle-exclamation', 'index.php?option=com_installer&view=warnings', [['core.manage', 'com_installer']], (bool) $this->params->get('show_ext_warnings', 0)),
            $this->definition('show_ext_install_languages', 'MOD_ALAMARTE_ADMINMENU_INSTALL_LANGUAGES', 'language', 'index.php?option=com_installer&view=languages', [['core.manage', 'com_installer']], (bool) $this->params->get('show_ext_install_languages', 0)),
            $this->definition('show_joomla_update', 'MOD_ALAMARTE_ADMINMENU_JOOMLA_UPDATE', 'upload', 'index.php?option=com_joomlaupdate', [['core.manage', 'com_joomlaupdate'], ['core.admin', 'com_config']], $joomlaUpdateParameter),
        ];
    }

    /**
     * @param list<array{0: string, 1: string}> $acl
     *
     * @return array<string, mixed>
     */
    private function definition(string $parameter, string $label, string $icon, string $url, array $acl, ?bool $enabled = null): array
    {
        return [
            'enabled' => $enabled ?? (bool) $this->params->get(
                $parameter,
                in_array($parameter, self::DEFAULT_ENABLED_PARAMETERS, true) ? 1 : 0
            ),
            'parameter' => $parameter,
            'label'   => $label,
            'labelIsKey' => true,
            'icon'    => $icon,
            'url'     => Route::_($url, false),
            'target'  => '_self',
            'acl'     => $acl,
        ];
    }

    /**
     * @param list<array<string, mixed>> $definitions
     *
     * @return list<array<string, mixed>>
     */
    private function filterDefinitions(array $definitions): array
    {
        return array_values(array_filter(
            $definitions,
            fn (array $definition): bool => (bool) $definition['enabled'] && $this->isAllowed($definition['acl'])
        ));
    }

    /**
     * @param list<array{0: string, 1: string}> $rules
     */
    private function isAllowed(array $rules): bool
    {
        foreach ($rules as [$action, $asset]) {
            if ($this->identity->authorise($action, $asset)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildCustomLinks(): array
    {
        if (!(bool) $this->params->get('enable_custom_links', 0)) {
            return [];
        }

        $items = $this->params->get('custom_links', []);

        if (is_string($items)) {
            $items = json_decode($items, true);
        } elseif ($items instanceof Registry) {
            $items = $items->toArray();
        } elseif (is_object($items)) {
            $items = (array) $items;
        }

        if (!is_array($items)) {
            return [];
        }

        $links = [];

        foreach (array_slice($items, 0, self::CUSTOM_LINK_LIMIT) as $item) {
            if ($item instanceof Registry) {
                $item = $item->toArray();
            } elseif (is_object($item)) {
                $item = (array) $item;
            }

            if (!is_array($item)) {
                continue;
            }

            if (!in_array($item['enabled'] ?? 1, [1, '1', true], true)) {
                continue;
            }

            $title  = $this->cleanLabel((string) ($item['title'] ?? ''), 80);
            $source = $this->normaliseLinkSource($item['source'] ?? '');
            $url    = $this->resolveCustomUrl(
                $source,
                (string) ($item['custom_url'] ?? ''),
                (string) ($item['url'] ?? '')
            );

            if ($title === '' || $url === '' || !$this->canAccessCustomUrl($url)) {
                continue;
            }

            $icon   = IconMap::normalise('link');
            $target = '_self';

            $links[] = [
                'label'  => $title,
                'labelIsKey' => false,
                'icon'   => $icon,
                'url'    => $url,
                'target' => $target,
            ];
        }

        return $links;
    }

    private function resolveCustomUrl(string $source, string $customUrl, string $menuUrl): string
    {
        if ($source === 'predefined') {
            $normalisedMenuUrl = $this->destinationValidator->normaliseAllowed($menuUrl);

            return $normalisedMenuUrl === '' ? '' : Route::_($normalisedMenuUrl, false);
        }

        if ($source !== 'manual') {
            return '';
        }

        $normalisedCustomUrl = $this->urlValidator->normalise($customUrl);

        if ($normalisedCustomUrl === '') {
            return '';
        }

        if (!$this->urlValidator->isAdministratorRoute($normalisedCustomUrl)) {
            return '';
        }

        return Route::_($normalisedCustomUrl, false);
    }

    private function normaliseLinkSource(mixed $source): string
    {
        if (!is_scalar($source)) {
            return '';
        }

        $source = strtolower(trim((string) $source));

        return in_array($source, ['predefined', 'manual'], true) ? $source : '';
    }

    private function canAccessCustomUrl(string $url): bool
    {
        // Route::_() may turn a validated administrator route into a root-relative
        // administrator URL. Normalize that routed value again before applying the
        // administrator-only boundary and component ACL checks.
        $normalisedUrl = $this->urlValidator->normalise($url);

        if ($normalisedUrl === '' || !$this->urlValidator->isAdministratorRoute($normalisedUrl)) {
            return false;
        }

        $option = $this->urlValidator->componentFromUrl($normalisedUrl);

        if ($option === '') {
            return strcasecmp($normalisedUrl, 'index.php') === 0;
        }

        if ($option === 'com_config') {
            return $this->isAllowed([['core.admin', 'com_config'], ['core.manage', 'com_config']]);
        }

        return $this->isAllowed([['core.manage', $option], ['core.admin', $option]]);
    }

    private function customSectionTitle(): string
    {
        $title = $this->cleanLabel((string) $this->params->get('custom_links_title', ''), 80);

        return $title !== '' ? $title : 'MOD_ALAMARTE_ADMINMENU_CUSTOM_LINKS_SECTION';
    }

    /**
     * @return array{value: string, isKey: bool}
     */
    private function menuTitle(): array
    {
        return ['value' => 'MOD_ALAMARTE_ADMINMENU_TRIGGER_LABEL', 'isKey' => true];
    }

    private function cleanLabel(string $value, int $maximumLength): string
    {
        $value = trim(strip_tags($value));
        $value = $this->replaceAsciiControlCharacters($value);
        $value = preg_replace('/\s{2,}/u', ' ', $value) ?? '';

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $maximumLength, 'UTF-8');
        }

        return substr($value, 0, $maximumLength);
    }

    private function replaceAsciiControlCharacters(string $value): string
    {
        $clean        = '';
        $controlRun   = false;
        $length       = strlen($value);

        for ($index = 0; $index < $length; $index++) {
            $character = $value[$index];
            $code      = ord($character);
            $isControl = $code <= 31 || $code === 127;

            if ($isControl) {
                if (!$controlRun) {
                    $clean .= ' ';
                }

                $controlRun = true;
                continue;
            }

            $clean      .= $character;
            $controlRun  = false;
        }

        return $clean;
    }

    private function normaliseTheme(string $theme): string
    {
        return in_array($theme, ['auto', 'light', 'dark'], true) ? $theme : 'auto';
    }

}
