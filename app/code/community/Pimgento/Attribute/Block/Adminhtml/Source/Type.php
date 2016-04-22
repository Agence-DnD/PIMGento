<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Attribute_Block_Adminhtml_Source_Type extends Mage_Adminhtml_Block_Abstract
{

    /**
     * @var string
     */
    protected $_template = 'pimgento/attribute/type.phtml';

    /**
     * Retrieve options
     *
     * @return array
     */
    public function getOptions()
    {
        /* @var $input Mage_Eav_Model_Adminhtml_System_Config_Source_Inputtype */
        $input = Mage::getModel('eav/adminhtml_system_config_source_inputtype');

        $types = $input->toOptionArray();
        $types[] = array('value' => 'price', 'label' => Mage::helper('pimgento_attribute')->__('Price'));

        return $types;
    }

}