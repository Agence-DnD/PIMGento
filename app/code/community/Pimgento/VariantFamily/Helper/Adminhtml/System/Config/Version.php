<?php

/**
 * Class Pimgento_VariantFamily_Helper_Adminhtml_System_Config_Version
 *
 * @category  Class
 * @package   Pimgento_VariantFamily_Helper_Adminhtml_System_Config_Version
 * @author    Pierre Barbarin <pierre.barbarin@dnd.fr>
 * @copyright Copyright (c) 2018 Agence Dn'D
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.dnd.fr/
 */
class Pimgento_VariantFamily_Helper_Adminhtml_System_Config_Version extends Mage_Core_Helper_Abstract
{

    /**
     * Get module version number
     *
     * @param null $moduleName
     *
     * @return bool|string
     */
    public function getModuleVersion($moduleName = null)
    {
        if ($moduleName === null) {
            /** @var string $moduleName */
            $moduleName = $this->_getModuleName();
        }

        if (!Mage::getConfig()->getNode('modules/' . $moduleName)) {
            return false;
        }

        return (string)Mage::getConfig()->getModuleConfig($moduleName)->version;
    }
}
