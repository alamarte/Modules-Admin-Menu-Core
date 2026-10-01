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

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

return new class () implements InstallerScriptInterface
{
    private const ELEMENT = 'mod_alamarte_adminmenu';
    private const EDITION = 'core';
    private const EXTENSION_NAME_KEY = 'MOD_ALAMARTE_ADMINMENU_CORE';
    private const DEFAULT_MODULE_TITLE_KEY = 'MOD_ALAMARTE_ADMINMENU_CORE_DEFAULT_MODULE_TITLE';
    private const MINIMUM_JOOMLA = '5.0.0';
    private const MAXIMUM_JOOMLA = '7.0.0';
    private const MINIMUM_PHP = '8.1.0';
    private const PUBLIC_VERSION = '1.4.1';

    public function install(InstallerAdapter $parent): bool
    {
        return true;
    }

    public function update(InstallerAdapter $parent): bool
    {
        return true;
    }

    public function uninstall(InstallerAdapter $parent): bool
    {
        try {
            $this->loadLanguage();
            $key    = 'MOD_ALAMARTE_ADMINMENU_UNINSTALL_SUCCESS_FORMAT';
            $format = Text::_($key);
            $name   = $this->getExtensionName();

            if ($format !== $key) {
                Factory::getApplication()->getLanguage()->set(
                    'COM_INSTALLER_UNINSTALL_SUCCESS',
                    Text::sprintf($key, $name)
                );
            }
        } catch (Throwable) {
            // A localised notice must never prevent uninstallation.
        }

        return true;
    }

    public function preflight(string $type, InstallerAdapter $parent): bool
    {
        if ($type === 'uninstall') {
            return true;
        }

        $this->loadLanguage();
        $application = Factory::getApplication();

        if (version_compare(PHP_VERSION, self::MINIMUM_PHP, '<')) {
            $application->enqueueMessage(
                Text::sprintf('MOD_ALAMARTE_ADMINMENU_INSTALL_ERROR_PHP', $this->getExtensionName(), self::MINIMUM_PHP, PHP_VERSION),
                'error'
            );

            return false;
        }

        if (!defined('JVERSION')
            || version_compare(JVERSION, self::MINIMUM_JOOMLA, '<')
            || version_compare(JVERSION, self::MAXIMUM_JOOMLA, '>=')
        ) {
            $currentVersion = defined('JVERSION') ? JVERSION : Text::_('MOD_ALAMARTE_ADMINMENU_INSTALL_UNKNOWN_VERSION');
            $application->enqueueMessage(
                Text::sprintf('MOD_ALAMARTE_ADMINMENU_INSTALL_ERROR_JOOMLA_RANGE', $this->getExtensionName(), $currentVersion),
                'error'
            );

            return false;
        }

        if ($type !== 'update') {
            return true;
        }

        try {
            $installedVersion = $this->getInstalledVersion();
            $targetVersion    = trim((string) $parent->getManifest()->version);

            if ($installedVersion !== null
                && $targetVersion !== ''
                && version_compare($targetVersion, $installedVersion, '<')
                && !$this->isInternalVersionRenumbering($installedVersion, $targetVersion)
            ) {
                $application->enqueueMessage(
                    Text::sprintf(
                        'MOD_ALAMARTE_ADMINMENU_INSTALL_ERROR_DOWNGRADE',
                        $this->getExtensionName(),
                        $installedVersion,
                        $targetVersion
                    ),
                    'error'
                );

                return false;
            }
        } catch (Throwable) {
            $application->enqueueMessage(
                Text::sprintf('MOD_ALAMARTE_ADMINMENU_INSTALL_ERROR_VERSION_CHECK', $this->getExtensionName()),
                'error'
            );

            return false;
        }

        return true;
    }

    public function postflight(string $type, InstallerAdapter $parent): bool
    {
        if (!in_array($type, ['install', 'update', 'discover-install', 'discover_install'], true)) {
            return true;
        }

        $this->loadLanguage();

        if ($type === 'update') {
            $this->migrateLegacyParameters();
            $this->removeObsoleteFiles();
        } else {
            $this->configureFreshInstallation();
        }

        $this->enqueueInstallerNotice($type);

        return true;
    }

    private function loadLanguage(): void
    {
        $language = Factory::getApplication()->getLanguage();
        $language->load(self::ELEMENT, __DIR__, null, false, true);
        $language->load(self::ELEMENT . '.sys', __DIR__, null, false, true);
        $language->load(self::ELEMENT, JPATH_ADMINISTRATOR, null, false, true);
        $language->load(self::ELEMENT . '.sys', JPATH_ADMINISTRATOR, null, false, true);
    }

    private function getExtensionName(): string
    {
        $name = Text::_(self::EXTENSION_NAME_KEY);

        return $name !== self::EXTENSION_NAME_KEY
            ? $name
            : 'Alamarte Admin Menu ' . ucfirst(self::EDITION);
    }

    private function getDefaultModuleTitle(): string
    {
        $title = Text::_(self::DEFAULT_MODULE_TITLE_KEY);

        return $title !== self::DEFAULT_MODULE_TITLE_KEY ? $title : $this->getExtensionName();
    }

    private function enqueueInstallerNotice(string $type): void
    {
        $module = null;

        try {
            $module = $this->findModule();
        } catch (Throwable) {
            // The notice is always queued; module state is optional data.
        }

        $state = $module === null
            ? 'AVAILABLE'
            : ((int) $module->published === 1 ? 'PUBLISHED' : 'UNPUBLISHED');
        $actionUrl = $module === null
            ? 'index.php?option=com_modules&view=modules&client_id=1'
            : 'index.php?option=com_modules&task=module.edit&id=' . (int) $module->id;
        $safeUrl   = htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8');
        $operation = $type === 'update' ? 'UPDATE' : 'INSTALL';
        $noticeKey = 'MOD_ALAMARTE_ADMINMENU_' . $operation . '_' . $state . '_NOTICE';

        Factory::getApplication()->enqueueMessage(
            Text::sprintf($noticeKey, $safeUrl),
            in_array($state, ['UNPUBLISHED', 'AVAILABLE'], true) ? 'warning' : 'info'
        );
    }

    private function findModule(): ?object
    {
        $database      = Factory::getContainer()->get(DatabaseInterface::class);
        $element       = self::ELEMENT;
        $administrator = 1;
        $query         = $database->getQuery(true)
            ->select([$database->quoteName('id'), $database->quoteName('published')])
            ->from($database->quoteName('#__modules'))
            ->where($database->quoteName('module') . ' = :module')
            ->where($database->quoteName('client_id') . ' = :clientId')
            ->order($database->quoteName('id') . ' DESC')
            ->bind(':module', $element, ParameterType::STRING)
            ->bind(':clientId', $administrator, ParameterType::INTEGER);

        $module = $database->setQuery($query, 0, 1)->loadObject();

        return $module ?: null;
    }

    private function getInstalledVersion(): ?string
    {
        $database      = Factory::getContainer()->get(DatabaseInterface::class);
        $element       = self::ELEMENT;
        $extensionType = 'module';
        $administrator = 1;
        $query         = $database->getQuery(true)
            ->select($database->quoteName('manifest_cache'))
            ->from($database->quoteName('#__extensions'))
            ->where($database->quoteName('element') . ' = :element')
            ->where($database->quoteName('type') . ' = :extensionType')
            ->where($database->quoteName('client_id') . ' = :clientId')
            ->order($database->quoteName('extension_id') . ' DESC')
            ->bind(':element', $element, ParameterType::STRING)
            ->bind(':extensionType', $extensionType, ParameterType::STRING)
            ->bind(':clientId', $administrator, ParameterType::INTEGER);

        $manifestCache = $database->setQuery($query, 0, 1)->loadResult();

        if (!is_string($manifestCache) || $manifestCache === '') {
            return null;
        }

        $manifest = json_decode($manifestCache, true);
        $version  = is_array($manifest) ? trim((string) ($manifest['version'] ?? '')) : '';

        return $version !== '' ? $version : null;
    }

    private function isInternalVersionRenumbering(string $installedVersion, string $targetVersion): bool
    {
        return $targetVersion === self::PUBLIC_VERSION
            && preg_match('/^1\.5(?:\.|$)/', $installedVersion) === 1;
    }

    private function migrateLegacyParameters(): void
    {
        $application = Factory::getApplication();
        $database    = Factory::getContainer()->get(DatabaseInterface::class);
        $started     = false;

        try {
            $database->transactionStart();
            $started       = true;
            $element       = self::ELEMENT;
            $administrator = 1;
            $query         = $database->getQuery(true)
                ->select([$database->quoteName('id'), $database->quoteName('params')])
                ->from($database->quoteName('#__modules'))
                ->where($database->quoteName('module') . ' = :module')
                ->where($database->quoteName('client_id') . ' = :clientId')
                ->bind(':module', $element, ParameterType::STRING)
                ->bind(':clientId', $administrator, ParameterType::INTEGER);

            $records = $database->setQuery($query)->loadObjectList();

            foreach ($records as $record) {
                $params = json_decode((string) $record->params, true);

                if (!is_array($params)) {
                    continue;
                }

                $changed = false;

                if (array_key_exists('show_ext_joomla_update', $params)) {
                    if (!array_key_exists('show_joomla_update', $params)) {
                        $params['show_joomla_update'] = in_array(
                            $params['show_ext_joomla_update'],
                            [1, '1', true],
                            true
                        ) ? 1 : 0;
                    }

                    unset($params['show_ext_joomla_update']);
                    $changed = true;
                }

                if (array_key_exists('custom_links', $params)) {
                    $customLinks = $params['custom_links'];

                    if (is_string($customLinks)) {
                        $decodedLinks = json_decode($customLinks, true);

                        if (is_array($decodedLinks)) {
                            $customLinks = $decodedLinks;
                        }
                    }

                    if (is_array($customLinks)) {
                        foreach ($customLinks as $key => $customLink) {
                            if (!is_array($customLink)) {
                                continue;
                            }

                            [$customLink, $linkChanged] = $this->migrateCustomLinkRow($customLink);

                            if (!$linkChanged) {
                                continue;
                            }

                            $customLinks[$key] = $customLink;
                            $changed           = true;
                        }

                        $params['custom_links'] = $customLinks;
                    }
                }

                if (!$changed) {
                    continue;
                }

                $encoded = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                if (!is_string($encoded)) {
                    continue;
                }

                $moduleId = (int) $record->id;
                $update   = $database->getQuery(true)
                    ->update($database->quoteName('#__modules'))
                    ->set($database->quoteName('params') . ' = :params')
                    ->where($database->quoteName('id') . ' = :moduleId')
                    ->bind(':params', $encoded, ParameterType::STRING)
                    ->bind(':moduleId', $moduleId, ParameterType::INTEGER);

                $database->setQuery($update)->execute();
            }

            $database->transactionCommit();
        } catch (Throwable) {
            if ($started) {
                try {
                    $database->transactionRollback();
                } catch (Throwable) {
                    // The generic warning below is sufficient and exposes no database details.
                }
            }

            $application->enqueueMessage(Text::_('MOD_ALAMARTE_ADMINMENU_INSTALL_WARNING_MIGRATION'), 'warning');
        }
    }


    /**
     * @param array<string, mixed> $customLink
     *
     * @return array{0: array<string, mixed>, 1: bool}
     */
    private function migrateCustomLinkRow(array $customLink): array
    {
        $changed = false;

        if (!array_key_exists('enabled', $customLink)) {
            $customLink['enabled'] = 1;
            $changed               = true;
        }

        $source = is_scalar($customLink['source'] ?? null)
            ? strtolower(trim((string) $customLink['source']))
            : '';

        if (!in_array($source, ['predefined', 'manual'], true)) {
            $menuDestination = is_scalar($customLink['url'] ?? null)
                ? trim((string) $customLink['url'])
                : '';
            $manualDestination = is_scalar($customLink['custom_url'] ?? null)
                ? trim((string) $customLink['custom_url'])
                : '';
            $customLink['source'] = $menuDestination !== ''
                ? 'predefined'
                : ($manualDestination !== '' ? 'manual' : 'predefined');
            $changed = true;
        }

        return [$customLink, $changed];
    }

    private function configureFreshInstallation(): void
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);
        $started  = false;

        try {
            $database->transactionStart();
            $started       = true;
            $element       = self::ELEMENT;
            $administrator = 1;
            $query         = $database->getQuery(true)
                ->select($database->quoteName('id'))
                ->from($database->quoteName('#__modules'))
                ->where($database->quoteName('module') . ' = :module')
                ->where($database->quoteName('client_id') . ' = :clientId')
                ->order($database->quoteName('id') . ' DESC')
                ->bind(':module', $element, ParameterType::STRING)
                ->bind(':clientId', $administrator, ParameterType::INTEGER);

            $moduleId = (int) $database->setQuery($query, 0, 1)->loadResult();

            if ($moduleId < 1) {
                $database->transactionCommit();

                return;
            }

            $title     = $this->getDefaultModuleTitle();
            $position  = 'status';
            $published = 1;
            $update    = $database->getQuery(true)
                ->update($database->quoteName('#__modules'))
                ->set($database->quoteName('title') . ' = :title')
                ->set($database->quoteName('position') . ' = :position')
                ->set($database->quoteName('published') . ' = :published')
                ->where($database->quoteName('id') . ' = :moduleId')
                ->bind(':title', $title, ParameterType::STRING)
                ->bind(':position', $position, ParameterType::STRING)
                ->bind(':published', $published, ParameterType::INTEGER)
                ->bind(':moduleId', $moduleId, ParameterType::INTEGER);

            $database->setQuery($update)->execute();

            $assignmentQuery = $database->getQuery(true)
                ->select('COUNT(*)')
                ->from($database->quoteName('#__modules_menu'))
                ->where($database->quoteName('moduleid') . ' = :moduleId')
                ->bind(':moduleId', $moduleId, ParameterType::INTEGER);

            if ((int) $database->setQuery($assignmentQuery)->loadResult() === 0) {
                $menuId = 0;
                $insert = $database->getQuery(true)
                    ->insert($database->quoteName('#__modules_menu'))
                    ->columns([$database->quoteName('moduleid'), $database->quoteName('menuid')])
                    ->values(':moduleId, :menuId')
                    ->bind(':moduleId', $moduleId, ParameterType::INTEGER)
                    ->bind(':menuId', $menuId, ParameterType::INTEGER);

                $database->setQuery($insert)->execute();
            }

            $database->transactionCommit();
        } catch (Throwable) {
            if ($started) {
                try {
                    $database->transactionRollback();
                } catch (Throwable) {
                    // The generic warning below is sufficient and exposes no database details.
                }
            }

            Factory::getApplication()->enqueueMessage(
                Text::_('MOD_ALAMARTE_ADMINMENU_INSTALL_WARNING_CONFIGURATION'),
                'warning'
            );
        }
    }

    private function removeObsoleteFiles(): void
    {
        $base = JPATH_ADMINISTRATOR . '/modules/' . self::ELEMENT;
        $obsoleteFiles = [
            $base . '/mod_alamarte_adminmenu.php',
            $base . '/helper.php',
            $base . '/fields/adminmenuitem.php',
            $base . '/fields/almtfieldsetend.php',
            $base . '/fields/almtfieldsetstart.php',
            $base . '/fields/fa_free_icons.php',
            $base . '/fields/iconpicker.php',
            $base . '/images/logo.png',
        ];
        $failed = false;

        foreach ($obsoleteFiles as $file) {
            try {
                if (is_file($file) && !File::delete($file)) {
                    $failed = true;
                }
            } catch (Throwable) {
                $failed = true;
            }
        }

        foreach ([$base . '/fields', $base . '/images'] as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            try {
                $files   = Folder::files($directory, '.', false, true);
                $folders = Folder::folders($directory, '.', false, true);

                if ($files === [] && $folders === [] && !Folder::delete($directory)) {
                    $failed = true;
                }
            } catch (Throwable) {
                $failed = true;
            }
        }

        if ($failed) {
            Factory::getApplication()->enqueueMessage(
                Text::_('MOD_ALAMARTE_ADMINMENU_INSTALL_WARNING_LEGACY_FILES'),
                'warning'
            );
        }
    }
};
