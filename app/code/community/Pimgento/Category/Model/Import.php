<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Category_Model_Import extends Pimgento_Core_Model_Import_Abstract
{

    /**
     * @var string
     */
    protected $_code = 'category';

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
                Mage::helper('pimgento_category')->__(
                    'No data to insert, verify the file is not empty or CSV configuration is correct'
                )
            );
        }

        $this->deleteExclusion();

        $task->setMessage(
            Mage::helper('pimgento_category')->__('%s lines found', $lines)
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
        $this->getRequest()->matchEntity($this->getCode(), 'catalog/category', 'entity_id');

        return true;
    }

    /**
     * Set Categories level (Step 4)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function setLevel($task)
    {
        $adapter = $this->getAdapter();

        $adapter->addColumn($this->getTable(), 'level', 'INT(11) NOT NULL DEFAULT 0');
        $adapter->addColumn($this->getTable(), 'path', 'VARCHAR(255) NOT NULL DEFAULT ""');
        $adapter->addColumn($this->getTable(), 'parent_id', 'INT(11) NOT NULL DEFAULT 0');

        $values = array(
            'level'     => 1,
            'path'      => $this->_zde('CONCAT(1,"/",`entity_id`)'),
            'parent_id' => 1,
        );
        $adapter->update($this->getTable(), $values, 'parent = ""');

        $depth = $this->getConfig('depth');

        for ($i = 1; $i <= $depth; $i++) {

            $adapter->query('
                UPDATE `' . $this->getTable() . '` c1
                INNER JOIN `' . $this->getTable() . '` c2 ON c2.`code` = c1.`parent`
                SET c1.`level` = c2.`level` + 1,
                    c1.`path` = CONCAT(c2.`path`,"/",c1.`entity_id`),
                    c1.`parent_id` = c2.`entity_id`
                WHERE c1.`level` <= c2.`level` - 1
            ');

        }

        $task->setMessage(
            Mage::helper('pimgento_category')->__('Max category depth in configuration is %s', $depth)
        );

        return true;
    }

    /**
     * Set categories position (Step 5)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function setPosition($task)
    {
        $adapter = $this->getAdapter();

        $adapter->addColumn($this->getTable(), 'position', 'INT(11) NOT NULL DEFAULT 0');

        $query = $adapter->query(
            $adapter->select()
            ->from(
                $this->getTable(),
                array(
                    'entity_id' => 'entity_id',
                    'parent_id' => 'parent_id',
                )
            )
        );

        while (($row = $query->fetch())) {

            $position = $adapter->fetchOne(
                $adapter->select()
                ->from(
                    $this->getTable(),
                    array(
                        'position' => $this->_zde('MAX(`position`) + 1')
                    )
                )
                ->where('parent_id = ?', $row['parent_id'])
                ->group('parent_id')
            );

            $values = array(
                'position' => $position
            );

            $adapter->update($this->getTable(), $values, array('entity_id = ?' => $row['entity_id']));

        }

        return true;
    }

    /**
     * Create category entities (Step 6)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function createEntities($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        $values = array(
            'entity_id'        => 'entity_id',
            'entity_type_id'   => $this->_zde(3),
            'attribute_set_id' => $this->_zde(3),
            'parent_id'        => 'parent_id',
            'updated_at'       => $this->_zde('now()'),
            'path'             => 'path',
            'position'         => 'position',
            'level'            => 'level',
            'children_count'   => $this->_zde('0'),
        );

        $parents = $adapter->select()->from($this->getTable(), $values);

        $adapter->query(
            $adapter->insertFromSelect(
                $parents,
                $resource->getTable('catalog/category'),
                array_keys($values),
                Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
            )
        );

        $values = array(
            'created_at' => $this->_zde('now()')
        );
        $adapter->update($resource->getTable('catalog/category'), $values, 'created_at IS NULL');

        return true;
    }

    /**
     * Set values to attributes (Step 7)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function setValues($task)
    {
        $values = array(
            'is_active'       => $this->_zde(1),
            'include_in_menu' => $this->_zde(1),
            'is_anchor'       => $this->_zde($this->getConfig('anchor')),
            'display_mode'    => $this->_zde('"PRODUCTS"'),
        );

        // Do not update
        $this->getRequest()->setValues($this->getCode(), 'catalog/category', $values, 3, 0, 2);

        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        $stores = $helper->getStoresLang();

        foreach ($stores as $local => $ids) {

            if ($this->getRequest()->columnExists($this->getTable(), 'label-' . $local)) {

                foreach ($ids as $storeId) {

                    $values = array(
                        'name' => 'label-' . $local,
                    );

                    $this->getRequest()->setValues($this->getCode(), 'catalog/category', $values, 3, $storeId);
                }

            }

        }

        return true;
    }

    /**
     * Update Children Count (Step 8)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateChildrenCount($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        $adapter->query('
        UPDATE `' . $resource->getTable('catalog/category') . '` c SET `children_count` = (
            SELECT COUNT(`parent_id`) FROM (
                SELECT * FROM `' . $resource->getTable('catalog/category') . '`
            ) tmp
            WHERE tmp.`path` LIKE CONCAT(c.`path`,\'/%\')
        )');

        return true;
    }

    /**
     * Update Url Keys (Step 9)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateUrl($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        $attribute = $resource->getAttribute('url_key', 3);

        /* @var $url Mage_Catalog_Model_Product_Url */
        $url = Mage::getModel('catalog/product_url');

        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        $stores = $helper->getStoresLang();

        foreach ($stores as $local => $ids) {

            if ($this->getRequest()->columnExists($this->getTable(), 'label-' . $local)) {

                foreach ($ids as $storeId) {

                    $select = $adapter->select()
                        ->from($this->getTable(), array('name' => 'label-' . $local, 'entity_id'));

                    $query = $adapter->query($select);

                    while (($row = $query->fetch())) {

                        $new = $url->formatUrlKey($row['name']);

                        $values = array(
                            'entity_type_id' => $this->_zde(3),
                            'attribute_id'   => $this->_zde($attribute['attribute_id']),
                            'store_id'       => $this->_zde($storeId),
                            'entity_id'      => $row['entity_id'],
                            'value'          => $new
                        );

                        $table = $resource->getValueTable('catalog/category', $attribute['backend_type']);

                        $current = false;

                        if ($this->isEnterprise()) {
                            $table = $resource->getValueTable('catalog/category', 'url_key');

                            $current = $adapter->fetchOne(
                                $adapter->select()
                                    ->from($table, array('value'))
                                    ->where('entity_id = ?', $row['entity_id'])
                                    ->where('attribute_id = ?', $attribute['attribute_id'])
                                    ->where('store_id = ?', $this->_zde($storeId))
                                    ->limit(1)
                            );
                        }

                        if ($this->getConfig('update_url')) {

                            $adapter->insertOnDuplicate($table, $values);

                            if ($this->isEnterprise()) {
                                if ($current) {
                                    if ($current != $new) {
                                        /* @var $factory Mage_Core_Model_Factory */
                                        $factory = Mage::getSingleton('core/factory');

                                        /* @var $redirect Enterprise_Catalog_Model_Category_Redirect */
                                        $redirect = $factory->getModel('enterprise_catalog/category_redirect');

                                        /* @var $category Mage_Catalog_Model_Category */
                                        $category = Mage::getModel('catalog/category');
                                        $category->setId($row['entity_id']);

                                        $adapter->dropTemporaryTable(
                                            Enterprise_Catalog_Model_Category_Redirect::TMP_TABLE_NAME
                                        );

                                        $redirect->saveCustomRedirects($category, $storeId);
                                    }
                                }
                            }

                        } else {
                            $adapter->insertIgnore($table, $values);
                        }

                    }

                }

            }

        }

        return true;
    }

    /**
     * Drop table (Step 10)
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
     * Reindex (Step 11)
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
                Mage::helper('pimgento_category')->__('Reindex is disabled')
            );
            return false;
        }

        /* @var $indexer Mage_Index_Model_Indexer */
        $indexer = Mage::getSingleton('index/indexer');

        Mage::dispatchEvent('shell_reindex_init_process');

        if ($this->isEnterprise()) {
            $processes = array(
                'catalog_category_flat',
                'catalog_url_category',
                'url_redirect',
                'catalog_category_product',
            );
        } else {
            $processes = array(
                'catalog_category_flat',
                'catalog_url',
                'catalog_category_product',
            );
        }

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