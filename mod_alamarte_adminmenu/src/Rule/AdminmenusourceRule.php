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

namespace Alamarte\Module\AdminMenu\Administrator\Rule;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

final class AdminmenusourceRule extends FormRule
{
    public function test(
        \SimpleXMLElement $element,
        $value,
        $group = null,
        ?Registry $input = null,
        ?Form $form = null
    ) {
        if (!is_scalar($value)
            || !in_array(strtolower(trim((string) $value)), ['predefined', 'manual'], true)
        ) {
            $this->setMessage($element);

            return false;
        }

        return true;
    }

    private function setMessage(\SimpleXMLElement $element): void
    {
        $message = Text::_('MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_SOURCE_INVALID');

        if (isset($element['message'])) {
            $element['message'] = $message;

            return;
        }

        $element->addAttribute('message', $message);
    }
}
