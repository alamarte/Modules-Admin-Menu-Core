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

use Alamarte\Module\AdminMenu\Administrator\Support\AdministratorLinkCatalog;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Throwable;

final class AdministratorDestinationValidator
{
    /** @var array<string, true>|null */
    private ?array $allowedDestinations = null;

    public function __construct(
        private User $identity,
        private ?CustomUrlValidator $urlValidator = null
    ) {
        $this->urlValidator ??= new CustomUrlValidator();
    }

    public function normaliseAllowed(string $value): string
    {
        $normalised = $this->urlValidator->normalise($value);

        if ($normalised === '' || !$this->urlValidator->isAdministratorRoute($normalised)) {
            return '';
        }

        return isset($this->allowedDestinations()[$normalised]) ? $normalised : '';
    }

    /**
     * @return array<string, true>
     */
    private function allowedDestinations(): array
    {
        if ($this->allowedDestinations !== null) {
            return $this->allowedDestinations;
        }

        $this->allowedDestinations = [];

        foreach (AdministratorLinkCatalog::coreLinksForUser($this->identity) as $definition) {
            $normalised = $this->urlValidator->normalise($definition['url']);

            if ($normalised !== '' && $this->urlValidator->isAdministratorRoute($normalised)) {
                $this->allowedDestinations[$normalised] = true;
            }
        }

        try {
            $database      = Factory::getContainer()->get(DatabaseInterface::class);
            $administrator = 1;
            $componentType = 'component';
            $published     = 1;
            $enabled       = 1;
            $emptyLink     = '';
            $query         = $database->getQuery(true)
                ->select([
                    $database->quoteName('m.link'),
                    $database->quoteName('e.element', 'component'),
                ])
                ->from($database->quoteName('#__menu', 'm'))
                ->innerJoin(
                    $database->quoteName('#__extensions', 'e')
                    . ' ON ' . $database->quoteName('e.extension_id') . ' = ' . $database->quoteName('m.component_id')
                )
                ->where($database->quoteName('m.client_id') . ' = :clientId')
                ->where($database->quoteName('m.type') . ' = :menuType')
                ->where($database->quoteName('m.published') . ' = :published')
                ->where($database->quoteName('m.link') . ' <> :emptyLink')
                ->where($database->quoteName('e.enabled') . ' = :enabled')
                ->where($database->quoteName('e.type') . ' = :extensionType')
                ->bind(':clientId', $administrator, ParameterType::INTEGER)
                ->bind(':menuType', $componentType, ParameterType::STRING)
                ->bind(':published', $published, ParameterType::INTEGER)
                ->bind(':emptyLink', $emptyLink, ParameterType::STRING)
                ->bind(':enabled', $enabled, ParameterType::INTEGER)
                ->bind(':extensionType', $componentType, ParameterType::STRING);

            $items = $database->setQuery($query)->loadObjectList();
        } catch (Throwable) {
            return $this->allowedDestinations;
        }

        foreach ($items as $item) {
            $component = strtolower((string) ($item->component ?? ''));

            $link = (string) ($item->link ?? '');

            if (preg_match('/^com_[a-z0-9_]+$/', $component) !== 1
                || !AdministratorLinkCatalog::canAccessUrl($this->identity, $link)
            ) {
                continue;
            }

            $normalised = $this->urlValidator->normalise($link);

            if ($normalised !== '' && $this->urlValidator->isAdministratorRoute($normalised)) {
                $this->allowedDestinations[$normalised] = true;
            }
        }

        return $this->allowedDestinations;
    }
}
