<?php

class Pimgento_Core_Model_Observer
{
    public function removeImportedFiles(Varien_Event_Observer $observer)
    {
        if (!Mage::getStoreConfig('pimdata/general/remove_csv_file_after_cron_import')) {
            return;
        }

        /* @var $task Pimgento_Core_Model_Task */
        $task = $observer->getEvent()->getTask();

        $file = $task->getFile();

        if (file_exists($file))
            unlink($file);
    }
}
