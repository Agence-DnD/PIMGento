<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Block_Adminhtml_Task extends Mage_Adminhtml_Block_Template
{

    /**
     * Prepare Layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $launcher = $this->getLayout()->createBlock('pimgento_core/adminhtml_launcher');

        $this->setChild('launcher', $launcher);

        return parent::_prepareLayout();
    }

    /**
     * Get html of uploader
     *
     * @return string
     */
    public function getLauncher()
    {
        return $this->getChildHtml('launcher');
    }

    /**
     * Get page header text
     *
     * @return string
     */
    public function getHeader()
    {
        return Mage::helper('pimgento_core')->__('Tasks');
    }

}