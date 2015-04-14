<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Attribute_Block_Adminhtml_System_Type
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->addColumn('pimgento_type', array(
                'label' => Mage::helper('pimgento_attribute')->__('Pim type'),
                'style' => 'width:120px',
            ));

        $this->addColumn('magento_type', array(
                'renderer' => new Pimgento_Attribute_Block_Adminhtml_Source_Type(),
                'label'    => Mage::helper('pimgento_attribute')->__('Magento type'),
                'style'    => 'width:120px',
            ));

        $this->_addAfter = false;

        $this->_addButtonLabel = Mage::helper('pimgento_attribute')->__('Add');

        parent::__construct();
    }

}