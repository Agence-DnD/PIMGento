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
                Mage::helper('pimgento_variant')->__(
                    'No data to insert, verify the file is not empty or CSV configuration is correct'
                )
            );
        }

        $task->setMessage(
            Mage::helper('pimgento_variant')->__('%s lines found', $lines)
        );

        return true;
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
        $adapter  = $this->getAdapter();

        $column = $this->columnExists('axis') ? 'axis' : 'attributes';

        $select = $adapter->select()
            ->from(
                $this->getTable(),
                array('code', $column)
            );

        $insert = $adapter->insertFromSelect(
            $select,
            $adapter->getTableName('pimgento_variant'),
            array('code', 'axis'),
            1
        );

        $adapter->query($insert);

        $attributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter(4)
            ->addFieldToFilter('is_configurable', 1);

        $attributes->getSelect()->order("LENGTH(attribute_code) DESC");

        foreach ($attributes as $attribute) {
            $values = array(
                'axis' => $this->_zde(
                    'REPLACE(axis, "' . $attribute->getAttributeCode() . '", "' . $attribute->getAttributeId() . '")'
                )
            );
            $adapter->update($adapter->getTableName('pimgento_variant'), $values);
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

        return true;
    }

}