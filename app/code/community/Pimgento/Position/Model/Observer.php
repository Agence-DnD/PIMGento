<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Position_Model_Observer
{

    /**
     * Add Task and steps to executor
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function addTask(Varien_Event_Observer $observer)
    {
        /* @var $task Pimgento_Core_Model_Task */
        $task = $observer->getEvent()->getTask();

        /* @var $helper Pimgento_Position_Helper_Data */
        $helper = Mage::helper('pimgento_position');

        $task->addTask(
            'update_position',
            array(
                'label'   => $helper->__('Categories: Update product positions'),
                'type'    => 'file',
                'comment' => $helper->__(
                    'Update product positions. Upload CSV with 3 columns : "sku", "category" and "position"'
                ),
                'steps' => array(
                    1 => array(
                        'comment' => $helper->__('Create temporary table'),
                        'method'  => 'pimgento_position/import::createTable'
                    ),
                    2 => array(
                        'comment' => $helper->__('Insert data into temporary table'),
                        'method'  => 'pimgento_position/import::insertData'
                    ),
                    3 => array(
                        'comment' => $helper->__('Update column name'),
                        'method'  => 'pimgento_position/import::updateColumn'
                    ),
                    4 => array(
                        'comment' => $helper->__('Match product code with entity'),
                        'method'  => 'pimgento_position/import::matchEntity'
                    ),
                    5 => array(
                        'comment' => $helper->__('Update categories'),
                        'method'  => 'pimgento_position/import::updateCategory'
                    ),
                    6 => array(
                        'comment' => $helper->__('Update positions'),
                        'method'  => 'pimgento_position/import::updatePosition'
                    ),
                    7 => array(
                        'comment' => $helper->__('Drop temporary table'),
                        'method'  => 'pimgento_position/import::dropTable'
                    ),
                    8 => array(
                        'comment' => $helper->__('Reindex Data'),
                        'method'  => 'pimgento_position/import::reindex'
                    ),
                    9 => array(
                        'comment' => $helper->__('Clean cache'),
                        'method'  => 'pimgento_position/import::cleanCache'
                    ),
                )
            )
        );

        return $this;
    }

}