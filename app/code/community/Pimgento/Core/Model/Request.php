<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Model_Request extends Mage_Core_Model_Abstract
{

    /**
     * Initialize
     */
    public function _construct()
    {
        $this->_init('pimgento_core/request');
    }

    /**
     * Retrieve Adapter
     *
     * @return Pimgento_Core_Model_Resource_Request
     */
    public function getResource()
    {
        return $this->_getResource();
    }

    /**
     * Set values to attributes
     *
     * @param string $name
     * @param string $entity
     * @param array  $values
     * @param int    $entityTypeId
     * @param int    $storeId
     * @param int    $mode
     *
     * @return $this
     */
    public function setValues($name, $entity, $values, $entityTypeId, $storeId, $mode = 1)
    {
        /* @var $resource Pimgento_Core_Model_Resource_Request */
        $resource = $this->_getResource();

        $resource->setValues($name, $entity, $values, $entityTypeId, $storeId, $mode);

        return $this;
    }

    /**
     * Create table
     *
     * @param string $name
     * @param array  $fields
     * @param int    $columns
     * @param bool   $unique
     *
     * @return $this
     */
    public function createTable($name, $fields, $columns = null, $unique = true)
    {
        /* @var $resource Pimgento_Core_Model_Resource_Request */
        $resource = $this->_getResource();

        $resource->createTable($name, $fields, $columns, $unique);

        return $this;
    }

    /**
     * Drop table
     *
     * @param string $name
     *
     * @return $this
     */
    public function dropTable($name)
    {
        /* @var $resource Pimgento_Core_Model_Resource_Request */
        $resource = $this->_getResource();

        $resource->dropTable($name);

        return $this;
    }

    /**
     * Match entity with code
     *
     * @param string $name
     * @param string $entity
     * @param string $primaryKey
     * @param string $prefix
     * @param bool   $create
     *
     * @return $this;
     */
    public function matchEntity($name, $entity, $primaryKey, $prefix = null, $create = true)
    {
        /* @var $resource Pimgento_Core_Model_Resource_Request */
        $resource = $this->_getResource();

        $resource->matchEntity($name, $entity, $primaryKey, $prefix, $create);

        return $this;
    }

    /**
     * Insert data from file
     *
     * @param string $name
     * @param string $file
     *
     * @return int
     */
    public function insertDataFromFile($name, $file)
    {
        /* @var $resource Pimgento_Core_Model_Resource_Request */
        $resource = $this->_getResource();

        return (int)$resource->loadDataInfile($name, $file);
    }

    /**
     * Create table from file
     *
     * @param string $name
     * @param string $file
     * @param int    $columns
     * @param bool   $unique
     *
     * @return $this
     */
    public function createTableFromFile($name, $file, $columns = null, $unique = true)
    {
        $this->createTable($name, $this->getFirstLine($file), $columns, $unique);

        return $this;
    }

    /**
     * Check if column exists
     *
     * @param string $table
     * @param string $column
     *
     * @return bool
     */
    public function columnExists($table, $column)
    {
        /* @var $resource Pimgento_Core_Model_Resource_Request */
        $resource = $this->_getResource();

        return $resource->columnExists($table, $column);
    }

    /**
     * Retrieve codes
     *
     * @param $import
     *
     * @return array
     */
    public function getCodes($import = null)
    {
        /* @var $resource Pimgento_Core_Model_Resource_Request */
        $resource = $this->_getResource();

        return $resource->getCodes($import);
    }

    /**
     * Retrieve First line of file
     *
     * @param $file
     *
     * @return array
     */
    public function getFirstLine($file)
    {
        $handle = fopen($file, 'r');
        $line = preg_replace("/\\r\\n/", "", fgets($handle));
        fclose($handle);

        $fieldsTerminated = Mage::getStoreConfig('pimdata/general/csv_fields_terminated');

        return explode($fieldsTerminated, $line);
    }

}