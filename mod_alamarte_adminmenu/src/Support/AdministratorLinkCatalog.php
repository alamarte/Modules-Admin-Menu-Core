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

namespace Alamarte\Module\AdminMenu\Administrator\Support;

defined('_JEXEC') or die;

use Alamarte\Module\AdminMenu\Administrator\Service\CustomUrlValidator;
use Joomla\CMS\User\User;

/**
 * Independent allowlist and grouping for Joomla administrator destinations.
 *
 * The definitions reproduce the standard Alamarte Admin Menu selector behaviour.
 */
final class AdministratorLinkCatalog
{
    /**
     * @var list<array{url: string, label: string, group: string, asset: string, action: string, super: bool}>
     */
    private const CORE_LINKS = [
    [
        'url' => 'index.php?option=com_cpanel',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_CONTROL_PANEL',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'root.1',
        'action' => 'core.login.admin',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_config',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GLOBAL_CONFIG',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'com_config',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_admin&view=sysinfo',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_SYSTEM_INFORMATION',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'com_config',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_cache',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_CLEAR_CACHE',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'com_cache',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_checkin',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GLOBAL_CHECKIN',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'com_checkin',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_actionlogs&view=actionlogs',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_USER_ACTIONS_LOG',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'com_actionlogs',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_scheduler&view=tasks',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_SCHEDULED_TASKS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'com_scheduler',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_guidedtours&view=tours',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GUIDED_TOURS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'com_guidedtours',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_postinstall',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_POSTINSTALL_MESSAGES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'com_postinstall',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_messages&view=messages',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_PRIVATE_MESSAGES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'com_messages',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_mails&view=templates',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_MAIL_TEMPLATES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'com_mails',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_redirect&view=links',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_REDIRECTS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'com_redirect',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_joomlaupdate',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_JOOMLA_UPDATE',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'asset' => 'root.1',
        'action' => 'core.admin',
        'super' => true,
    ],
    [
        'url' => 'index.php?option=com_content&view=articles',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_ARTICLES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTENT',
        'asset' => 'com_content',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_content&view=featured',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_FEATURED_ARTICLES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTENT',
        'asset' => 'com_content',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_categories&view=categories&extension=com_content',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_CATEGORIES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTENT',
        'asset' => 'com_content.category',
        'action' => 'core.edit',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_fields&view=fields&context=com_content.article',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_FIELDS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTENT',
        'asset' => 'com_content',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_fields&view=groups&context=com_content.article',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_FIELD_GROUPS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTENT',
        'asset' => 'com_content',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_media',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_MEDIA',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTENT',
        'asset' => 'com_media',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_tags&view=tags',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_TAGS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTENT',
        'asset' => 'com_tags',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_workflow&view=workflows&extension=com_content.article',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_WORKFLOWS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTENT',
        'asset' => 'com_content',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_menus&view=menus',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_MENUS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_MENUS',
        'asset' => 'com_menus',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_menus&view=items',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_MENU_ITEMS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_MENUS',
        'asset' => 'com_menus',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_users&view=users',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_USERS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_USERS',
        'asset' => 'com_users',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_users&view=groups',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_USER_GROUPS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_USERS',
        'asset' => 'com_users',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_users&view=levels',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_ACCESS_LEVELS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_USERS',
        'asset' => 'com_users',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_users&view=notes',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_USER_NOTES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_USERS',
        'asset' => 'com_users',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_categories&view=categories&extension=com_users.notes',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_USER_NOTE_CATEGORIES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_USERS',
        'asset' => 'com_users',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_installer&view=install',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_INSTALL_EXTENSIONS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_installer',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_installer&view=manage',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_MANAGE_EXTENSIONS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_installer',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_installer&view=update',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_UPDATE_EXTENSIONS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_installer',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_installer&view=updatesites',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_UPDATE_SITES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_installer',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_installer&view=discover',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_DISCOVER_EXTENSIONS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_installer',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_installer&view=database',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_DATABASE',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_installer',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_installer&view=warnings',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_WARNINGS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_installer',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_installer&view=languages',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_INSTALL_LANGUAGES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_installer',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_plugins&view=plugins',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_PLUGINS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_plugins',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_modules&view=modules&client_id=0',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_SITE_MODULES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_modules',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_modules&view=modules&client_id=1',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_ADMIN_MODULES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_modules',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_templates&view=styles&client_id=0',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_SITE_TEMPLATES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_templates',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_templates&view=styles&client_id=1',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_ADMIN_TEMPLATES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'asset' => 'com_templates',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_languages&view=installed',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_INSTALLED_LANGUAGES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_LANGUAGES',
        'asset' => 'com_languages',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_languages&view=languages',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_CONTENT_LANGUAGES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_LANGUAGES',
        'asset' => 'com_languages',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_languages&view=overrides',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_LANGUAGE_OVERRIDES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_LANGUAGES',
        'asset' => 'com_languages',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_associations&view=associations',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_MULTILINGUAL_ASSOCIATIONS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_LANGUAGES',
        'asset' => 'com_associations',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_banners&view=banners',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_BANNERS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_BANNERS',
        'asset' => 'com_banners',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_categories&view=categories&extension=com_banners',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_CATEGORIES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_BANNERS',
        'asset' => 'com_banners.category',
        'action' => 'core.edit',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_banners&view=clients',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_BANNER_CLIENTS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_BANNERS',
        'asset' => 'com_banners',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_banners&view=tracks',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_BANNER_TRACKS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_BANNERS',
        'asset' => 'com_banners',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_contact&view=contacts',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_CONTACTS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTACTS',
        'asset' => 'com_contact',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_categories&view=categories&extension=com_contact',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_CATEGORIES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTACTS',
        'asset' => 'com_contact.category',
        'action' => 'core.edit',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_fields&view=fields&context=com_contact.contact',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_FIELDS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTACTS',
        'asset' => 'com_contact',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_fields&view=groups&context=com_contact.contact',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_FIELD_GROUPS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTACTS',
        'asset' => 'com_contact',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_newsfeeds&view=newsfeeds',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_NEWS_FEEDS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_NEWSFEEDS',
        'asset' => 'com_newsfeeds',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_categories&view=categories&extension=com_newsfeeds',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_CATEGORIES',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_NEWSFEEDS',
        'asset' => 'com_newsfeeds.category',
        'action' => 'core.edit',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_finder&view=index',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_SEARCH_INDEX',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_SMART_SEARCH',
        'asset' => 'com_finder',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_finder&view=maps',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_CONTENT_MAPS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_SMART_SEARCH',
        'asset' => 'com_finder',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_finder&view=filters',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_SEARCH_FILTERS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_SMART_SEARCH',
        'asset' => 'com_finder',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_finder&view=searches',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_SEARCH_TERMS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_SMART_SEARCH',
        'asset' => 'com_finder',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_privacy&view=requests',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_PRIVACY_REQUESTS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_PRIVACY',
        'asset' => 'com_privacy',
        'action' => 'core.manage',
        'super' => false,
    ],
    [
        'url' => 'index.php?option=com_privacy&view=consents',
        'label' => 'MOD_ALAMARTE_ADMINMENU_SELECT_PRIVACY_CONSENTS',
        'group' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_PRIVACY',
        'asset' => 'com_privacy',
        'action' => 'core.manage',
        'super' => false,
    ],    ];

    /** @var array<string, string> */
    private const COMPONENT_GROUPS = [
        'com_admin' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_actionlogs' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_cache' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_checkin' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_config' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_cpanel' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_guidedtours' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_joomlaupdate' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_mails' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_messages' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_postinstall' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_redirect' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_scheduler' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_JOOMLA',
        'com_content' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTENT',
        'com_media' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTENT',
        'com_tags' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTENT',
        'com_workflow' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTENT',
        'com_menus' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_MENUS',
        'com_users' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_USERS',
        'com_installer' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'com_modules' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'com_plugins' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'com_templates' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_EXTENSIONS',
        'com_associations' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_LANGUAGES',
        'com_languages' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_LANGUAGES',
        'com_banners' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_BANNERS',
        'com_contact' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_CONTACTS',
        'com_newsfeeds' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_NEWSFEEDS',
        'com_finder' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_SMART_SEARCH',
        'com_privacy' => 'MOD_ALAMARTE_ADMINMENU_SELECT_GROUP_PRIVACY',
    ];

    /**
     * @return list<array{url: string, label: string, group: string, asset: string, action: string, super: bool}>
     */
    public static function coreLinksForUser(?User $user): array
    {
        if (!$user || !empty($user->guest)) {
            return [];
        }

        return array_values(array_filter(
            self::CORE_LINKS,
            static fn (array $definition): bool => self::canAccessDefinition($user, $definition)
        ));
    }

    /** @return list<string> */
    public static function coreGroupKeys(): array
    {
        $groups = [];

        foreach (self::CORE_LINKS as $definition) {
            if (!in_array($definition['group'], $groups, true)) {
                $groups[] = $definition['group'];
            }
        }

        return $groups;
    }

    public static function groupKeyForComponent(string $component): string
    {
        return self::COMPONENT_GROUPS[strtolower(trim($component))] ?? '';
    }

    public static function canonicalCoreUrl(string $url): string
    {
        $identity = self::urlIdentity($url);

        if ($identity === '') {
            return '';
        }

        foreach (self::CORE_LINKS as $definition) {
            if (hash_equals(self::urlIdentity($definition['url']), $identity)) {
                return $definition['url'];
            }
        }

        return '';
    }

    public static function canAccessUrl(?User $user, string $url): bool
    {
        if (!$user || !empty($user->guest)) {
            return false;
        }

        $identity = self::urlIdentity($url);

        if ($identity === '') {
            return false;
        }

        foreach (self::CORE_LINKS as $definition) {
            if (hash_equals(self::urlIdentity($definition['url']), $identity)) {
                return self::canAccessDefinition($user, $definition);
            }
        }

        $vars   = self::queryVariables($url);
        $option = isset($vars['option']) && is_string($vars['option'])
            ? strtolower($vars['option'])
            : '';

        if (preg_match('/^com_[a-z0-9_]+$/D', $option) !== 1) {
            return false;
        }

        if ($option === 'com_joomlaupdate') {
            return (bool) $user->authorise('core.admin', 'root.1');
        }

        if ($option === 'com_categories') {
            $owner = isset($vars['extension']) && is_string($vars['extension'])
                ? strtolower($vars['extension'])
                : '';

            return preg_match('/^com_[a-z0-9_]+$/D', $owner) === 1
                && ($user->authorise('core.admin', 'root.1') || $user->authorise('core.edit', $owner . '.category'));
        }

        if ($option === 'com_fields') {
            $owner = self::contextOwner($vars);

            return $owner !== ''
                && ($user->authorise('core.admin', 'root.1')
                    || ($user->authorise('core.manage', 'com_fields')
                        && $user->authorise('core.manage', $owner)));
        }

        return (bool) ($user->authorise('core.admin', 'root.1') || $user->authorise('core.manage', $option));
    }

    public static function functionalComponent(string $url, string $fallbackComponent): string
    {
        $vars   = self::queryVariables($url);
        $option = isset($vars['option']) && is_string($vars['option'])
            ? strtolower($vars['option'])
            : '';

        if ($option === 'com_categories') {
            $owner = isset($vars['extension']) && is_string($vars['extension'])
                ? strtolower($vars['extension'])
                : '';

            if (preg_match('/^com_[a-z0-9_]+$/D', $owner) === 1) {
                return $owner;
            }
        }

        if ($option === 'com_fields') {
            $owner = self::contextOwner($vars);

            if ($owner !== '') {
                return $owner;
            }
        }

        return preg_match('/^com_[a-z0-9_]+$/D', $fallbackComponent) === 1
            ? $fallbackComponent
            : '';
    }

    public static function contextualLabelKey(string $url): string
    {
        $vars   = self::queryVariables($url);
        $option = isset($vars['option']) && is_string($vars['option'])
            ? strtolower($vars['option'])
            : '';

        if ($option === 'com_categories') {
            return 'MOD_ALAMARTE_ADMINMENU_SELECT_CATEGORIES';
        }

        if ($option === 'com_fields') {
            $view = isset($vars['view']) && is_string($vars['view'])
                ? strtolower($vars['view'])
                : '';

            return $view === 'groups'
                ? 'MOD_ALAMARTE_ADMINMENU_SELECT_FIELD_GROUPS'
                : 'MOD_ALAMARTE_ADMINMENU_SELECT_FIELDS';
        }

        return '';
    }

    public static function urlIdentity(string $url): string
    {
        $normalised = self::urlValidator()->normalise($url);

        if ($normalised === '' || !str_starts_with($normalised, 'index.php')) {
            return '';
        }

        $query = (string) parse_url($normalised, PHP_URL_QUERY);

        if ($query === '') {
            return 'index.php';
        }

        parse_str($query, $vars);

        foreach ($vars as $key => $value) {
            if (!is_string($key) || is_array($value)) {
                return '';
            }
        }

        ksort($vars);

        return 'index.php?' . http_build_query($vars, '', '&', PHP_QUERY_RFC3986);
    }


    /**
     * Determine whether two selector entries represent the same destination
     * because one route only omits Joomla's explicit default view.
     *
     * The comparison is deliberately conservative: labels must match after
     * normalisation, all query parameters other than view must be identical,
     * and exactly one route must contain a non-empty view value. Different
     * explicit views are never merged.
     */
    public static function areEquivalentSelectorRoutes(
        string $leftUrl,
        string $leftLabel,
        string $rightUrl,
        string $rightLabel
    ): bool {
        if (!hash_equals(self::normaliseSelectorLabel($leftLabel), self::normaliseSelectorLabel($rightLabel))) {
            return false;
        }

        $left  = self::selectorQueryVariables($leftUrl);
        $right = self::selectorQueryVariables($rightUrl);

        if ($left === [] || $right === []) {
            return false;
        }

        $leftView  = isset($left['view']) && is_string($left['view']) ? trim($left['view']) : '';
        $rightView = isset($right['view']) && is_string($right['view']) ? trim($right['view']) : '';

        if (($leftView === '') === ($rightView === '')) {
            return false;
        }

        // Remove view only from local comparison copies. The stored and rendered URLs are never modified.
        unset($left['view'], $right['view']);
        ksort($left);
        ksort($right);

        return $left === $right;
    }

    /** @return array<string, string> */
    private static function selectorQueryVariables(string $url): array
    {
        $normalised = self::urlValidator()->normalise($url);

        if ($normalised === '' || !str_starts_with($normalised, 'index.php')) {
            return [];
        }

        $query = (string) parse_url($normalised, PHP_URL_QUERY);

        if ($query === '') {
            return [];
        }

        parse_str($query, $parsed);
        $variables = [];

        foreach ($parsed as $key => $value) {
            if (!is_string($key) || !is_string($value)) {
                return [];
            }

            $variables[strtolower($key)] = $value;
        }

        return $variables;
    }


    private static function urlValidator(): CustomUrlValidator
    {
        static $validator = null;

        if (!$validator instanceof CustomUrlValidator) {
            $validator = new CustomUrlValidator('https://example.invalid/administrator/');
        }

        return $validator;
    }

    private static function normaliseSelectorLabel(string $label): string
    {
        $label = trim(strip_tags(html_entity_decode($label, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $label = preg_replace('/\s+/u', ' ', $label) ?? '';

        return function_exists('mb_strtolower') ? mb_strtolower($label, 'UTF-8') : strtolower($label);
    }

    /**
     * @param array{url: string, label: string, group: string, asset: string, action: string, super: bool} $definition
     */
    private static function canAccessDefinition(User $user, array $definition): bool
    {
        if ($definition['super']) {
            return (bool) $user->authorise('core.admin', 'root.1');
        }

        if ($user->authorise('core.admin', 'root.1')) {
            return true;
        }

        $vars   = self::queryVariables($definition['url']);
        $option = isset($vars['option']) && is_string($vars['option'])
            ? strtolower($vars['option'])
            : '';

        if ($option === 'com_fields') {
            $owner = self::contextOwner($vars);

            return $owner !== ''
                && $user->authorise('core.manage', 'com_fields')
                && $user->authorise('core.manage', $owner);
        }

        return (bool) $user->authorise($definition['action'], $definition['asset']);
    }

    /** @return array<string, mixed> */
    private static function queryVariables(string $url): array
    {
        $query = (string) parse_url(trim(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')), PHP_URL_QUERY);

        if ($query === '') {
            return [];
        }

        parse_str($query, $parsed);
        $vars = [];

        foreach ($parsed as $key => $value) {
            if (is_string($key)) {
                $vars[$key] = $value;
            }
        }

        return $vars;
    }

    /** @param array<string, mixed> $vars */
    private static function contextOwner(array $vars): string
    {
        $context = isset($vars['context']) && is_string($vars['context'])
            ? strtolower($vars['context'])
            : '';

        return preg_match('/^(com_[a-z0-9_]+)(?:\.|$)/D', $context, $matches) === 1
            ? $matches[1]
            : '';
    }
}
