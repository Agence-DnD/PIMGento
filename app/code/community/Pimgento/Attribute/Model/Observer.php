<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Attribute_Model_Observer
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

        /* @var $helper Pimgento_Attribute_Helper_Data */
        $helper = Mage::helper('pimgento_attribute');

        $task->addTask(
            'pimgento_attribute',
            array(
                'label'   => $helper->__('Pim: Import Attributes'),
                'type'    => 'file',
                'comment' => $helper->__('Import attributes from PIM, upload CSV file.'),
                'steps' => array(
                    1 => array(
                        'comment' => $helper->__('Create temporary table'),
                        'method'  => 'pimgento_attribute/import::createTable'
                    ),
                    2 => array(
                        'comment' => $helper->__('Insert data into temporary table'),
                        'method'  => 'pimgento_attribute/import::insertData'
                    ),
                    3 => array(
                        'comment' => $helper->__('Match family code with entity'),
                        'method'  => 'pimgento_attribute/import::matchEntity'
                    ),
                    4 => array(
                        'comment' => $helper->__('Match type with Magento logic'),
                        'method'  => 'pimgento_attribute/import::matchType'
                    ),
                    5 => array(
                        'comment' => $helper->__('Add attributes if not exists'),
                        'method'  => 'pimgento_attribute/import::addAttributes'
                    ),
                    6 => array(
                        'comment' => $helper->__('Update attributes'),
                        'method'  => 'pimgento_attribute/import::updateAttributes'
                    ),
                    7 => array(
                        'comment' => $helper->__('Update family'),
                        'method'  => 'pimgento_attribute/import::updatefamily'
                    ),
                    8 => array(
                        'comment' => $helper->__('Drop temporary table'),
                        'method'  => 'pimgento_attribute/import::dropTable'
                    ),
                    9 => array(
                        'comment' => $helper->__('Reindex Data'),
                        'method'  => 'pimgento_attribute/import::reindex'
                    ),
                    10 => array(
                        'comment' => $helper->__('Clean cache'),
                        'method'  => 'pimgento_attribute/import::cleanCache'
                    ),
                )
            )
        );

        return $this;
    }

    /**
     * Delete attribute code
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this;
     */
    public function deleteAttributeCode(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();

        Mage::getModel('pimgento_core/code')
            ->setEntityId($attribute->getId())
            ->setImport(Mage::getModel('pimgento_attribute/import')->getCode())
            ->delete();

        return $this;
    }

}