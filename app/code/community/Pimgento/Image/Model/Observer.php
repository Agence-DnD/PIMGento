<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Image_Model_Observer
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

        /* @var $helper Pimgento_Image_Helper_Data */
        $helper = Mage::helper('pimgento_image');

        $task->addTask(
            'pimgento_image',
            array(
                'label' => $helper->__('Pim: Import Images'),
                'type'  => 'button',
                'comment' => $helper->__('Import images from %s directory', 'media/import/files/'),
                'steps' => array(
                    1 => array(
                        'comment' => $helper->__('Create temporary table'),
                        'method'  => 'pimgento_image/import::createTable'
                    ),
                    2 => array(
                        'comment' => $helper->__('Detect configurable'),
                        'method'  => 'pimgento_image/import::detectConfigurable'
                    ),
                    3 => array(
                        'comment' => $helper->__('Move images'),
                        'method'  => 'pimgento_image/import::moveImages'
                    ),
                    4 => array(
                        'comment' => $helper->__('Match PIM code with entity'),
                        'method'  => 'pimgento_image/import::matchEntity'
                    ),
                    5 => array(
                        'comment' => $helper->__('Associate images to products'),
                        'method'  => 'pimgento_image/import::setValues'
                    ),
                    6 => array(
                        'comment' => $helper->__('Drop temporary table'),
                        'method'  => 'pimgento_image/import::dropTable'
                    ),
                    7 => array(
                        'comment' => $helper->__('Reindex Data'),
                        'method'  => 'pimgento_image/import::reindex'
                    ),
                    8 => array(
                        'comment' => $helper->__('Flush catalog image cache'),
                        'method'  => 'pimgento_image/import::cleanImage'
                    ),
                    9 => array(
                        'comment' => $helper->__('Clean cache'),
                        'method'  => 'pimgento_image/import::cleanCache'
                    ),
                )
            )
        );

        return $this;
    }

}