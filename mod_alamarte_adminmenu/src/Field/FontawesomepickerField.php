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

use Alamarte\Module\AdminMenu\Administrator\Support\IconMap;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

final class FontawesomepickerField extends FormField
{
    protected $type = 'Fontawesomepicker';

    private static bool $assetsLoaded = false;

    protected function getInput(): string
    {
        $this->loadAssets();

        $value   = IconMap::normalise((string) $this->value);
        $label   = IconMap::label($value);
        $id      = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $name    = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
        $escaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $disabled = $this->disabled ? ' disabled' : '';

        return '<div class="alm-icon-picker" data-alm-icon-picker>'
            . '<input type="hidden" id="' . $id . '" name="' . $name . '" value="' . $escaped . '" data-alm-icon-input' . $disabled . ' />'
            . '<div class="input-group alm-icon-picker__control">'
            . '<span class="btn btn-primary alm-icon-picker__preview-box">'
            . '<span class="alm-icon-picker__preview text-white ' . IconMap::cssClass($value) . ($value === 'none' ? ' is-none' : '')
            . '" aria-hidden="true" data-alm-icon-preview data-icon="' . $escaped . '"></span>'
            . '</span>'
            . '<input class="form-control alm-icon-picker__selected" type="text" value="'
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '" readonly data-alm-icon-selected />'
            . '<button class="btn btn-primary alm-icon-picker__trigger" type="button" data-alm-icon-trigger aria-haspopup="dialog"' . $disabled . '>'
            . htmlspecialchars(Text::_('MOD_ALAMARTE_ADMINMENU_ICON_PICKER_SELECT'), ENT_QUOTES, 'UTF-8')
            . '</button>'
            . '</div>'
            . '</div>';
    }

    private function loadAssets(): void
    {
        if (self::$assetsLoaded) {
            return;
        }

        $document = Factory::getApplication()->getDocument();
        $assets   = $document->getWebAssetManager();

        $assets->getRegistry()->addRegistryFile('media/mod_alamarte_adminmenu/joomla.asset.json');
        $assets->useStyle('mod_alamarte_adminmenu.iconpicker');
        $assets->useScript('mod_alamarte_adminmenu.iconpicker');

        $catalog = [];

        foreach (IconMap::icons() as $value) {
            $catalog[] = [
                'value'   => $value,
                'label'   => IconMap::label($value),
                'aliases' => str_replace(['fa-solid ', 'fa-regular ', 'fa-brands ', 'fa-'], '', $value),
            ];
        }

        $document->addScriptOptions('mod_alamarte_adminmenu.iconPicker', [
            'icons'  => $catalog,
            'labels' => [
                'title'   => Text::_('MOD_ALAMARTE_ADMINMENU_ICON_PICKER_TITLE'),
                'search'  => Text::_('MOD_ALAMARTE_ADMINMENU_ICON_PICKER_SEARCH'),
                'close'   => Text::_('MOD_ALAMARTE_ADMINMENU_ICON_PICKER_CLOSE'),
                'empty'   => Text::_('MOD_ALAMARTE_ADMINMENU_ICON_PICKER_EMPTY'),
            ],
        ]);

        self::$assetsLoaded = true;
    }
}
