<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Model_Task extends Varien_Object
{

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->loadAllTasks();
    }

    /**
     * Load task
     *
     * @param string $command
     *
     * @return $this
     */
    public function load($command)
    {
        $tasks = $this->getTasks();

        $this->setTaskId($this->_getId());
        $this->printPrefix(true);

        if (!isset($tasks[$command])) {
            $this->addTask($command, array());
            $tasks = $this->getTasks();
        }

        $this->setData('command', $command);
        $this->setData('task', $tasks[$command]);
        $this->setStepNumber(0);

        return $this;
    }

    /**
     * Execute task
     *
     * @return bool
     * @throws Exception
     */
    public function execute()
    {
        $execute = false;

        if (($method = $this->getStepMethod())) {
            Mage::dispatchEvent('task_executor_step_start', array('task' => $this));

            list($alias, $method) = explode('::', $method);

            $model = Mage::getSingleton($alias);
            if (!$model) {
                $this->error(
                    $this->_getHelper()->__('%s model does not exists', $alias)
                );
            }
            if (!method_exists($model, $method)) {
                $this->error(
                    $this->_getHelper()->__('%s method does not exists in %s', $method, get_class($model))
                );
            }
            $execute = $model->$method($this);

            Mage::dispatchEvent('task_executor_step_end', array('task' => $this));
        }

        return $execute;
    }

    /**
     * Switch to the next Step
     *
     * @return $this
     */
    public function nextStep()
    {
        if (!$this->getLock()) {
            $stepNumber = (int)$this->getStepNumber();
            $this->setStepNumber($stepNumber + 1);
        }

        return $this;
    }

    /**
     * Switch to the previous Step
     *
     * @return $this
     */
    public function previousStep()
    {
        if (!$this->getLock()) {
            $stepNumber = (int)$this->getStepNumber();
            $this->setStepNumber($stepNumber - 1);
        }

        return $this;
    }

    /**
     * Prevented from proceeding to the next or previous step
     *
     * @return $this
     */
    public function lockStep()
    {
        $this->setLock(true);

        return $this;
    }

    /**
     * Unlock next and previous movements
     *
     * @return $this
     */
    public function unlockStep()
    {
        $this->unsLock();

        return $this;
    }

    /**
     * Set step number and set associated step
     *
     * @param $number
     *
     * @return $this
     */
    public function setStepNumber($number)
    {
        $task = $this->getTask();

        $this->unsetData('step_number');

        if ($task) {
            if (isset($task['steps'][$number])) {
                $this->setData('step_number', $number);
            }
        }

        return $this;
    }

    /**
     * Retrieve current step
     *
     * @return array|bool
     */
    public function getCurrentStep()
    {
        $task = $this->getTask();

        if (!is_null($this->getStepNumber())) {
            return $task['steps'][$this->getStepNumber()];
        }

        return false;
    }

    /**
     * Retrieve current step method. Send false if step is undefined
     *
     * @return string|bool
     */
    public function getStepMethod()
    {
        $step = $this->getCurrentStep();

        if ($step) {
            return isset($step['method']) ? $step['method'] : false;
        }
        return false;
    }

    /**
     * Retrieve current step comment. Send false if step is undefined
     *
     * @return string|bool
     */
    public function getStepComment()
    {
        $step = $this->getCurrentStep();

        if ($step) {
            if (isset($step['comment'])) {
                return $step['comment'] ? $this->getStepCommentPrefix() . $step['comment'] : false;
            }
        }
        return false;
    }

    /**
     * Set end step message
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->setData(
            'message_step_' . $this->getStepNumber(), $this->getStepCommentPrefix() . $message
        );

        return $this;
    }

    /**
     * Retrieve end step message
     *
     * @return string
     */
    public function getMessage()
    {
        $stepNumber = $this->getStepNumber();

        if (!$this->hasData('message_step_' . $stepNumber)) {
            $this->setMessage($this->_getHelper()->__('Step completed'));
        }

        return $this->getData('message_step_' . $stepNumber);
    }

    /**
     * Retrieve task options
     *
     * @return Varien_Object
     */
    public function getOptions()
    {
        if (!$this->getData('options')) {
            $this->setData('options', new Varien_Object());
        }

        return $this->getData('options');
    }

    /**
     * Transform options to object
     *
     * @param array $items
     *
     * @return void
     */
    public function addOptions($items)
    {
        if (is_array($items)) {
            foreach ($items as $key => $value) {
                if (!is_object($value)) {
                    $this->getOptions()->setData($key, $value);
                }
            }
        }

        return $this;
    }

    /**
     * Add Task to executor
     *
     * @param string $command
     * @param array  $data
     *
     * @return $this
     */
    public function addTask($command, $data)
    {
        $tasks = $this->getData('tasks');

        if (!$tasks) {
            $tasks = array();
        }

        $data['steps'][0] = array(
            'comment' => $this->_getHelper()->__('Start task'),
            'method'  => 'pimgento_core/task::startTask'
        );
        $data['steps'][count($data['steps'])] = array(
            'comment' => $this->_getHelper()->__('End task'),
            'method'  => 'pimgento_core/task::endTask'
        );

        $tasks[$command] = $data;

        $this->setData('tasks', $tasks);

        return $this;
    }

    /**
     * Check task is over
     *
     * @return bool
     */
    public function taskIsOver()
    {
        return is_null($this->getStepNumber());
    }

    /**
     * Start Task
     *
     * @return bool
     */
    public function startTask()
    {
        Mage::dispatchEvent('task_executor_start', array('task' => $this));

        $this->setMessage(
            $this->_getHelper()->__('Task id: %s', $this->getTaskId())
        );

        return true;
    }

    /**
     * End Task
     *
     * @return bool
     */
    public function endTask()
    {
        Mage::dispatchEvent('task_executor_end', array('task' => $this));

        $this->setMessage(
            $this->_getHelper()->__('Task id: %s', $this->getTaskId())
        );

        return true;
    }

    /**
     * Retrieve all tasks with event
     */
    public function loadAllTasks()
    {
        if (!$this->getData('tasks')) {
            Mage::dispatchEvent('task_executor_load_task', array('task' => $this));
        }
    }

    /**
     * Add prefix to messages
     *
     * @param bool $state
     *
     * @return $this
     */
    public function printPrefix($state)
    {
        $this->setData('print_prefix', $state);

        return $this;
    }

    /**
     * Retrieve step comment prefix
     *
     * @return string
     */
    public function getStepCommentPrefix()
    {
        return $this->getData('print_prefix') ? '['.$this->_getTime().'] ' : '';
    }

    /**
     * Dispatch event error
     *
     * @param string $message
     *
     * @return $this
     */
    public function dispatchError($message)
    {
        Mage::dispatchEvent('task_executor_error', array('error' => $message, 'task' => $this));

        return $this;
    }

    /**
     * Throw Exception
     *
     * @param string$message
     * @throws Exception
     */
    public function error($message)
    {
        throw new Exception($message);
    }

    /**
     * Retrieve default id
     *
     * @return string
     */
    protected function _getId()
    {
        return uniqid();
    }

    /**
     * Retrieve Current Time
     *
     * @return string
     */
    protected function _getTime()
    {
        /* @var $dateModel Mage_Core_Model_Date */
        $dateModel = Mage::getModel('core/date');

        return $dateModel->date('H:i:s');
    }

    /**
     * Retrieve TaskExecutor Helper
     *
     * @return Pimgento_Core_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('pimgento_core');
    }

}