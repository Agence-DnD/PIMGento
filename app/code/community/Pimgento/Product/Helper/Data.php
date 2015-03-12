<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Product_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * Retrieve columns to match
     *
     * @return array
     */
    public function transformer()
    {
        $matches = unserialize(Mage::getStoreConfig('pimdata/product/matches'));

        $transform = array();

        foreach ($matches as $match) {
            if (!isset($transform[$match['pimgento_attribute']])) {
                $transform[$match['pimgento_attribute']] = array();
            }
            $transform[$match['pimgento_attribute']][] = $match['magento_attribute'];
        }

        return $transform;
    }

}