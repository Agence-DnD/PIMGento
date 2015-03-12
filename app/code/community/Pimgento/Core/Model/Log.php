<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Model_Log
{

    const LOG_FILE_NAME = 'pimgento.log';

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

        $this->_log($error, Zend_Log::ERR, self::LOG_FILE_NAME);

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
        if ($this->logEnabled()) {
            Mage::log($message, $level, self::LOG_FILE_NAME, true);
        }
    }

}