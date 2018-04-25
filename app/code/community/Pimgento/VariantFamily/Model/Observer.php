<?php
/**
 * Class Pimgento_VariantFamily_Model_Observer
 *
 * @category  Class
 * @package   Pimgento_VariantFamily_Model_Observer
 * @author    Pierre Barbarin <pierre.barbarin@dnd.fr>
 * @copyright Copyright (c) 2018 Agence Dn'D
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.dnd.fr/
 */

class Pimgento_VariantFamily_Model_Observer
{

    /**
     * @param Varien_Event_Observer $observer
     */
    public function taskExecutorLoadTask(Varien_Event_Observer $observer)
    {
        $this->addTask($observer);
    }

    /**
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    protected function addTask(Varien_Event_Observer $observer)
    {
        /* @var Pimgento_Core_Model_Task $task */
        $task = $observer->getEvent()->getTask();

        /* @var Pimgento_VariantFamily_Helper_Data $helper */
        $helper = Mage::helper('pimgento_variantfamily');

        $task->addTask('pimgento_variantfamily', [
                'label'   => $helper->__('Pim: Import Variant Family (Akeneo > 2.0)'),
                'type'    => 'file',
                'comment' => $helper->__('Import Variant Family, upload CSV file.'),
                'steps'   => [
                    1 => [
                        'comment' => $helper->__('Create temporary table'),
                        'method'  => 'pimgento_variantfamily/import::createTable'
                    ],
                    2 => [
                        'comment' => $helper->__('Insert data into temporary table'),
                        'method'  => 'pimgento_variantfamily/import::insertData'
                    ],
                    3 => [
                        'comment' => $helper->__('Update Axis'),
                        'method'  => 'pimgento_variantfamily/import::updateAxis'
                    ],
                    4 => [
                        'comment' => $helper->__('Update Product Model'),
                        'method'  => 'pimgento_variantfamily/import::updateProductModel'
                    ],
                    5 => [
                        'comment' => $helper->__('Drop temporary table'),
                        'method'  => 'pimgento_variantfamily/import::dropTable'
                    ],
                ]
            ]);

        return $this;
    }
}
