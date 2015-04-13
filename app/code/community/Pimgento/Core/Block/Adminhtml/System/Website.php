<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Block_Adminhtml_System_Website extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->addColumn('channel', array(
                'label' => Mage::helper('pimgento_core')->__('Channel'),
                'style' => 'width:120px',
            ));

        $this->addColumn('website', array(
                'renderer' => new Pimgento_Core_Block_Adminhtml_Source_Website(),
                'label'    => Mage::helper('pimgento_core')->__('Website'),
                'style'    => 'width:120px',
            ));

        $this->_addAfter = false;

        $this->_addButtonLabel = Mage::helper('pimgento_core')->__('Add');

        parent::__construct();
    }

}