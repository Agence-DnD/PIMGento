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
     * @param bool   $reindex
     *
     * @return $this
     */
    public function launch($command, $file = null, $reindex = true)
    {
        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        /* @var $model Pimgento_Core_Model_Task */
        $model = Mage::getSingleton('pimgento_core/task');

        if ($command) {
            try {
                $task = $model->load($command);

                $task->setFile(null);
                if ($file) {
                    $task->setFile($helper->getCronDir() . $file);
                }

                $data = $task->getTask();
                if ($data['type'] == 'file' && !is_file($task->getFile())) {
                    return $this;
                }

                $task->setNoReindex(false);
                if (!$reindex) {
                    $task->setNoReindex(true);
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

    /**
     * Retrieve files
     *
     * @param string $import
     *
     * @return array
     */
    public function getFiles($import)
    {
        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        $cronFiles = Mage::getStoreConfig('pimdata/' . $import . '/cron_file');

        $import = array();

        if ($cronFiles) {
            $files = explode(';', $cronFiles);

            foreach ($files as $key => $file) {
                if (is_file($helper->getCronDir() . trim($file))) {
                    $import[] = trim($file);
                }
            }
        }

        return $import;
    }

}