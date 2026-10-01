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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

final class DescriptiontabField extends FormField
{
    protected $type = 'Descriptiontab';

    protected $hidden = true;

    protected function getInput(): string
    {
        $assets = Factory::getApplication()->getDocument()->getWebAssetManager();

        $assets->getRegistry()->addRegistryFile('media/mod_alamarte_adminmenu/joomla.asset.json');
        $assets->useStyle('mod_alamarte_adminmenu.adminform');
        $assets->useScript('mod_alamarte_adminmenu.adminform');

        $application = Factory::getApplication();
        $document    = $application->getDocument();

        foreach ([
            'MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_URL_CHECKING',
            'MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_URL_VALID',
            'MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_URL_INVALID',
            'MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_URL_UNAVAILABLE',
        ] as $languageKey) {
            Text::script($languageKey);
        }

        $document->addScriptOptions('mod_alamarte_adminmenu.ajax', [
            'endpoint'        => 'index.php?option=com_ajax&module=alamarte_adminmenu&method=validateAdminUrl&format=json',
            'token'           => Session::getFormToken(),
            'moduleId'        => $application->getInput()->getInt('id'),
            'debounce'        => 600,
            'timeout'         => 8000,
            'maxResponseSize' => 8192,
            'labels'          => [
                'checking'    => Text::_('MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_URL_CHECKING'),
                'valid'       => Text::_('MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_URL_VALID'),
                'invalid'     => Text::_('MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_URL_INVALID'),
                'unavailable' => Text::_('MOD_ALAMARTE_ADMINMENU_CUSTOM_LINK_URL_UNAVAILABLE'),
            ],
        ]);

        return '';
    }

    public function getLabel()
    {
        return '';
    }
}
