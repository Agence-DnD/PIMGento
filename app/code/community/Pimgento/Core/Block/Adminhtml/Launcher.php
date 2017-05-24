<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Block_Adminhtml_Launcher extends Mage_Uploader_Block_Multiple
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('pimgento/launcher.phtml');

        $type = $this->_getMediaType();

        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        $allowed = $helper->getAllowedExtensions();

        $labels = array();
        $files  = array();

        foreach ($allowed as $ext) {
            $labels[] = '.' . $ext;
            $files[]  = '*.' . $ext;
        }

        /* @var $urlModel Mage_Adminhtml_Model_Url */
        $urlModel = Mage::getModel('adminhtml/url');

        $this->getUploaderConfig()
             ->setFileParameterName('file')
             ->setTarget(
                 $urlModel->addSessionParam()->getUrl('adminhtml/task/upload', array('type' => $type))
             );

        $this->getButtonConfig()
            ->setAttributes(
                array(
                    'accept' => $allowed
                )
            );
    }

    /**
     * Prepare Layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                    'label'   => Mage::helper('pimgento_core')->__('Execute'),
                    'onclick' => 'taskExecutor.run(this.rel);',
                    'class'   => 'save',
                ));

        $this->setChild('execute_button', $block);

        return parent::_prepareLayout();
    }

    /**
     * Return current media type based on request or data
     * @return string
     */
    protected function _getMediaType()
    {
        if ($this->hasData('media_type')) {
            return $this->_getData('media_type');
        }
        return $this->getRequest()->getParam('type');
    }

    /**
     * Get html code of button
     *
     * @return string
     */
    public function getExecuteButtonHtml()
    {
        return $this->getChildHtml('execute_button');
    }

    /**
     * Retrieve All Tasks
     *
     * @return array
     */
    public function getTasks()
    {
        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        return $helper->getTasks();
    }

    /**
     *
     * Retrieve Task As Json Object
     *
     * @return string
     */
    public function getTasksJson()
    {
        $tasks = $this->getTasks();

        /* @var $helper Mage_Core_Helper_Data */
        $helper = Mage::helper('core');

        return $helper->jsonEncode($tasks);
    }

}