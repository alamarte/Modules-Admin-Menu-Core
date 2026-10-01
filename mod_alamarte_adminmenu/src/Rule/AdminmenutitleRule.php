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

final class AdminmenutitleRule extends FormRule
{
    public function test(
        \SimpleXMLElement $element,
        $value,
        $group = null,
        ?Registry $input = null,
        ?Form $form = null
    ) {
        if ($input !== null && in_array($input->get('enabled', 1), [0, '0', false, 'false', 'off', 'no'], true)) {
            return true;
        }

        if (!is_scalar($value) || trim(strip_tags((string) $value)) === '') {
            $message = Text::_('MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_TITLE_INVALID');

            if (isset($element['message'])) {
                $element['message'] = $message;
            } else {
                $element->addAttribute('message', $message);
            }

            return false;
        }

        return true;
    }
}
