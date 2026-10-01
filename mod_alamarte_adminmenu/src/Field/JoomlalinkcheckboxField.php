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

use Joomla\CMS\Form\FormField;

final class JoomlalinkcheckboxField extends FormField
{
    protected $type = 'Joomlalinkcheckbox';

    protected function getInput(): string
    {
        $currentValue = $this->value;

        if ($currentValue === null || $currentValue === '') {
            $currentValue = (string) ($this->element['default'] ?? '0');
        }

        $checked  = in_array($currentValue, [1, '1', true], true) ? ' checked' : '';
        $disabled = $this->disabled || $this->readonly ? ' disabled' : '';
        $required = $this->required ? ' required' : '';
        $name     = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
        $id       = htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
        $class    = htmlspecialchars(trim('form-check-input alm-adminmenu-link-checkbox ' . (string) $this->class), ENT_QUOTES, 'UTF-8');

        return '<div class="form-check alm-adminmenu-link-checkbox-control">'
            . '<input type="hidden" name="' . $name . '" value="0"' . $disabled . '>'
            . '<input type="checkbox" name="' . $name . '" id="' . $id . '" class="' . $class
            . '" value="1"' . $checked . $disabled . $required . '>'
            . '</div>';
    }
}
