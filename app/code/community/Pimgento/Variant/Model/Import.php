<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Variant_Model_Import extends Pimgento_Core_Model_Import_Abstract
{

    /**
     * @var string
     */
    protected $_code = 'variant';

    /**
     * Create table (Step 1)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function createTable($task)
    {
        /** @var string $file */
        $file = $task->getFile();

        $this->getRequest()->createTableFromFile($this->getCode(), $file, ['code']);

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
        /** @var string $file */
        $file = $task->getFile();
        /** @var int $lines */
        $lines = $this->getRequest()->insertDataFromFile($this->getCode(), $file);

        if (!$lines) {
            $task->error(Mage::helper('pimgento_variant')->__('No data to insert, verify the file is not empty or CSV configuration is correct'));
        }

        $task->setMessage(Mage::helper('pimgento_variant')->__('%s lines found', $lines));

        return true;
    }

    /**
     * Remove columns from variant table
     */
    public function removeColumns()
    {
        /** @var Varien_Db_Adapter_Interface $adapter */
        $adapter = $this->getAdapter();
        /** @var array $except */
        $except = [
            'code',
            'axis'
        ];
        /** @var string $variantTable */
        $variantTable = Mage::getSingleton('core/resource')->getTableName('pimgento_variant');

        $columns = array_keys($adapter->describeTable($variantTable));

        foreach ($columns as $column) {
            if (in_array($column, $except)) {
                continue;
            }

            $adapter->dropColumn($variantTable, $column);
        }
    }

    /**
     * Add columns to variant table
     */
    public function addColumns()
    {
        /** @var Varien_Db_Adapter_Interface $adapter */
        $adapter = $this->getAdapter();
        /** @var string $temporaryTable */
        $temporaryTable = $this->getTable();
        /** @var array $except */
        $except = [
            'code',
            'axis',
            'type',
            '_entity_id',
            '_is_new'
        ];
        /** @var string $variantTable */
        $variantTable = Mage::getSingleton('core/resource')->getTableName('pimgento_variant');
        /** @var array $columns */
        $columns = array_keys($adapter->describeTable($temporaryTable));

        foreach ($columns as $column) {
            if (in_array($column, $except)) {
                continue;
            }

            $adapter->addColumn($variantTable, $this->_columnName($column), 'TEXT');
        }

        if (!$adapter->tableColumnExists($temporaryTable, 'axis')) {
            $adapter->addColumn($temporaryTable, 'axis', 'VARCHAR(255)');
        }
    }

    /**
     * Insert data (Step 3)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     * @throws Exception
     */
    public function updateTable($task)
    {
        /** @var Varien_Db_Adapter_Interface $adapter */
        $adapter = $this->getAdapter();
        /** @var string $variantTable */
        $variantTable = Mage::getSingleton('core/resource')->getTableName('pimgento_variant');
        /** @var  $temporaryTable */
        $temporaryTable = $this->getTable();
        /** @var string $eavTable */
        $eavTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute');
        /** @var Zend_Db_Statement_Interface $variant */
        $variant = $adapter->query($adapter->select()->from($temporaryTable));
        /** @var array $attributes */
        $attributes = $adapter->fetchPairs($adapter->select()->from($eavTable, [
            'attribute_code',
            'attribute_id'
        ])->where('entity_type_id = ?', 4));
        /** @var array $columns */
        $columns = array_keys($adapter->describeTable($temporaryTable));
        /** @var array $values */
        $values = [];
        /** @var int $i */
        $i = 0;
        /** @var array $keys */
        $keys = [];

        while ($row = $variant->fetch()) {

            $values[$i] = [];

            foreach ($columns as $column) {
                if ($adapter->tableColumnExists($variantTable, $this->_columnName($column))) {
                    if ($column != 'axis') {
                        $values[$i][$this->_columnName($column)] = $row[$column];
                    }

                    if ($column == 'axis' && !$adapter->tableColumnExists($temporaryTable, 'family_variant')) {
                        $axisAttributes = explode(',', $row['axis']);

                        $axis = array();

                        foreach ($axisAttributes as $code) {
                            if (isset($attributes[$code])) {
                                $axis[] = $attributes[$code];
                            }
                        }

                        $values[$i][$column] = join(',', $axis);
                    }

                    $keys = array_keys($values[$i]);
                }
            }
            $i++;

            /**
             * Write 500 values at a time.
             */
            if (count($values) > 500) {
                $adapter->insertOnDuplicate($variantTable, $values, $keys);
                $values = [];
                $i      = 0;
            }
        }

        if (count($values) > 0) {
            $adapter->insertOnDuplicate($variantTable, $values, $keys);
        }

        return true;
    }

    /**
     * Drop table (Step 4)
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
     * Replace column name
     *
     * @param string $column
     *
     * @return string
     */
    protected function _columnName($column)
    {
        $matches = array(
            'label' => 'name',
        );

        foreach ($matches as $name => $replace) {
            if (preg_match('/^' . $name . '/', $column)) {
                $column = preg_replace('/^' . $name . '/', $replace, $column);
            }
        }

        return $column;
    }
}
