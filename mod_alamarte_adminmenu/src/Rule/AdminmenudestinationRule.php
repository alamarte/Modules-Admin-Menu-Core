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

use Alamarte\Module\AdminMenu\Administrator\Service\AdministratorDestinationValidator;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

final class AdminmenudestinationRule extends FormRule
{
    public function test(
        \SimpleXMLElement $element,
        $value,
        $group = null,
        ?Registry $input = null,
        ?Form $form = null
    ) {
        if (!$this->isRowEnabled($input) || $this->linkSource($input) !== 'predefined') {
            return true;
        }

        if (!is_scalar($value) || trim((string) $value) === '') {
            $this->setMessage($element);

            return false;
        }

        $identity = Factory::getApplication()->getIdentity();

        if ($identity
            && (new AdministratorDestinationValidator($identity))->normaliseAllowed(trim((string) $value)) !== ''
        ) {
            return true;
        }

        $this->setMessage($element);

        return false;
    }

    private function isRowEnabled(?Registry $input): bool
    {
        if ($input === null) {
            return true;
        }

        return !in_array($input->get('enabled', 1), [0, '0', false, 'false', 'off', 'no'], true);
    }

    private function linkSource(?Registry $input): string
    {
        if ($input === null) {
            return 'predefined';
        }

        $value = $input->get('source', 'predefined');

        if (!is_scalar($value)) {
            return '';
        }

        return strtolower(trim((string) $value));
    }

    private function setMessage(\SimpleXMLElement $element): void
    {
        $message = Text::_('MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_MENU_ITEM_INVALID');

        if (isset($element['message'])) {
            $element['message'] = $message;

            return;
        }

        $element->addAttribute('message', $message);
    }
}
