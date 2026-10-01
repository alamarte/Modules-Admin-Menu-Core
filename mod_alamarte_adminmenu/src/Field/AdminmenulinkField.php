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

namespace Alamarte\Module\AdminMenu\Administrator\Field;

defined('_JEXEC') or die;

use Alamarte\Module\AdminMenu\Administrator\Support\AdministratorLinkCatalog;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Throwable;

final class AdminmenulinkField extends ListField
{
    protected $type = 'Adminmenulink';

    /** @var array<string, string> */
    private array $selectorAliases = [];

    protected function getInput(): string
    {
        $groups   = $this->buildGroups();
        $current  = html_entity_decode((string) $this->value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $identity = AdministratorLinkCatalog::urlIdentity($current);

        if ($identity !== '' && isset($this->selectorAliases[$identity])) {
            $current = $this->selectorAliases[$identity];
        }
        $id       = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $name     = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
        $class    = htmlspecialchars(trim('form-select ' . (string) $this->class), ENT_QUOTES, 'UTF-8');
        $disabled = $this->disabled ? ' disabled' : '';
        $required = $this->required ? ' required' : '';
        $html     = '<select id="' . $id . '" name="' . $name . '" class="' . $class . '"' . $disabled . $required . '>';

        $html .= '<option value="">'
            . htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_MENU_ITEM_OPTION'), ENT_QUOTES, 'UTF-8')
            . '</option>';

        foreach ($groups as $groupLabel => $items) {
            $html .= '<optgroup label="' . htmlspecialchars($groupLabel, ENT_QUOTES, 'UTF-8') . '">';

            foreach ($items as $item) {
                $selected = hash_equals($current, $item['value']) ? ' selected' : '';
                $html .= '<option value="' . htmlspecialchars($item['value'], ENT_QUOTES, 'UTF-8') . '"' . $selected . '>'
                    . htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8')
                    . '</option>';
            }

            $html .= '</optgroup>';
        }

        return $html . '</select>';
    }

    /** @return array<string, list<array{value: string, text: string}>> */
    private function buildGroups(): array
    {
        $app                 = Factory::getApplication();
        $identity            = $app->getIdentity();
        $groups              = [];
        $seen                = [];
        $canonicalByIdentity = [];
        $coreEntries         = [];
        $this->selectorAliases = [];

        foreach (AdministratorLinkCatalog::coreLinksForUser($identity) as $definition) {
            $identityKey = AdministratorLinkCatalog::urlIdentity($definition['url']);

            if ($identityKey === '' || isset($seen[$identityKey])) {
                continue;
            }

            $text = Text::_($definition['label']);
            $seen[$identityKey] = true;
            $canonicalByIdentity[$identityKey] = $definition['url'];
            $coreEntries[] = [
                'url'  => $definition['url'],
                'text' => $text,
            ];
            $groups[Text::_($definition['group'])][] = [
                'value' => $definition['url'],
                'text'  => $text,
            ];
        }

        foreach ($this->databaseItems() as $item) {
            $link        = trim((string) ($item->link ?? ''));
            $component   = strtolower((string) ($item->extension_element ?? ''));
            $identityKey = AdministratorLinkCatalog::urlIdentity($link);

            if ($identityKey === ''
                || preg_match('/^com_[a-z0-9_]+$/D', $component) !== 1
                || !AdministratorLinkCatalog::canAccessUrl($identity, $link)
            ) {
                continue;
            }

            if (isset($seen[$identityKey])) {
                $this->selectorAliases[$identityKey] = $canonicalByIdentity[$identityKey];

                continue;
            }

            $owner = AdministratorLinkCatalog::functionalComponent($link, $component);

            if ($owner === '') {
                continue;
            }

            $this->loadComponentLanguage($owner);
            $groupKey   = AdministratorLinkCatalog::groupKeyForComponent($owner);
            $groupLabel = $groupKey !== '' ? Text::_($groupKey) : $this->extensionLabel($item, $owner);
            $contextKey = AdministratorLinkCatalog::contextualLabelKey($link);
            $titleKey   = (string) ($item->title ?? '');
            $title      = $contextKey !== '' ? Text::_($contextKey) : Text::_($titleKey);

            if ($title === $titleKey) {
                $title = $titleKey;
            }

            $title = trim(strip_tags($title));

            if ($title === '') {
                continue;
            }

            $canonicalUrl = $this->matchingCoreUrl($link, $title, $coreEntries);

            if ($canonicalUrl !== '') {
                $this->selectorAliases[$identityKey] = $canonicalUrl;

                continue;
            }

            $seen[$identityKey] = true;
            $canonicalByIdentity[$identityKey] = $link;
            $groups[$groupLabel][] = ['value' => $link, 'text' => $title];
        }

        foreach ($groups as &$items) {
            usort($items, static fn (array $a, array $b): int => strnatcasecmp($a['text'], $b['text']));
        }
        unset($items);

        $ordered = [];

        foreach (AdministratorLinkCatalog::coreGroupKeys() as $key) {
            $label = Text::_($key);

            if (isset($groups[$label])) {
                $ordered[$label] = $groups[$label];
                unset($groups[$label]);
            }
        }

        uksort($groups, 'strnatcasecmp');

        return $ordered + $groups;
    }

    /**
     * @param list<array{url: string, text: string}> $coreEntries
     */
    private function matchingCoreUrl(string $url, string $label, array $coreEntries): string
    {
        foreach ($coreEntries as $entry) {
            if (AdministratorLinkCatalog::areEquivalentSelectorRoutes(
                $url,
                $label,
                $entry['url'],
                $entry['text']
            )) {
                return $entry['url'];
            }
        }

        return '';
    }

    /** @return list<object> */
    private function databaseItems(): array
    {
        try {
            $db            = Factory::getContainer()->get(DatabaseInterface::class);
            $administrator = 1;
            $componentType = 'component';
            $published     = 1;
            $enabled       = 1;
            $query         = $db->getQuery(true)
                ->select([
                    $db->quoteName('m.link'),
                    $db->quoteName('m.title'),
                    $db->quoteName('e.name', 'extension_name'),
                    $db->quoteName('e.element', 'extension_element'),
                ])
                ->from($db->quoteName('#__menu', 'm'))
                ->innerJoin($db->quoteName('#__extensions', 'e') . ' ON ' . $db->quoteName('e.extension_id') . ' = ' . $db->quoteName('m.component_id'))
                ->where($db->quoteName('m.client_id') . ' = :clientId')
                ->where($db->quoteName('m.type') . ' = :menuType')
                ->where($db->quoteName('m.published') . ' = :published')
                ->where($db->quoteName('e.enabled') . ' = :enabled')
                ->where($db->quoteName('e.type') . ' = :extensionType')
                ->bind(':clientId', $administrator, ParameterType::INTEGER)
                ->bind(':menuType', $componentType, ParameterType::STRING)
                ->bind(':published', $published, ParameterType::INTEGER)
                ->bind(':enabled', $enabled, ParameterType::INTEGER)
                ->bind(':extensionType', $componentType, ParameterType::STRING);

            return $db->setQuery($query)->loadObjectList();
        } catch (Throwable) {
            return [];
        }
    }

    private function loadComponentLanguage(string $component): void
    {
        $language = Factory::getApplication()->getLanguage();

        foreach ([JPATH_ADMINISTRATOR, JPATH_ADMINISTRATOR . '/components/' . $component, JPATH_ROOT] as $base) {
            $language->load($component, $base);
            $language->load($component . '.sys', $base);
        }
    }

    private function extensionLabel(object $item, string $fallback): string
    {
        $storedName = trim((string) ($item->extension_name ?? ''));
        $candidates = [];

        if ($storedName !== '' && preg_match('/^[A-Z][A-Z0-9_]+$/D', $storedName) === 1) {
            $candidates[] = $storedName;
        }

        $derivedKey = strtoupper($fallback);

        if (preg_match('/^COM_[A-Z0-9_]+$/D', $derivedKey) === 1 && !in_array($derivedKey, $candidates, true)) {
            $candidates[] = $derivedKey;
        }

        foreach ($candidates as $candidate) {
            $translated = trim(strip_tags(Text::_($candidate)));

            if ($translated !== '' && $translated !== $candidate) {
                return $translated;
            }
        }

        $literalName = trim(strip_tags($storedName));

        if ($literalName !== '' && !$this->isTechnicalExtensionIdentifier($literalName)) {
            return $literalName;
        }

        return $fallback;
    }

    private function isTechnicalExtensionIdentifier(string $value): bool
    {
        return preg_match('/^(?:com|mod|plg|pkg|lib|file|tpl)_[a-z0-9_]+$/Di', $value) === 1
            || preg_match('/^[A-Z][A-Z0-9_]+$/D', $value) === 1;
    }
}
