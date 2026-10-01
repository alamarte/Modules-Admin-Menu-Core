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

use Joomla\Registry\Registry;

final class StyleResolver
{
    private const WIDTHS = ['compact', 'standard', 'wide'];


    public function __construct(private Registry $params)
    {
    }

    /**
     * Core always renders the fixed Joomla-based visual preset. Pro-only
     * colour parameters remain stored by the form but are intentionally
     * ignored here so a Core -> Pro round trip preserves them safely.
     *
     * @return array{
     *     className: string,
     *     preset: string,
     *     customPanelWidth: int|null
     * }
     */
    public function resolve(): array
    {
        $legacyWidth = $this->enum('panel_width_preset', 'standard', self::WIDTHS);
        $width       = $this->enum('core_panel_width_preset', $legacyWidth, self::WIDTHS);

        return [
            'className' => 'alm-adminmenu--preset-joomla alm-adminmenu--width-' . $width,
            'preset' => 'joomla',
            'customPanelWidth' => null,
        ];
    }

    /**
     * @param list<string> $allowed
     */
    private function enum(string $parameter, string $default, array $allowed): string
    {
        $value = strtolower(trim((string) $this->params->get($parameter, $default)));

        return in_array($value, $allowed, true) ? $value : $default;
    }

}
