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

use Joomla\CMS\Uri\Uri;

final class CustomUrlValidator
{
    private const MAX_LENGTH = 2048;

    private string $origin = '';

    private string $administratorPath = '/administrator';

    public function __construct(?string $administratorBase = null)
    {
        $base  = $administratorBase ?? Uri::base();
        $parts = parse_url($base);

        if (!is_array($parts)) {
            return;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host   = strtolower((string) ($parts['host'] ?? ''));

        if (in_array($scheme, ['http', 'https'], true) && $host !== '') {
            $this->origin = $this->originFromParts($parts);
        }

        $path = '/' . trim((string) ($parts['path'] ?? '/administrator/'), '/');

        if ($path !== '/') {
            $this->administratorPath = rtrim($path, '/');
        }
    }

    public function normalise(string $value): string
    {
        $url = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($url === '') {
            return '';
        }

        if (strlen($url) > self::MAX_LENGTH || preg_match('//u', $url) !== 1) {
            return '';
        }

        if (!$this->hasValidPercentEncoding($url) || preg_match('/%25[0-9a-f]{2}/i', $url) === 1) {
            return '';
        }

        $decoded = $this->decodePercentEncoding($url);

        if (preg_match('//u', $decoded) !== 1
            || $this->containsUnsafeCharacters($url)
            || $this->containsUnsafeCharacters($decoded)
            || $this->containsPathTraversal($decoded)
        ) {
            return '';
        }

        if (str_starts_with($url, '//')) {
            return '';
        }

        if (preg_match('#^(?:\./)?index\.php(?:\?|$)#i', $url) === 1) {
            return $this->normaliseAdministratorRoute(preg_replace('#^\./#', '', $url) ?? $url);
        }

        if (preg_match('#^administrator(?:/index\.php)?(?:\?|$)#i', $url) === 1) {
            $route = preg_replace('#^administrator(?:/index\.php)?#i', 'index.php', $url);

            return is_string($route) ? $this->normaliseAdministratorRoute($route) : '';
        }

        if (str_starts_with($url, '/')) {
            return $this->normaliseRootRelativeUrl($url);
        }

        $url = $this->normaliseSchemeLessSameOriginUrl($url);

        if (preg_match('#^https?://#i', $url) !== 1) {
            return '';
        }

        return $this->normaliseAbsoluteUrl($url);
    }

    public function isAdministratorRoute(string $url): bool
    {
        return preg_match('/^index\.php(?:\?|$)/i', $url) === 1;
    }

    public function componentFromUrl(string $url): string
    {
        $normalised = $this->normalise($url);

        if (!$this->isAdministratorRoute($normalised)) {
            return '';
        }

        $query = parse_url($normalised, PHP_URL_QUERY);

        if (!is_string($query) || $query === '') {
            return '';
        }

        foreach (explode('&', $query) as $pair) {
            [$rawName, $rawValue] = array_pad(explode('=', $pair, 2), 2, '');
            $name                 = strtolower($this->decodePercentEncoding($rawName));

            if ($name === 'option') {
                $option = strtolower($this->decodePercentEncoding($rawValue));

                return preg_match('/^com_[a-z0-9_]+$/', $option) === 1 ? $option : '';
            }
        }

        return '';
    }

    /**
     * Interpret a scheme-less absolute value only when its authority exactly
     * matches the current HTTPS administrator origin. Other hostnames remain
     * invalid and are never upgraded into accepted external destinations.
     */
    private function normaliseSchemeLessSameOriginUrl(string $url): string
    {
        if ($this->origin === '' || !str_starts_with($this->origin, 'https://')) {
            return $url;
        }

        $slashPosition = strpos($url, '/');

        if ($slashPosition === false || $slashPosition < 1) {
            return $url;
        }

        $candidateAuthority = strtolower(substr($url, 0, $slashPosition));
        $expectedAuthority  = strtolower(substr($this->origin, strlen('https://')));

        if ($candidateAuthority === '' || !hash_equals($expectedAuthority, $candidateAuthority)) {
            return $url;
        }

        return 'https://' . $url;
    }

    private function normaliseRootRelativeUrl(string $url): string
    {
        $parts = parse_url($url);

        if (!is_array($parts) || isset($parts['user']) || isset($parts['pass'])) {
            return '';
        }

        $path = rtrim((string) ($parts['path'] ?? ''), '/');

        if ($this->isAdministratorPath($path)) {
            if (isset($parts['fragment'])) {
                return '';
            }

            $route = 'index.php';

            if (isset($parts['query']) && $parts['query'] !== '') {
                $route .= '?' . $parts['query'];
            }

            return $this->normaliseAdministratorRoute($route);
        }

        return '';
    }

    private function normaliseAbsoluteUrl(string $url): string
    {
        $parts = parse_url($url);

        if (!is_array($parts)
            || isset($parts['user'])
            || isset($parts['pass'])
            || !isset($parts['scheme'], $parts['host'])
            || !in_array(strtolower((string) $parts['scheme']), ['http', 'https'], true)
            || filter_var($url, FILTER_VALIDATE_URL) === false
        ) {
            return '';
        }

        $path = rtrim((string) ($parts['path'] ?? ''), '/');

        if (strtolower((string) $parts['scheme']) !== 'https'
            || $this->origin === ''
            || !str_starts_with($this->origin, 'https://')
            || !hash_equals($this->origin, $this->originFromParts($parts))
            || !$this->isAdministratorPath($path)
            || isset($parts['fragment'])
        ) {
            return '';
        }

        $route = 'index.php';

        if (isset($parts['query']) && $parts['query'] !== '') {
            $route .= '?' . $parts['query'];
        }

        return $this->normaliseAdministratorRoute($route);
    }

    private function normaliseAdministratorRoute(string $route): string
    {
        $parts = parse_url($route);

        if (!is_array($parts)
            || strcasecmp((string) ($parts['path'] ?? ''), 'index.php') !== 0
            || isset($parts['scheme'])
            || isset($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['fragment'])
        ) {
            return '';
        }

        $query = isset($parts['query']) ? (string) $parts['query'] : '';

        if (!$this->validateAdministratorQuery($query)) {
            return '';
        }

        return $query === '' ? 'index.php' : 'index.php?' . $query;
    }

    private function validateAdministratorQuery(string $query): bool
    {
        if ($query === '') {
            return true;
        }

        if (str_contains($query, ';')) {
            return false;
        }

        $seen      = [];
        $component = '';

        foreach (explode('&', $query) as $pair) {
            if ($pair === '') {
                return false;
            }

            [$rawName, $rawValue] = array_pad(explode('=', $pair, 2), 2, '');

            if (!$this->hasValidPercentEncoding($rawName) || !$this->hasValidPercentEncoding($rawValue)) {
                return false;
            }

            $name  = $this->decodePercentEncoding($rawName);
            $value = $this->decodePercentEncoding($rawValue);

            if ($name === ''
                || preg_match('/[\[\]]/', $name) === 1
                || $this->containsUnsafeCharacters($name)
                || $this->containsUnsafeCharacters($value)
            ) {
                return false;
            }

            $key = strtolower($name);

            if (isset($seen[$key]) || in_array($key, ['task', 'token', 'return'], true)) {
                return false;
            }

            $seen[$key] = true;

            if ($key === 'option') {
                $component = strtolower($value);

                if (preg_match('/^com_[a-z0-9_]+$/', $component) !== 1) {
                    return false;
                }
            }
        }

        return $component !== '';
    }

    private function isAdministratorPath(string $path): bool
    {
        $path = rtrim($path, '/');

        return strcasecmp($path, $this->administratorPath) === 0
            || strcasecmp($path, $this->administratorPath . '/index.php') === 0;
    }

    /**
     * @param array<string, mixed> $parts
     */
    private function originFromParts(array $parts): string
    {
        $scheme     = strtolower((string) ($parts['scheme'] ?? ''));
        $host       = strtolower((string) ($parts['host'] ?? ''));
        $portNumber = isset($parts['port']) ? (int) $parts['port'] : 0;
        $defaultPort = ($scheme === 'http' && $portNumber === 80)
            || ($scheme === 'https' && $portNumber === 443);
        $port = $portNumber > 0 && !$defaultPort ? ':' . $portNumber : '';

        return $scheme . '://' . $host . $port;
    }

    private function hasValidPercentEncoding(string $value): bool
    {
        return preg_match('/%(?![0-9a-f]{2})/i', $value) !== 1;
    }

    private function decodePercentEncoding(string $value): string
    {
        $decoded = '';
        $length  = strlen($value);

        for ($index = 0; $index < $length; $index++) {
            if ($value[$index] === '%' && $index + 2 < $length) {
                $hexadecimal = substr($value, $index + 1, 2);

                if (preg_match('/^[0-9a-f]{2}$/i', $hexadecimal) === 1) {
                    $decoded .= chr((int) hexdec($hexadecimal));
                    $index += 2;

                    continue;
                }
            }

            $decoded .= $value[$index];
        }

        return $decoded;
    }

    private function containsPathTraversal(string $value): bool
    {
        $parts = parse_url($value);

        if (!is_array($parts)) {
            return true;
        }

        $path     = (string) ($parts['path'] ?? '');
        $segments = explode('/', str_replace('\\', '/', $path));

        foreach ($segments as $index => $segment) {
            if ($segment === '..') {
                return true;
            }

            if ($segment === '.' && !($index === 0 && str_starts_with($path, './index.php'))) {
                return true;
            }
        }

        return false;
    }

    private function containsUnsafeCharacters(string $value): bool
    {
        if (str_contains($value, '\\')) {
            return true;
        }

        $length = strlen($value);

        for ($index = 0; $index < $length; $index++) {
            $code = ord($value[$index]);

            if ($code <= 32 || $code === 127) {
                return true;
            }
        }

        return false;
    }
}
