<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Stock_Model_Observer
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

        /* @var $helper Pimgento_Stock_Helper_Data */
        $helper = Mage::helper('pimgento_stock');

        $task->addTask(
            'update_stock',
            array(
                'label'   => $helper->__('Stock: Update'),
                'type'    => 'file',
                'comment' => $helper->__('Update stock. Upload CSV with 2 columns : "sku" and "qty"'),
                'steps' => array(
                    1 => array(
                        'comment' => $helper->__('Create temporary table'),
                        'method'  => 'pimgento_stock/import::createTable'
                    ),
                    2 => array(
                        'comment' => $helper->__('Insert data into temporary table'),
                        'method'  => 'pimgento_stock/import::insertData'
                    ),
                    3 => array(
                        'comment' => $helper->__('Update column name'),
                        'method'  => 'pimgento_stock/import::updateColumn'
                    ),
                    4 => array(
                        'comment' => $helper->__('Match product code with entity'),
                        'method'  => 'pimgento_stock/import::matchEntity'
                    ),
                    5 => array(
                        'comment' => $helper->__('Update stock data'),
                        'method'  => 'pimgento_stock/import::updateStock'
                    ),
                    6 => array(
                        'comment' => $helper->__('Drop temporary table'),
                        'method'  => 'pimgento_stock/import::dropTable'
                    ),
                    7 => array(
                        'comment' => $helper->__('Reindex Data'),
                        'method'  => 'pimgento_stock/import::reindex'
                    ),
                    8 => array(
                        'comment' => $helper->__('Clean cache'),
                        'method'  => 'pimgento_stock/import::cleanCache'
                    ),
                )
            )
        );

        return $this;
    }

}