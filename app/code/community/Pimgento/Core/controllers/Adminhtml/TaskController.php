<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Adminhtml_TaskController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/pimgento_data');
    }

    /**
     * Index Action
     */
    public function indexAction()
    {
        $this->loadLayout();

        $this->_title($this->__('System'))
             ->_title($this->__('Tasks'));

        $this->_setActiveMenu('catalog');

        $this->renderLayout();
    }

    /**
     * Options Action
     */
    public function optionsAction()
    {
        $command = $this->getRequest()->getPost('command');

        $this->loadLayout(false);
        $this->getLayout()->getBlock('pimgento.core.options')->setCommand($command);
        $this->renderLayout();
    }

    /**
     * Upload File Action
     */
    public function uploadAction()
    {
        if (!empty($_FILES)) {
            try {
                $uploader = new Varien_File_Uploader("file");

                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $uploader->setAllowCreateFolders(true);

                $path = $this->_getUploadDir();

                /* @var $helper Pimgento_Core_Helper_Data */
                $helper = Mage::helper('pimgento_core');

                $uploader->setAllowedExtensions($helper->getAllowedExtensions());
                $uploadSaveResult = $uploader->save($path, $_FILES['file']['name']);

                $result = $uploadSaveResult['file'];

            } catch(Exception $e) {
                $result = array(
                    "error" => $e->getMessage(),
                    "errorCode" => $e->getCode(),
                    "status" => "error"
                );
            }
            /* @var $helper Mage_Core_Helper_Data */
            $coreHelper = Mage::helper('core');

            $this->getResponse()->setBody($coreHelper->jsonEncode($result));
        }
    }

    /**
     * launch Action
     */
    public function launchAction()
    {
        $step    = (int) $this->getRequest()->getPost('step');
        $command = $this->getRequest()->getPost('command');
        $taskId  = $this->getRequest()->getPost('task_id');

        $file    = $this->getRequest()->getPost('file');
        $options = $this->getRequest()->getPost('options');

        /* @var $helper Mage_Core_Helper_Data */
        $helper = Mage::helper('core');

        /* @var $model Pimgento_Core_Model_Task */
        $model = Mage::getSingleton('pimgento_core/task');
        $task = $model->load($command);

        try {
            $task->setStepNumber($step)
                 ->setFile($file ? $this->_getUploadDir() . $file : null);

            if ($options) {
                $task->addOptions($helper->jsonDecode($options));
            }

            if ($taskId) {
                $task->setTaskId($taskId);
            }

            $messages = array();
            $error = false;

            $status = $task->execute();

            if (!$status) {
                $error = true;
            }

            if ($task->getStepNumber() === 0) {
                $messages[] = $task->getStepComment();
            }
            $messages[] = $task->getMessage();

            $task->nextStep();

            $messages[] = $task->getStepComment();

            $result = array(
                'messages' => $messages,
                'launch'   => $task->getStepNumber(),
                'error'    => $error,
                'task_id'  => $task->getTaskId(),
                'options'  => $helper->jsonEncode($task->getOptions()->toArray())
            );

        } catch(Exception $e) {
            $task->dispatchError($e->getMessage());

            $result = array(
                'messages' => array(
                    $e->getMessage()
                ),
                'launch'   => false,
                'error'    => true,
            );
        }

        $this->getResponse()->setBody($helper->jsonEncode($result));
    }

    /**
     * Retrieve Upload Directory
     *
     * @return string
     */
    protected function _getUploadDir()
    {
        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        return $helper->getUploadDir();
    }

}