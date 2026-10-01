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

final class IconMap
{
    private const DEFAULT_ICON = 'fa-solid fa-link';

    /** @var array<string, true>|null */
    private static ?array $allowed = null;

    /** @var array<string, string> */
    private const LEGACY = [
        'link' => 'fa-solid fa-link',
        'house' => 'fa-solid fa-house',
        'home' => 'fa-solid fa-house',
        'gauge-high' => 'fa-solid fa-gauge-high',
        'dashboard' => 'fa-solid fa-gauge-high',
        'file-lines' => 'fa-solid fa-file-lines',
        'file-circle-plus' => 'fa-solid fa-file-circle-plus',
        'folder' => 'fa-solid fa-folder',
        'star' => 'fa-solid fa-star',
        'images' => 'fa-solid fa-images',
        'bars' => 'fa-solid fa-bars',
        'list' => 'fa-solid fa-list',
        'tags' => 'fa-solid fa-tags',
        'table-list' => 'fa-solid fa-table-list',
        'cube' => 'fa-solid fa-cube',
        'cubes' => 'fa-solid fa-cubes',
        'gear' => 'fa-solid fa-gear',
        'cog' => 'fa-solid fa-gear',
        'user' => 'fa-solid fa-user',
        'users' => 'fa-solid fa-users',
        'user-group' => 'fa-solid fa-user-group',
        'user-shield' => 'fa-solid fa-user-shield',
        'plug' => 'fa-solid fa-plug',
        'palette' => 'fa-solid fa-palette',
        'language' => 'fa-solid fa-language',
        'check-double' => 'fa-solid fa-check-double',
        'broom' => 'fa-solid fa-broom',
        'puzzle-piece' => 'fa-solid fa-puzzle-piece',
        'joomla' => 'fa-brands fa-joomla',
        'box-archive' => 'fa-solid fa-box-archive',
        'archive' => 'fa-solid fa-box-archive',
        'arrows-rotate' => 'fa-solid fa-arrows-rotate',
        'refresh' => 'fa-solid fa-arrows-rotate',
        'sync' => 'fa-solid fa-arrows-rotate',
        'download' => 'fa-solid fa-download',
        'upload' => 'fa-solid fa-upload',
        'database' => 'fa-solid fa-database',
        'wrench' => 'fa-solid fa-wrench',
        'shield-halved' => 'fa-solid fa-shield-halved',
        'shield' => 'fa-solid fa-shield-halved',
        'globe' => 'fa-solid fa-globe',
        'envelope' => 'fa-solid fa-envelope',
        'calendar' => 'fa-solid fa-calendar',
        'cart-shopping' => 'fa-solid fa-cart-shopping',
        'chart-line' => 'fa-solid fa-chart-line',
        'circle-info' => 'fa-solid fa-circle-info',
        'circle-question' => 'fa-solid fa-circle-question',
        'book' => 'fa-solid fa-book',
        'comments' => 'fa-solid fa-comments',
        'address-book' => 'fa-solid fa-address-book',
        'lock' => 'fa-solid fa-lock',
        'key' => 'fa-solid fa-key',
        'server' => 'fa-solid fa-server',
        'code' => 'fa-solid fa-code',
        'sitemap' => 'fa-solid fa-sitemap',
        'pen-to-square' => 'fa-solid fa-pen-to-square',
        'edit' => 'fa-solid fa-pen-to-square',
        'trash-can' => 'fa-solid fa-trash-can',
        'magnifying-glass' => 'fa-solid fa-magnifying-glass',
        'search' => 'fa-solid fa-magnifying-glass',
        'eye' => 'fa-solid fa-eye',
        'bell' => 'fa-solid fa-bell',
        'bolt' => 'fa-solid fa-bolt',
        'ellipsis-vertical' => 'fa-solid fa-ellipsis-vertical',
        'circle-half-stroke' => 'fa-solid fa-circle-half-stroke',
        'adjust' => 'fa-solid fa-circle-half-stroke',
        'sun' => 'fa-solid fa-sun',
        'moon' => 'fa-solid fa-moon',
        'superpowers' => 'fa-brands fa-superpowers',
    ];

    public static function normalise(string $value): string
    {
        return self::canonical($value) ?? self::DEFAULT_ICON;
    }

    public static function isAllowed(string $value): bool
    {
        return self::canonical($value) !== null;
    }

    public static function cssClass(string $value): string
    {
        $normalised = self::normalise($value);

        return $normalised === 'none' ? 'alm-fa-icon is-none' : 'alm-fa-icon ' . $normalised;
    }

    public static function label(string $value): string
    {
        $normalised = self::normalise($value);

        if ($normalised === 'none') {
            return 'None';
        }

        $parts = explode(' ', $normalised, 2);
        $name  = isset($parts[1]) ? preg_replace('/^fa-/', '', $parts[1]) : $normalised;

        return ucwords(str_replace('-', ' ', (string) $name));
    }

    /** @return list<string> */
    public static function icons(): array
    {
        return FontAwesomeCatalog::all();
    }

    private static function canonical(string $value): ?string
    {
        $value = strtolower(trim(preg_replace('/\s+/', ' ', $value) ?? ''));

        if ($value === 'none') {
            return 'none';
        }

        if (isset(self::LEGACY[$value])) {
            $value = self::LEGACY[$value];
        } else {
            $parts = explode(' ', $value);

            if (count($parts) === 1) {
                if (preg_match('/^[a-z0-9-]+$/D', $value) !== 1) {
                    return null;
                }

                $value = self::LEGACY[$value] ?? 'fa-solid fa-' . $value;
            } elseif (count($parts) === 2) {
                $style = '';
                $name  = '';

                foreach ($parts as $part) {
                    if (in_array($part, ['fa-solid', 'fas', 'fa'], true)) {
                        if ($style !== '') {
                            return null;
                        }

                        $style = 'fa-solid';
                    } elseif (in_array($part, ['fa-regular', 'far'], true)) {
                        if ($style !== '') {
                            return null;
                        }

                        $style = 'fa-regular';
                    } elseif (in_array($part, ['fa-brands', 'fab'], true)) {
                        if ($style !== '') {
                            return null;
                        }

                        $style = 'fa-brands';
                    } elseif (preg_match('/^fa-[a-z0-9-]+$/D', $part) === 1) {
                        if ($name !== '') {
                            return null;
                        }

                        $name = $part;
                    } else {
                        return null;
                    }
                }

                if ($style === '' || $name === '') {
                    return null;
                }

                $value = $style . ' ' . $name;
            } else {
                return null;
            }
        }

        return isset(self::allowed()[$value]) ? $value : null;
    }

    /** @return array<string, true> */
    private static function allowed(): array
    {
        if (self::$allowed === null) {
            self::$allowed = array_fill_keys(FontAwesomeCatalog::all(), true);
        }

        return self::$allowed;
    }
}
