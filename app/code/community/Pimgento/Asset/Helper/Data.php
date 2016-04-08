<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Asset_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * Retrieve product base media path
     *
     * @return string
     */
    public function getBaseMediaPath()
    {
        /* @var $media Mage_Catalog_Model_Product_Media_Config */
        $media = Mage::getSingleton('catalog/product_media_config');

        return $media->getBaseMediaPath();
    }

}