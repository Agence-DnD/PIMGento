<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Product_Block_Adminhtml_System_Configurable_Default
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setTemplate('pimgento/product/configurable/default.phtml');

        $this->addColumn('attribute', array(
                'label' => Mage::helper('pimgento_product')->__('Pim Attribute'),
                'style' => 'width:120px',
            ));

        $this->addColumn('value', array(
                'label' => Mage::helper('pimgento_product')->__('Value'),
                'style' => 'width:120px',
            ));

        $this->_addAfter = false;

        $this->_addButtonLabel = Mage::helper('pimgento_product')->__('Add');

        parent::__construct();
    }

}