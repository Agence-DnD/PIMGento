<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Option_Model_Observer
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

        /* @var $helper Pimgento_Option_Helper_Data */
        $helper = Mage::helper('pimgento_option');

        $task->addTask(
            'pimgento_option',
            array(
                'label'   => $helper->__('Pim: Import Options'),
                'type'    => 'file',
                'comment' => $helper->__('Import options, upload CSV file.'),
                'steps' => array(
                    1 => array(
                        'comment' => $helper->__('Create temporary table'),
                        'method'  => 'pimgento_option/import::createTable'
                    ),
                    2 => array(
                        'comment' => $helper->__('Insert data into temporary table'),
                        'method'  => 'pimgento_option/import::insertData'
                    ),
                    3 => array(
                        'comment' => $helper->__('Match PIM code with entity'),
                        'method'  => 'pimgento_option/import::matchEntity'
                    ),
                    4 => array(
                        'comment' => $helper->__('Associate options to attributes'),
                        'method'  => 'pimgento_option/import::insertOptions'
                    ),
                    5 => array(
                        'comment' => $helper->__('Associate values to options'),
                        'method'  => 'pimgento_option/import::insertValues'
                    ),
                    6 => array(
                        'comment' => $helper->__('Drop temporary table'),
                        'method'  => 'pimgento_option/import::dropTable'
                    ),
                    7 => array(
                        'comment' => $helper->__('Reindex data'),
                        'method'  => 'pimgento_option/import::reindex'
                    ),
                    8 => array(
                        'comment' => $helper->__('Clean cache'),
                        'method'  => 'pimgento_option/import::cleanCache'
                    ),
                )
            )
        );

        return $this;
    }

    /**
     * Delete option code
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this;
     */
    public function deleteOptionCode(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();

        if ($attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
            foreach ($options as $option) {
                Mage::getModel('pimgento_core/code')
                    ->setEntityId($option['value'])
                    ->setImport(Mage::getModel('pimgento_option/import')->getCode())
                    ->delete();
            }
        }

        return $this;
    }

}