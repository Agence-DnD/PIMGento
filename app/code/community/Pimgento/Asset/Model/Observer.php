<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Asset_Model_Observer
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

        /* @var $helper Pimgento_Variant_Helper_Data */
        $helper = Mage::helper('pimgento_asset');

        $task->addTask(
            'pimgento_asset',
            array(
                'label'   => $helper->__('Pim: Import Asset Variation'),
                'type'    => 'file',
                'comment' => $helper->__('Import asset, upload CSV file.'),
                'steps' => array(
                    1 => array(
                        'comment' => $helper->__('Create temporary table'),
                        'method'  => 'pimgento_asset/import::createTable'
                    ),
                    2 => array(
                        'comment' => $helper->__('Insert data into temporary table'),
                        'method'  => 'pimgento_asset/import::insertData'
                    ),
                    3 => array(
                        'comment' => $helper->__('Update columns'),
                        'method'  => 'pimgento_asset/import::updateColumns'
                    ),
                    4 => array(
                        'comment' => $helper->__('Update asset table'),
                        'method'  => 'pimgento_asset/import::updateTable'
                    ),
                    5 => array(
                        'comment' => $helper->__('Drop temporary table'),
                        'method'  => 'pimgento_asset/import::dropTable'
                    ),
                    6 => array(
                        'comment' => $helper->__('Download Images'),
                        'method'  => 'pimgento_asset/import::downloadImages'
                    ),
                )
            )
        );

        return $this;
    }

}