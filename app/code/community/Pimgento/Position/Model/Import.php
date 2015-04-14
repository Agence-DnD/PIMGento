<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Position_Model_Import extends Pimgento_Core_Model_Import_Abstract
{

    /**
     * @var string
     */
    protected $_code = 'product';

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

        $this->getRequest()->createTableFromFile($this->getCode(), $file, 3, false);

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
                Mage::helper('pimgento_position')->__(
                    'No data to insert, verify the file is not empty or CSV configuration is correct'
                )
            );
        }

        $task->setMessage(
            Mage::helper('pimgento_position')->__('%s lines found', $lines)
        );

        return true;
    }

    /**
     * Insert data (Step 3)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateColumn($task)
    {
        $adapter = $this->getAdapter();

        if (!$this->columnsRequired(array('sku'), $task)) {
            $task->error(
                Mage::helper('pimgento_position')->__('Column %s not found', 'sku')
            );
        }

        $adapter->changeColumn($this->getTable(), 'sku', 'code', 'VARCHAR(255)');

        return true;
    }

    /**
     * Match Entity with Code (Step 4)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function matchEntity($task)
    {
        $this->getRequest()->matchEntity($this->getCode(), 'catalog/product', 'entity_id', null, false);

        return true;
    }

    /**
     * Update categories (Step 5)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateCategory($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        if (!$this->columnsRequired(array('category'), $task)) {
            return false;
        }

        /* @var $category Pimgento_Category_Model_Import */
        $category = Mage::getModel('pimgento_category/import');

        $select = $adapter->select()
            ->from(false, array())
            ->joinInner(
                array('c' => $resource->getTable('pimgento_core/code')),
                'p.category = c.code AND c.import = "' . $category->getCode() . '"',
                array(
                    'category' => 'c.entity_id'
                )
            );

        $adapter->query(
            $adapter->updateFromSelect($select, array('p' => $this->getTable()))
        );

        return true;
    }

    /**
     * Update categories (Step 6)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updatePosition($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        if (!$this->columnsRequired(array('category', 'position'), $task)) {
            return false;
        }

        $select = $adapter->select()
            ->from(
                array('c' => $this->getTable()),
                array(
                      'category_id' => 'category',
                      'product_id'  => 'entity_id',
                      'position'    => 'position',
                )
            )
            ->joinInner(
                array('e' => $resource->getTable('catalog/category')),
                'c.category = e.entity_id',
                array()
            );

        $adapter->query(
            $adapter->insertFromSelect($select, $resource->getTable('catalog/category_product'), array(), 1)
        );

        return true;
    }

    /**
     * Drop table (Step 7)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function dropTable($task)
    {
        $this->getRequest()->dropTable($this->getCode());

        return true;
    }

    /**
     * Reindex (Step 8)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function reindex($task)
    {
        if (!$this->getConfig('reindex')) {
            $task->setMessage(
                Mage::helper('pimgento_position')->__('Reindex is disabled')
            );
            return false;
        }

        /* @var $indexer Mage_Index_Model_Indexer */
        $indexer = Mage::getSingleton('index/indexer');

        Mage::dispatchEvent('shell_reindex_init_process');

        $processes = array(
            'catalog_category_product',
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

}