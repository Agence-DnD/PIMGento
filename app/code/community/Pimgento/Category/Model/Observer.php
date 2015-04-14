<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Category_Model_Observer
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

        /* @var $helper Pimgento_Category_Helper_Data */
        $helper = Mage::helper('pimgento_category');

        $task->addTask(
            'pimgento_category',
            array(
                'label'   => $helper->__('Pim: Import Categories'),
                'type'    => 'file',
                'comment' => $helper->__('Import categories, upload CSV file.'),
                'steps' => array(
                    1 => array(
                        'comment' => $helper->__('Create temporary table'),
                        'method'  => 'pimgento_category/import::createTable'
                    ),
                    2 => array(
                        'comment' => $helper->__('Insert data into temporary table'),
                        'method'  => 'pimgento_category/import::insertData'
                    ),
                    3 => array(
                        'comment' => $helper->__('Match PIM code with entity'),
                        'method'  => 'pimgento_category/import::matchEntity'
                    ),
                    4 => array(
                        'comment' => $helper->__('Detect categories level'),
                        'method'  => 'pimgento_category/import::setLevel'
                    ),
                    5 => array(
                        'comment' => $helper->__('Detect categories position'),
                        'method'  => 'pimgento_category/import::setPosition'
                    ),
                    6 => array(
                        'comment' => $helper->__('Create category entities'),
                        'method'  => 'pimgento_category/import::createEntities'
                    ),
                    7 => array(
                        'comment' => $helper->__('Set values to attributes'),
                        'method'  => 'pimgento_category/import::setValues'
                    ),
                    8 => array(
                        'comment' => $helper->__('Count of child categories'),
                        'method'  => 'pimgento_category/import::updateChildrenCount'
                    ),
                    9 => array(
                        'comment' => $helper->__('Update URL keys'),
                        'method'  => 'pimgento_category/import::updateUrl'
                    ),
                    10 => array(
                        'comment' => $helper->__('Drop temporary table'),
                        'method'  => 'pimgento_category/import::dropTable'
                    ),
                    11 => array(
                        'comment' => $helper->__('Reindex data'),
                        'method'  => 'pimgento_category/import::reindex'
                    ),
                    12 => array(
                        'comment' => $helper->__('Clean cache'),
                        'method'  => 'pimgento_category/import::cleanCache'
                    ),
                )
            )
        );

        return $this;
    }

    /**
     * Delete category code
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this;
     */
    public function deleteCategoryCode(Varien_Event_Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();

        Mage::getModel('pimgento_core/code')
            ->setEntityId($category->getId())
            ->setImport(Mage::getModel('pimgento_category/import')->getCode())
            ->delete();

        return $this;
    }

}