<?php

/**
 * Class Pimgento_VariantFamily_Helper_Data
 *
 * @category  Class
 * @package   Pimgento_VariantFamily_Helper_Data
 * @author    Pierre Barbarin <pierre.barbarin@dnd.fr>
 * @copyright Copyright (c) 2018 Agence Dn'D
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.dnd.fr/
 */
class Pimgento_VariantFamily_Helper_Data extends Mage_Core_Helper_Abstract
{

    /** @var string CONFIG_PIMGENTO_VARIANT_FAMILY_MAX_AXES_NUMBER */
    const CONFIG_PIMGENTO_VARIANT_FAMILY_MAX_AXES_NUMBER = 'pimdata/variantfamily/max_axes_number';

    /**
     * Retrieve max axes number
     *
     * @return int
     */
    public function getMaxAxesNumber()
    {
        return (int)Mage::getStoreConfig(self::CONFIG_PIMGENTO_VARIANT_FAMILY_MAX_AXES_NUMBER);
    }
}
