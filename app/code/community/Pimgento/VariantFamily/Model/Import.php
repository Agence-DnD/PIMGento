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
     * @var int
     */
    protected $_productEntityTypeId = null;

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
                Mage::helper('pimgento_variantfamily')->__(
                    'No data to insert, verify the file is not empty or CSV configuration is correct'
                )
            );
        }

        $task->setMessage(Mage::helper('pimgento_variantfamily')->__('%s lines found', $lines));

        return true;
    }

    /**
     * Insert data into TemporaryTable (Step 3)
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

        $temporaryTable = $this->getTable();

        $adapter->addColumn($temporaryTable, '_axis', [
            'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'  => 255,
            'comment' => 'axis second'
        ]);


        $columns = [];

        $maxAxesNumber = Mage::helper('pimgento_variantfamily')->getMaxAxesNumber();

        for ($i = 1; $i <= $maxAxesNumber; $i++) {
            $columns[] = 'variant-axes_' . $i;
        }

        foreach ($columns as $key => $column) {
            if (!$adapter->tableColumnExists($temporaryTable, $column)) {
                unset($columns[$key]);
            }
        }

        if (!empty($columns)) {
            $axesColumns = join('`, "," ,`', $columns);

            $updateExpression = 'TRIM(BOTH "," FROM CONCAT(`' . $axesColumns . '`))';

            $adapter->update($temporaryTable, ['_axis' => new Zend_Db_Expr($updateExpression)]);
        }

        /** @var Zend_Db_Statement_Interface $variantFamily */
        $variantFamily = $adapter->query($adapter->select()->from($temporaryTable));

        $eavTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute');

        $attributes = $adapter->fetchPairs($adapter->select()->from($eavTable, [
            'attribute_code',
            'attribute_id'
        ])->where('entity_type_id = ?', $this->getProductEntityTypeId()));

        while (($row = $variantFamily->fetch())) {
            $axisAttributes = explode(',', $row['_axis']);

            $axis = [];

            foreach ($axisAttributes as $axisAttribute) {
                if (isset($attributes[$axisAttribute])) {
                    $axis[] = $attributes[$axisAttribute];

                    /** @var Mage_Eav_Model_Attribute $attributeModel */
                    $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode(
                        $this->getProductEntityTypeId(),
                        $axisAttribute
                    );
                    if ($attributeModel->hasData()) {
                        $attributeModel->setData('is_configurable', 1);
                        $attributeModel->save();
                    }
                }
            }

            $axisUpdate = join(',', $axis);

            $adapter->update($temporaryTable, ['_axis' => $axisUpdate], ['code = ?' => $row['code']]);
        }

        return true;
    }

    /**
     * Update Product model (Step 4)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateProductModel($task)
    {
        /** @var Varien_Db_Adapter_Interface $adapter */
        $adapter = $this->getAdapter();

        $temporaryTable = $this->getTable();

        $variantTable = Mage::getSingleton('core/resource')->getTableName('pimgento_variant');

        $query = $adapter->select();

        $query->from(false, ['axis' => 'f._axis']);
        $query->joinLeft(['f' => $temporaryTable], 'p.family_variant = f.code', []);
        $adapter->query($adapter->updateFromSelect($query, ['p' => $variantTable]));

        return true;
    }

    /**
     * Drop table (Step 5)
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

    /**
     * get product entity type id
     *
     * @return int
     */
    public function getProductEntityTypeId()
    {
        if ($this->_productEntityTypeId === NULL) {
            $this->_productEntityTypeId = Mage::helper('pimgento_core')->getProductEntityTypeId();
        }
        return $this->_productEntityTypeId;
    }
}
