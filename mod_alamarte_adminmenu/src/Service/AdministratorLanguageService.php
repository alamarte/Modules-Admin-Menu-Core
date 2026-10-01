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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\Registry\Registry;
use Throwable;

final class AdministratorLanguageService
{
    private const LANGUAGE_PARAMETERS = [
        'backend_language_1' => 'es-ES',
        'backend_language_2' => 'en-GB',
    ];

    private const SWITCH_TITLE_KEY = 'MOD_ALAMARTE_ADMINMENU_BACKEND_LANGUAGE_SWITCH_TO';
    private const FALLBACK_LANGUAGE = 'en-GB';

    /**
     * @return array{
     *     enabled: bool,
     *     showTitle: bool,
     *     title: string,
     *     titleIsKey: bool,
     *     current: string,
     *     languages: list<array{tag: string, nativeName: string, name: string, shortCode: string, switchTitle: string}>
     * }
     */
    public function build(Registry $params, string $currentTag): array
    {
        $title = $this->cleanLabel((string) $params->get('backend_language_title', ''), 40);
        $languages = $this->configuredLanguages($params);

        foreach ($languages as &$language) {
            $language['switchTitle'] = $this->buildSwitchTitle(
                $language['tag'],
                $language['nativeName']
            );
        }

        unset($language);

        return [
            'enabled'    => (bool) $params->get('enable_backend_language_switcher', 1)
                && count($languages) === 2,
            'showTitle'  => (bool) $params->get('show_backend_language_title', 1),
            'title'      => $title !== ''
                ? $title
                : 'MOD_ALAMARTE_ADMINMENU_BACKEND_LANGUAGE_TITLE_DEFAULT',
            'titleIsKey' => $title === '',
            'current'    => $this->isLanguageTag($currentTag) ? $currentTag : '',
            'languages'  => $languages,
        ];
    }

    public function isConfiguredLanguage(Registry $params, string $tag): bool
    {
        if (!(bool) $params->get('enable_backend_language_switcher', 1) || !$this->isLanguageTag($tag)) {
            return false;
        }

        $languages = $this->configuredLanguages($params);

        if (count($languages) !== 2) {
            return false;
        }

        foreach ($languages as $language) {
            if (hash_equals($language['tag'], $tag)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array{tag: string, nativeName: string, name: string, shortCode: string}>
     */
    private function configuredLanguages(Registry $params): array
    {
        $installed = $this->installedLanguages();
        $configured = [];
        $seen = [];

        foreach (self::LANGUAGE_PARAMETERS as $parameter => $default) {
            $tag = trim((string) $params->get($parameter, $default));

            if (!$this->isLanguageTag($tag) || !isset($installed[$tag]) || isset($seen[$tag])) {
                continue;
            }

            $configured[] = $installed[$tag];
            $seen[$tag] = true;
        }

        return $configured;
    }

    /**
     * @return array<string, array{tag: string, nativeName: string, name: string, shortCode: string}>
     */
    private function installedLanguages(): array
    {
        try {
            $installed = LanguageHelper::getInstalledLanguages(1, true, false, 'element');
        } catch (Throwable) {
            return [];
        }

        $languages = [];

        foreach ($installed as $tag => $language) {
            $tag = (string) $tag;

            if (!$this->isLanguageTag($tag)) {
                continue;
            }

            $metadata = is_array($language->metadata ?? null) ? $language->metadata : [];
            $name = $this->cleanLabel((string) ($metadata['name'] ?? $language->name ?? $tag), 80);
            $nativeName = $this->resolveNativeName($tag, $metadata, $name);
            $primary = explode('-', $tag, 2)[0];

            $languages[$tag] = [
                'tag'        => $tag,
                'nativeName' => $nativeName !== '' ? $nativeName : $tag,
                'name'       => $name !== '' ? $name : $tag,
                'shortCode'  => strtoupper($primary),
            ];
        }

        return $languages;
    }

    /**
     * Resolve the language's own name. Joomla language packs should provide
     * metadata.nativeName; PHP Intl supplies a generic fallback when they do not.
     *
     * @param array<string, mixed> $metadata
     */
    private function resolveNativeName(string $tag, array $metadata, string $fallbackName): string
    {
        $metadataNativeName = $this->stripLanguageTagSuffix(
            $this->cleanLabel((string) ($metadata['nativeName'] ?? ''), 80),
            $tag
        );

        $fallbackName = $this->stripLanguageTagSuffix($fallbackName, $tag);

        if ($metadataNativeName !== ''
            && ($fallbackName === '' || strcasecmp($metadataNativeName, $fallbackName) !== 0)
        ) {
            return $metadataNativeName;
        }

        $intlNativeName = $this->nativeNameFromIntl($tag);

        if ($intlNativeName !== '') {
            return $intlNativeName;
        }

        if ($metadataNativeName !== '') {
            return $metadataNativeName;
        }

        return $fallbackName !== '' ? $fallbackName : $tag;
    }

    private function buildSwitchTitle(string $tag, string $nativeName): string
    {
        $template = $this->translatedString($tag, self::SWITCH_TITLE_KEY);

        if ($template === '' && !hash_equals(self::FALLBACK_LANGUAGE, $tag)) {
            $template = $this->translatedString(self::FALLBACK_LANGUAGE, self::SWITCH_TITLE_KEY);
        }

        if ($template === '') {
            $template = 'Change the administrator language to %s (%s)';
        }

        try {
            return sprintf($template, $nativeName, $tag);
        } catch (\ValueError) {
            return sprintf('Change the administrator language to %s (%s)', $nativeName, $tag);
        }
    }

    private function translatedString(string $tag, string $key): string
    {
        try {
            $languageFactory = Factory::getContainer()->get(LanguageFactoryInterface::class);
            $language = $languageFactory->createLanguage($tag, false);
            $loaded = $language->load(
                'mod_alamarte_adminmenu',
                JPATH_ADMINISTRATOR,
                $tag,
                true,
                false
            );

            if (!$loaded) {
                $loaded = $language->load(
                    'mod_alamarte_adminmenu',
                    JPATH_ADMINISTRATOR . '/modules/mod_alamarte_adminmenu',
                    $tag,
                    true,
                    false
                );
            }

            if (!$loaded || !$language->hasKey($key)) {
                return '';
            }

            return $this->cleanLabel($language->_($key), 160);
        } catch (Throwable) {
            return '';
        }
    }

    private function nativeNameFromIntl(string $tag): string
    {
        if (!class_exists('Locale') || !method_exists('Locale', 'getDisplayLanguage')) {
            return '';
        }

        try {
            $primary = explode('-', $tag, 2)[0];
            $nativeName = \Locale::getDisplayLanguage($primary, str_replace('-', '_', $tag));
        } catch (Throwable) {
            return '';
        }

        if (!is_string($nativeName)) {
            return '';
        }

        $nativeName = $this->cleanLabel($nativeName, 80);

        return $this->upperFirst($nativeName);
    }

    private function stripLanguageTagSuffix(string $value, string $tag): string
    {
        $value = trim($value);
        $suffix = '(' . $tag . ')';
        $suffixLength = strlen($suffix);

        if ($value !== ''
            && strlen($value) >= $suffixLength
            && strcasecmp(substr($value, -$suffixLength), $suffix) === 0
        ) {
            $value = rtrim(substr($value, 0, -$suffixLength));
        }

        return $value;
    }

    private function upperFirst(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
            $first = mb_substr($value, 0, 1, 'UTF-8');
            $rest = mb_substr($value, 1, null, 'UTF-8');

            return mb_strtoupper($first, 'UTF-8') . $rest;
        }

        return strtoupper(substr($value, 0, 1)) . substr($value, 1);
    }

    private function isLanguageTag(string $tag): bool
    {
        return preg_match('/^[a-z]{2,3}-[A-Z]{2}$/D', $tag) === 1;
    }

    private function cleanLabel(string $value, int $maximumLength): string
    {
        $value = trim(strip_tags($value));
        $clean = '';
        $controlRun = false;
        $length = strlen($value);

        for ($index = 0; $index < $length; $index++) {
            $character = $value[$index];
            $code = ord($character);
            $isControl = $code <= 31 || $code === 127;

            if ($isControl) {
                if (!$controlRun) {
                    $clean .= ' ';
                }

                $controlRun = true;
                continue;
            }

            $clean .= $character;
            $controlRun = false;
        }

        $clean = preg_replace('/\s{2,}/u', ' ', $clean) ?? '';

        if (function_exists('mb_substr')) {
            return mb_substr($clean, 0, $maximumLength, 'UTF-8');
        }

        return substr($clean, 0, $maximumLength);
    }
}
