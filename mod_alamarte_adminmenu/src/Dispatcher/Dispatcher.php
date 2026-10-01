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

namespace Alamarte\Module\AdminMenu\Administrator\Dispatcher;

defined('_JEXEC') or die;

use Alamarte\Module\AdminMenu\Administrator\Service\AdministratorLanguageService;
use Alamarte\Module\AdminMenu\Administrator\Service\MenuBuilder;
use Alamarte\Module\AdminMenu\Administrator\Service\StyleResolver;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;

final class Dispatcher extends AbstractModuleDispatcher
{
    protected function getLayoutData(): array
    {
        $data     = parent::getLayoutData();
        $identity = $this->getApplication()->getIdentity();

        if (!$identity || $identity->guest) {
            $data['adminMenu'] = null;

            return $data;
        }

        $document = $this->getApplication()->getDocument();
        $assets   = $document->getWebAssetManager();

        $assets->getRegistry()->addRegistryFile('media/mod_alamarte_adminmenu/joomla.asset.json');
        $assets->useStyle('mod_alamarte_adminmenu.menu');
        $assets->useScript('mod_alamarte_adminmenu.styles');
        $assets->useScript('mod_alamarte_adminmenu.menu');
        $assets->useScript('mod_alamarte_adminmenu.language');

        $menuBuilder             = new MenuBuilder($identity, $data['params']);
        $languageService          = new AdministratorLanguageService();
        $styleResolver            = new StyleResolver($data['params']);
        $data['adminMenu']        = $menuBuilder->build();
        $data['adminMenu']['language'] = $languageService->build(
            $data['params'],
            $this->getApplication()->getLanguage()->getTag()
        );
        $data['styleConfig']      = $styleResolver->resolve();

        return $data;
    }
}
