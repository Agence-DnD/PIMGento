<?php
/**
 * Class Pimgento_Variant_Helper_Data
 *
 * @category  Class
 * @package   Pimgento_Variant_Helper_Data
 * @author    Pierre Barbarin <pierre.barbarin@dnd.fr>
 * @copyright Copyright (c) 2018 Agence Dn'D
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.dnd.fr/
 */

class Pimgento_Variant_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * @return string
     */
    public function getImportName()
    {
        /** @var bool $variantFamilyEnabled */
        $variantFamilyEnabled = Mage::helper('core')->isModuleEnabled('Pimgento_VariantFamily');

        if ($variantFamilyEnabled) {
            return $this->__('Product Model (Akeneo > 2.0)');
        }

        return $this->__('Variant');
    }
}