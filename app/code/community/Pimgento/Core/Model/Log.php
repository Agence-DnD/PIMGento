<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Model_Log
{

    /**
     * Add log when error is sent
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function error(Varien_Event_Observer $observer)
    {
        /* @var $tasks Pimgento_Core_Model_Task */
        $error = $observer->getEvent()->getError();

        if ($this->getFile()) {
            $this->_log($error, Zend_Log::ERR, $this->getFile());
        }

        return $this;
    }

    /**
     * Add log when task begins
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function startTask(Varien_Event_Observer $observer)
    {
        /* @var $task Pimgento_Core_Model_Task */
        $task = $observer->getEvent()->getTask();

        $this->_log(str_pad('', 84, '-'), Zend_Log::INFO);
        $this->_log($task->getCommand() . ' : ' . $task->getFile(), Zend_Log::INFO);

        return $this;
    }

    /**
     * Add log when step begins
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function startStep(Varien_Event_Observer $observer)
    {
        /* @var $task Pimgento_Core_Model_Task */
        $task = $observer->getEvent()->getTask();

        if ($task->getStepComment() && $task->getStepNumber() > 0) {
            $this->_log($task->getStepComment(), Zend_Log::INFO);
        }

        return $this;
    }

    /**
     * Add log when step ends
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function endStep(Varien_Event_Observer $observer)
    {
        /* @var $task Pimgento_Core_Model_Task */
        $task = $observer->getEvent()->getTask();

        $this->_log($task->getMessage(), Zend_Log::INFO);

        return $this;
    }

    /**
     * Retrieve log file name
     *
     * @retun string
     */
    public function getFile()
    {
        $file = trim(Mage::getStoreConfig('pimdata/general/log_file'), '/');

        if ($file) {
            $log = Mage::getBaseDir('var') . DS . 'log' . DS . $file;

            $directory = dirname($log);
            if ($directory) {
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);
                }
            }
        }

        return $file;
    }

    /**
     * Check if log is enabled
     *
     * @return int
     */
    public function logEnabled()
    {
        return (int)Mage::getStoreConfig('pimdata/general/log');
    }

    /**
     * Log message
     *
     * @param string $message
     * @param int $level
     *
     * @return void
     */
    protected function _log($message, $level)
    {
        if ($this->logEnabled() && $this->getFile()) {
            Mage::log($message, $level, $this->getFile(), true);
        }
    }

}