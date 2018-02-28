<?php

/**
 * Class Pimgento_VariantFamily_Helper_Config
 *
 * @category  Class
 * @package   Pimgento_VariantFamily_Helper_Config
 * @author    Pierre Barbarin <pierre.barbarin@dnd.fr>
 * @copyright Copyright (c) 2018 Agence Dn'D
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.dnd.fr/
 */
class Pimgento_VariantFamily_Helper_Config extends Pimgento_VariantFamily_Helper_Data
{

    /** @var string CONFIG_PIMGENTO_VARIANT_FAMILY_MAX_AXES_NUMBER */
    const CONFIG_PIMGENTO_VARIANT_FAMILY_MAX_AXES_NUMBER = 'pimgento_variantfamily/import_config/max_axes_number';

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
