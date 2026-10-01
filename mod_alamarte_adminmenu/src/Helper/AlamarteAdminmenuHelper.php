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

namespace Alamarte\Module\AdminMenu\Administrator\Helper;

defined('_JEXEC') or die;

use Alamarte\Module\AdminMenu\Administrator\Service\AdministratorLanguageService;
use Alamarte\Module\AdminMenu\Administrator\Service\CustomUrlValidator;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use RuntimeException;
use Throwable;

final class AlamarteAdminmenuHelper
{
    private const ELEMENT = 'mod_alamarte_adminmenu';

    private const MAX_INPUT_BYTES = 2048;

    /**
     * Validate one manual custom-link address without writing data.
     *
     * @return array{valid: bool, normalized: string, code: string}
     */
    public function validateAdminUrlAjax(): array
    {
        $application = Factory::getApplication();
        $input       = $application->getInput();
        $identity    = $application->getIdentity();

        if (!$application->isClient('administrator') || !$identity || $identity->guest) {
            throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        if (strtoupper($input->getMethod()) !== 'POST') {
            throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 405);
        }

        $requestedWith = strtolower($input->server->getString('HTTP_X_REQUESTED_WITH'));
        $contentType   = strtolower($input->server->getString('CONTENT_TYPE'));
        $fetchSite     = strtolower($input->server->getString('HTTP_SEC_FETCH_SITE'));

        if ($requestedWith !== 'xmlhttprequest'
            || !str_starts_with($contentType, 'application/x-www-form-urlencoded')
            || ($fetchSite !== '' && !in_array($fetchSite, ['same-origin', 'none'], true))
            || !Session::checkToken('post')
        ) {
            throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $moduleId = $input->post->getInt('module_id');

        if (!$this->canEditModule($moduleId)) {
            throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $source = strtolower($input->post->getCmd('source'));
        $value  = $input->post->get('url', null, 'raw');

        if ($source !== 'manual' || !is_scalar($value)) {
            return $this->invalidResult();
        }

        $url = trim((string) $value);

        if (strlen($url) > self::MAX_INPUT_BYTES) {
            return $this->invalidResult();
        }

        if ($url === '') {
            return [
                'valid'     => true,
                'normalized' => '',
                'code'      => 'empty',
            ];
        }

        $validator  = new CustomUrlValidator();
        $normalised = $validator->normalise($url);

        if ($normalised === '' || !$this->canAccessDestination($validator, $normalised)) {
            return $this->invalidResult();
        }

        return [
            'valid'      => true,
            'normalized' => $normalised,
            'code'       => 'valid',
        ];
    }


    /**
     * Change the administrator language for the current session.
     *
     * @return array{changed: bool, language: string}
     */
    public function switchAdminLanguageAjax(): array
    {
        $application = Factory::getApplication();
        $input = $application->getInput();
        $identity = $application->getIdentity();

        if (!$application->isClient('administrator') || !$identity || $identity->guest) {
            throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        if (strtoupper($input->getMethod()) !== 'POST') {
            throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 405);
        }

        $requestedWith = strtolower($input->server->getString('HTTP_X_REQUESTED_WITH'));
        $contentType = strtolower($input->server->getString('CONTENT_TYPE'));
        $fetchSite = strtolower($input->server->getString('HTTP_SEC_FETCH_SITE'));

        if ($requestedWith !== 'xmlhttprequest'
            || !str_starts_with($contentType, 'application/x-www-form-urlencoded')
            || ($fetchSite !== '' && !in_array($fetchSite, ['same-origin', 'none'], true))
            || !Session::checkToken('post')
        ) {
            throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $moduleId = $input->post->getInt('module_id');
        $languageValue = $input->post->get('language', null, 'raw');

        if (!is_scalar($languageValue)) {
            throw new RuntimeException(Text::_('MOD_ALAMARTE_ADMINMENU_BACKEND_LANGUAGE_SWITCH_ERROR'), 400);
        }

        $language = trim((string) $languageValue);
        $params = $this->languageSwitcherParams($moduleId);
        $service = new AdministratorLanguageService();

        if ($params === null || !$service->isConfiguredLanguage($params, $language)) {
            throw new RuntimeException(Text::_('MOD_ALAMARTE_ADMINMENU_BACKEND_LANGUAGE_SWITCH_ERROR'), 400);
        }

        $application->setUserState('application.lang', $language);

        return [
            'changed'  => true,
            'language' => $language,
        ];
    }

    private function canEditModule(int $moduleId): bool
    {
        $identity = Factory::getApplication()->getIdentity();

        if (!$identity->authorise('core.manage', 'com_modules')) {
            return false;
        }

        if ($moduleId < 1) {
            return $identity->authorise('core.create', 'com_modules')
                || $identity->authorise('core.admin', 'com_modules');
        }

        try {
            $database      = Factory::getContainer()->get(DatabaseInterface::class);
            $administrator = 1;
            $element       = self::ELEMENT;
            $query         = $database->getQuery(true)
                ->select($database->quoteName('id'))
                ->from($database->quoteName('#__modules'))
                ->where($database->quoteName('id') . ' = :moduleId')
                ->where($database->quoteName('module') . ' = :module')
                ->where($database->quoteName('client_id') . ' = :clientId')
                ->bind(':moduleId', $moduleId, ParameterType::INTEGER)
                ->bind(':module', $element, ParameterType::STRING)
                ->bind(':clientId', $administrator, ParameterType::INTEGER);

            if ((int) $database->setQuery($query, 0, 1)->loadResult() !== $moduleId) {
                return false;
            }
        } catch (Throwable) {
            return false;
        }

        return $identity->authorise('core.edit', 'com_modules.module.' . $moduleId)
            || $identity->authorise('core.edit', 'com_modules')
            || $identity->authorise('core.admin', 'com_modules');
    }

    private function canAccessDestination(CustomUrlValidator $validator, string $url): bool
    {
        if (!$validator->isAdministratorRoute($url)) {
            return false;
        }

        $component = $validator->componentFromUrl($url);

        if ($component === '') {
            return strcasecmp($url, 'index.php') === 0;
        }

        $identity = Factory::getApplication()->getIdentity();

        if ($component === 'com_config') {
            return $identity->authorise('core.admin', 'com_config')
                || $identity->authorise('core.manage', 'com_config');
        }

        return $identity->authorise('core.manage', $component)
            || $identity->authorise('core.admin', $component);
    }


    private function languageSwitcherParams(int $moduleId): ?Registry
    {
        if ($moduleId < 1) {
            return null;
        }

        $identity = Factory::getApplication()->getIdentity();

        try {
            $database = Factory::getContainer()->get(DatabaseInterface::class);
            $administrator = 1;
            $published = 1;
            $element = self::ELEMENT;
            $query = $database->getQuery(true)
                ->select([
                    $database->quoteName('params'),
                    $database->quoteName('access'),
                ])
                ->from($database->quoteName('#__modules'))
                ->where($database->quoteName('id') . ' = :moduleId')
                ->where($database->quoteName('module') . ' = :module')
                ->where($database->quoteName('client_id') . ' = :clientId')
                ->where($database->quoteName('published') . ' = :published')
                ->bind(':moduleId', $moduleId, ParameterType::INTEGER)
                ->bind(':module', $element, ParameterType::STRING)
                ->bind(':clientId', $administrator, ParameterType::INTEGER)
                ->bind(':published', $published, ParameterType::INTEGER);
            $module = $database->setQuery($query, 0, 1)->loadObject();
        } catch (Throwable) {
            return null;
        }

        if (!$module) {
            return null;
        }

        $access = (int) ($module->access ?? 0);
        $levels = array_map('intval', $identity->getAuthorisedViewLevels());

        if (!in_array($access, $levels, true)) {
            return null;
        }

        return new Registry((string) ($module->params ?? ''));
    }

    /**
     * @return array{valid: bool, normalized: string, code: string}
     */
    private function invalidResult(): array
    {
        return [
            'valid'      => false,
            'normalized' => '',
            'code'       => 'invalid',
        ];
    }
}
