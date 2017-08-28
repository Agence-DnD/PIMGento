<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Family_Model_Observer
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

        /* @var $helper Pimgento_Family_Helper_Data */
        $helper = Mage::helper('pimgento_family');

        $task->addTask(
            'pimgento_family',
            array(
                'label'   => $helper->__('Pim: Import Families'),
                'type'    => 'file',
                'comment' => $helper->__('Import families, upload CSV file.'),
                'steps' => array(
                    1 => array(
                        'comment' => $helper->__('Create temporary table'),
                        'method'  => 'pimgento_family/import::createTable'
                    ),
                    2 => array(
                        'comment' => $helper->__('Insert data into temporary table'),
                        'method'  => 'pimgento_family/import::insertData'
                    ),
                    3 => array(
                        'comment' => $helper->__('Match PIM code with entity'),
                        'method'  => 'pimgento_family/import::matchEntity'
                    ),
                    4 => array(
                        'comment' => $helper->__('Create Families'),
                        'method'  => 'pimgento_family/import::insertFamily'
                    ),
                    5 => array(
                        'comment' => $helper->__('Create family attribute relations'),
                        'method'  => 'pimgento_family/import::insertFamilyAttributeRelations'
                    ),
                    6 => array(
                        'comment' => $helper->__('Init default groups'),
                        'method'  => 'pimgento_family/import::initDefaultGroup'
                    ),
                    7 => array(
                        'comment' => $helper->__('Drop temporary table'),
                        'method'  => 'pimgento_family/import::dropTable'
                    ),
                    8 => array(
                        'comment' => $helper->__('Clean cache'),
                        'method'  => 'pimgento_family/import::cleanCache'
                    ),
                )
            )
        );

        return $this;
    }

    /**
     * Delete family code
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this;
     */
    public function deleteFamilyCode(Varien_Event_Observer $observer)
    {
        $attributeSet = $observer->getEvent()->getObject();

        Mage::getModel('pimgento_core/code')
            ->setEntityId($attributeSet->getId())
            ->setImport(Mage::getModel('pimgento_family/import')->getCode())
            ->delete();

        return $this;
    }

}