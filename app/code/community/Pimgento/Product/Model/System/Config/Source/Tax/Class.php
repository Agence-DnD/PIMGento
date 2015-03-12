<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Product_Model_System_Config_Source_Tax_Class
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $taxes = $this->getCollection();

        $options = array(
            array(
                'value' => 0,
                'label' => Mage::helper('pimgento_product')->__('None')
            )
        );

        foreach ($taxes as $tax) {
            $options[] = array('value' => $tax->getId(), 'label' => $tax->getClassName());
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
        $taxes = $this->getCollection();

        $options = array(
            '0' => Mage::helper('pimgento_product')->__('None')
        );

        foreach ($taxes as $tax) {
            $options[$tax->getId()] = $tax->getClassName();
        }

        return $options;
    }

    /**
     * Retrieve collection
     *
     * @return mixed
     */
    protected function getCollection()
    {
        return Mage::getModel('tax/class')->getCollection()->addFieldToFilter('class_type', 'PRODUCT');
    }

}
