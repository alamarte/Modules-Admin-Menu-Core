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

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Language\Text;

final class BackendlanguagetitleField extends TextField
{
    protected $type = 'Backendlanguagetitle';

    protected function getInput(): string
    {
        if (trim((string) $this->value) === '') {
            $this->value = Text::_('MOD_ALAMARTE_ADMINMENU_BACKEND_LANGUAGE_TITLE_DEFAULT');
        }

        return parent::getInput();
    }
}
