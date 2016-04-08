<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Asset_Model_System_Config_Source_Attribute
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = $this->getAttributes();

        $options = array(
            array(
                'value' => '',
                'label' => '',
            )
        );

        foreach ($attributes as $attribute) {
            $options[] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getAttributeCode(),
            );
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
        $attributes = $this->getAttributes();

        $options = array('' => '');

        foreach ($attributes as $attribute) {
            $options[$attribute->getAttributeCode()] = $attribute->getAttributeCode();
        }

        return $options;
    }

    /**
     * Retrieve attributes
     *
     * @return Mage_Eav_Model_Resource_Attribute_Collection
     */
    public function getAttributes()
    {
        return Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter(4)
            ->addFieldToFilter('backend_type', array('neq' => 'static'));
    }

}
