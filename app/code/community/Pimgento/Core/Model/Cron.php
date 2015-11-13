<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Model_Cron
{

    /**
     * Execute task
     *
     * @param string $command
     * @param string $file
     *
     * @return $this
     */
    public function launch($command, $file = null)
    {
        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        /* @var $model Pimgento_Core_Model_Task */
        $model = Mage::getSingleton('pimgento_core/task');
        $task = $model->load($command);

        if ($command) {
            try {
                if ($file) {
                    $task->setFile($helper->getCronDir() . $file);
                }

                while (!$task->taskIsOver()) {
                    $task->execute();
                    $task->nextStep();
                }

            } catch(Exception $e) {
                $task->dispatchError($e->getMessage());
            }
        }

        return $this;
    }

}