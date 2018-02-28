<?php

/**
 * Class Pimgento_VariantFamily_Model_Import
 *
 * @category  Class
 * @package   Pimgento_VariantFamily_Model_Import
 * @author    Pierre Barbarin <pierre.barbarin@dnd.fr>
 * @copyright Copyright (c) 2018 Agence Dn'D
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.dnd.fr/
 */

class Pimgento_VariantFamily_Model_Import extends Pimgento_Core_Model_Import_Abstract
{

    /**
     * @var string $_code
     */
    protected $_code = 'variantfamily';

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
        /** @var string $file */
        $file = $task->getFile();
        /** @var int $lines */
        $lines = $this->getRequest()->insertDataFromFile($this->getCode(), $file);

        if (!$lines) {
            $task->error(Mage::helper('pimgento_variantfamily')->__('No data to insert, verify the file is not empty or CSV configuration is correct'));
        }

        $task->setMessage(Mage::helper('pimgento_variantfamily')->__('%s lines found', $lines));

        return true;
    }

    /**
     * Insert data into TemporaryTable(Step 3)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     * @throws Exception
     */
    public function updateAxis($task)
    {
        /** @var Varien_Db_Adapter_Interface $adapter */
        $adapter = $this->getAdapter();
        /** @var string $temporaryTable */
        $temporaryTable = $this->getTable();

        $adapter->addColumn($temporaryTable, '_axis', [
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 255,
            'comment' => 'axis second'
        ]);

        /** @var array $columns */
        $columns = [];
        /** @var int $maxAxesNumber */
        $maxAxesNumber = Mage::helper('pimgento_variantfamily/config')->getMaxAxesNumber();

        for ($i = 1; $i <= $maxAxesNumber; $i++) {
            $columns[] = 'variant-axes_' . $i;
        }

        foreach ($columns as $key => $column) {
            if (!$adapter->tableColumnExists($temporaryTable, $column)) {
                unset($columns[$key]);
            }
        }

        if (!empty($columns)) {
            /** @var string $axesColumns */
            $axesColumns = join('`, "," ,`', $columns);
            /** @var string $updateExpression */
            $updateExpression = 'TRIM(BOTH "," FROM CONCAT(`' . $axesColumns . '`))';

            $adapter->update($temporaryTable, ['_axis' => new Zend_Db_Expr($updateExpression)]);
        }

        /** @var Zend_Db_Statement_Interface $variantFamily */
        $variantFamily = $adapter->query($adapter->select()->from($temporaryTable));
        /** @var string $eavTable */
        $eavTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute');
        /** @var array $attributes */
        $attributes = $adapter->fetchPairs($adapter->select()->from($eavTable, [
            'attribute_code',
            'attribute_id'
        ])->where('entity_type_id = ?', 4));

        while (($row = $variantFamily->fetch())) {
            /** @var array $axisAttributes */
            $axisAttributes = explode(',', $row['_axis']);
            /** @var array $axis */
            $axis = [];

            foreach ($axisAttributes as $axisAttribute) {
                if (isset($attributes[$axisAttribute])) {
                    $axis[] = $attributes[$axisAttribute];
                }
            }

            /** @var string $axisUpdate */
            $axisUpdate = join(',', $axis);

            $adapter->update($temporaryTable, ['_axis' => $axisUpdate], ['code = ?' => $row['code']]);
        }

        return true;
    }

    public function updateProductModel()
    {
        /** @var Varien_Db_Adapter_Interface $adapter */
        $adapter = $this->getAdapter();
        /** @var string $temporaryTable */
        $temporaryTable = $this->getTable();
        /** @var string $variantTable */
        $variantTable = Mage::getSingleton('core/resource')->getTableName('pimgento_variant');
        /** @var Varien_Db_Select $query */
        $query = $adapter->select();

        $query->from(false, ['axis' => 'f._axis']);
        $query->joinLeft(['f' => $temporaryTable], 'p.family_variant = f.code', []);
        $adapter->query($adapter->updateFromSelect($query, ['p' => $variantTable]));
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

        Mage::dispatchEvent('task_executor_drop_table_after', ['task' => $task]);

        return true;
    }
}
