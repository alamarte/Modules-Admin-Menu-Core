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
use Joomla\CMS\Language\Text;

final class JoomlalinkresetField extends FormField
{
    protected $type = 'Joomlalinkreset';

    protected function getInput(): string
    {
        $button = htmlspecialchars(
            Text::_('MOD_ALAMARTE_ADMINMENU_RESET_JOOMLA_LINKS_DEFAULTS'),
            ENT_QUOTES,
            'UTF-8'
        );
        $description = htmlspecialchars(
            Text::_('MOD_ALAMARTE_ADMINMENU_RESET_JOOMLA_LINKS_DEFAULTS_DESCRIPTION'),
            ENT_QUOTES,
            'UTF-8'
        );
        $success = htmlspecialchars(
            Text::_('MOD_ALAMARTE_ADMINMENU_RESET_JOOMLA_LINKS_DEFAULTS_APPLIED'),
            ENT_QUOTES,
            'UTF-8'
        );
        $saveWarning = htmlspecialchars(
            Text::_('MOD_ALAMARTE_ADMINMENU_RESET_JOOMLA_LINKS_DEFAULTS_SAVE'),
            ENT_QUOTES,
            'UTF-8'
        );

        return '<div class="alm-adminmenu-reset-control">'
            . '<button type="button" class="btn btn-outline-primary" data-alm-adminmenu-reset-defaults>'
            . '<span class="icon-refresh" aria-hidden="true"></span> '
            . $button
            . '</button>'
            . '<p class="form-text">' . $description . '</p>'
            . '<p class="alert alert-warning mt-2 mb-0" role="status" aria-live="polite" '
            . 'data-alm-adminmenu-reset-status hidden>'
            . '<span class="icon-warning me-1" aria-hidden="true"></span>'
            . $success . ' <strong>' . $saveWarning . '</strong></p>'
            . '</div>';
    }

    public function getLabel()
    {
        return '';
    }
}
