<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Product_Model_Observer
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

        /* @var $helper Pimgento_Product_Helper_Data */
        $helper = Mage::helper('pimgento_product');

        $task->addTask(
            'pimgento_product',
            array(
                'label'   => $helper->__('Pim: Import Products'),
                'type'    => 'file',
                'comment' => $helper->__('Import products, upload CSV file.'),
                'steps' => array(
                    1 => array(
                        'comment' => $helper->__('Create temporary table'),
                        'method'  => 'pimgento_product/import::createTable'
                    ),
                    2 => array(
                        'comment' => $helper->__('Insert data into temporary table'),
                        'method'  => 'pimgento_product/import::insertData'
                    ),
                    3 => array(
                        'comment' => $helper->__('Update column name'),
                        'method'  => 'pimgento_product/import::updateColumn'
                    ),
                    4 => array(
                        'comment' => $helper->__('Detect configurable products'),
                        'method'  => 'pimgento_product/import::createConfigurable'
                    ),
                    5 => array(
                        'comment' => $helper->__('Match PIM code with entity'),
                        'method'  => 'pimgento_product/import::matchEntity'
                    ),
                    6 => array(
                        'comment' => $helper->__('Update family'),
                        'method'  => 'pimgento_product/import::updateFamily'
                    ),
                    7 => array(
                        'comment' => $helper->__('Update column values for options'),
                        'method'  => 'pimgento_product/import::updateValues'
                    ),
                    8 => array(
                        'comment' => $helper->__('Create product entities'),
                        'method'  => 'pimgento_product/import::createEntities'
                    ),
                    9 => array(
                        'comment' => $helper->__('Set values to attributes'),
                        'method'  => 'pimgento_product/import::setValues'
                    ),
                    10 => array(
                        'comment' => $helper->__('Update configurable products relation'),
                        'method'  => 'pimgento_product/import::updateConfigurable'
                    ),
                    11 => array(
                        'comment' => $helper->__('Set products to websites'),
                        'method'  => 'pimgento_product/import::setWebsites'
                    ),
                    12 => array(
                        'comment' => $helper->__('Update prices'),
                        'method'  => 'pimgento_product/import::setPrices'
                    ),
                    13 => array(
                        'comment' => $helper->__('Update configurable prices'),
                        'method'  => 'pimgento_product/import::setConfigurablePrices'
                    ),
                    14 => array(
                        'comment' => $helper->__('Update categories relation'),
                        'method'  => 'pimgento_product/import::setCategories'
                    ),
                    15 => array(
                        'comment' => $helper->__('Init stock'),
                        'method'  => 'pimgento_product/import::initStock'
                    ),
                    16 => array(
                        'comment' => $helper->__('Update related, up-sell and cross-sell products'),
                        'method'  => 'pimgento_product/import::setRelated'
                    ),
                    17 => array(
                        'comment' => $helper->__('Insert product images (Asset, only Akeneo Enterprise)'),
                        'method'  => 'pimgento_product/import::setAsset'
                    ),
                    18 => array(
                        'comment' => $helper->__('Drop temporary table'),
                        'method'  => 'pimgento_product/import::dropTable'
                    ),
                    19 => array(
                        'comment' => $helper->__('Reindex data'),
                        'method'  => 'pimgento_product/import::reindex'
                    ),
                    20 => array(
                        'comment' => $helper->__('Clean cache'),
                        'method'  => 'pimgento_product/import::cleanCache'
                    ),
                )
            )
        );

        return $this;
    }

    /**
     * Delete product code
     *
     * @param Varien_Event_Observer $observer
     *
     * @return $this;
     */
    public function deleteProductCode(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        Mage::getModel('pimgento_core/code')
            ->setEntityId($product->getId())
            ->setImport(Mage::getModel('pimgento_product/import')->getCode())
            ->delete();

        return $this;
    }

}