<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Model_System_Config_Source_Cache_Type
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();

        foreach (Mage::app()->getCacheInstance()->getTypes() as $type) {
            $options[] = array('value' => $type->getId(), 'label' => $type->getCacheType());
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
        $options = array();

        foreach (Mage::app()->getCacheInstance()->getTypes() as $type) {
            $options[$type->getId()] = $type->getCacheType();
        }

        return $options;
    }

}
