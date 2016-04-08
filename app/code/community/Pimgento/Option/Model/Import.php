<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Option_Model_Import extends Pimgento_Core_Model_Import_Abstract
{

    /**
     * @var string
     */
    protected $_code = 'option';

    /**
     * Create table (Step 1)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function createTable($task)
    {
        $file = $task->getFile();

        $this->getRequest()->createTableFromFile($this->getCode(), $file);

        return true;
    }

    /**
     * Insert data (Step 2)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     * @throws Exception
     */
    public function insertData($task)
    {
        $file = $task->getFile();

        $lines = $this->getRequest()->insertDataFromFile($this->getCode(), $file);

        if (!$lines) {
            $task->error(
                Mage::helper('pimgento_option')->__(
                    'No data to insert, verify the file is not empty or CSV configuration is correct'
                )
            );
        }

        $this->deleteExclusion();

        $task->setMessage(
            Mage::helper('pimgento_option')->__('%s lines found', $lines)
        );

        return true;
    }

    /**
     * Match Entity with Code (Step 3)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function matchEntity($task)
    {
        $this->getRequest()->matchEntity($this->getCode(), 'eav/attribute_option', 'option_id', 'attribute');

        return true;
    }

    /**
     * Insert Option (Step 4)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function insertOptions($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        /* @var $attribute Pimgento_Attribute_Model_Import */
        $attribute = Mage::getModel('pimgento_attribute/import');

        $columns = array(
            'option_id'  => 'a.entity_id',
            'sort_order' => $this->_zde('"0"')
        );

        if ($this->getRequest()->columnExists($this->getTable(), 'sort_order')) {
            $columns['sort_order'] = 'a.sort_order';
        }

        $options = $adapter->select()
            ->from(array('a' => $this->getTable()), $columns)
            ->joinInner(
                array('b' => $resource->getTable('pimgento_core/code')),
                'a.attribute = b.code AND b.import = "' . $attribute->getCode() . '"',
                array(
                    'attribute_id' => 'b.entity_id'
                )
            );

        $insert = $adapter->insertFromSelect(
            $options, $resource->getTable('eav/attribute_option'), array('option_id', 'sort_order', 'attribute_id'), 1
        );

        $adapter->query($insert);

        return true;
    }

    /**
     * Insert Values (Step 5)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function insertValues($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        $stores = $helper->getStoresLang();

        foreach ($stores as $local => $ids) {

            if ($this->getRequest()->columnExists($this->getTable(), 'label-' . $local)) {

                foreach ($ids as $storeId) {

                    $options = $adapter->select()
                        ->from(
                            $this->getTable(),
                            array(
                                'option_id' => 'entity_id',
                                'store_id'  => $this->_zde($storeId),
                                'value'     => 'label-' . $local
                            )
                        );

                    $insert = $adapter->insertFromSelect(
                        $options,
                        $resource->getTable('eav/attribute_option_value'),
                        array('option_id', 'store_id', 'value'),
                        1
                    );

                    $adapter->query($insert);

                }

            }

        }

        return true;
    }

    /**
     * Drop table (Step 6)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function dropTable($task)
    {
        $this->getRequest()->dropTable($this->getCode());

        Mage::dispatchEvent('task_executor_drop_table_after', array('task' => $task));

        return true;
    }

    /**
     * Reindex (Step 7)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function reindex($task)
    {
        if ($task->getNoReindex()) {
            return false;
        }

        if (!$this->getConfig('reindex')) {
            $task->setMessage(
                Mage::helper('pimgento_option')->__('Reindex is disabled')
            );
            return false;
        }

        /* @var $indexer Mage_Index_Model_Indexer */
        $indexer = Mage::getSingleton('index/indexer');

        Mage::dispatchEvent('shell_reindex_init_process');

        $processes = array(
            'catalog_product_attribute',
        );

        foreach ($processes as $code) {
            $process = $indexer->getProcessByCode($code);
            if ($process) {
                $process->reindexEverything();
                Mage::dispatchEvent($code . '_shell_reindex_after');
            }
        }

        Mage::dispatchEvent('shell_reindex_finalize_process');

        return true;
    }

    /**
     * Remove exclusion from config
     *
     * @return bool
     */
    public function deleteExclusion()
    {
        parent::deleteExclusion();

        /* @var $attribute Pimgento_Attribute_Model_Import */
        $attribute = Mage::getModel('pimgento_attribute/import');

        $exclusions = Mage::getStoreConfig('pimdata/' . $attribute->getCode() . '/exclusions');

        if ($exclusions) {
            $exclusions = explode(',', $exclusions);
            foreach ($exclusions as $code) {
                $this->getAdapter()->delete($this->getTable(), array('attribute = ?' => $code));
            }
        }

        return true;
    }

}