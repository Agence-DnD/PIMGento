<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Product_Model_System_Config_Source_Configurable_Attribute
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = $this->getCollection();

        $options = array();

        foreach ($attributes as $attribute) {
            $options[] = array('value' => $attribute->getId(), 'label' => $attribute->getAttributeCode());
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = $this->getCollection();

        $options = array();

        foreach ($attributes as $attribute) {
            $options[$attribute->getId()] = $attribute->getAttributeCode();
        }

        return $options;
    }

    /**
     * Retrieve attributes collection
     *
     * @return mixed
     */
    protected function getCollection()
    {
        return Mage::getResourceModel('catalog/product_attribute_collection')->addFieldToFilter('is_configurable', 1);
    }

}
