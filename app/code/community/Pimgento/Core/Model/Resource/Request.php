<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Model_Resource_Request extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('pimgento_core/request', false);
    }

    /**
     * Create Table
     *
     * @param string $name
     * @param array  $fields
     * @param int    $columns
     * @param bool   $unique
     *
     * @return $this;
     * @throw Mage_Core_Exception
     */
    public function createTable($name, $fields, $columns = null, $unique = true)
    {
        $adapter = $this->_getWriteAdapter();

        $adapter->resetDdlCache($this->getTableName($name));
        $adapter->dropTable($this->getTableName($name));

        if (is_numeric($columns)) {
            if (count($fields) != $columns) {
                Mage::throwException(
                    Mage::helper('pimgento_core')->__('%s columns excepted, %s given', $columns, count($fields))
                );
            }
        }

        $table = $adapter->newTable($this->getTableName($name));
        foreach ($fields as $field) {
            $table->addColumn(
                $this->_formatField($field),
                Varien_Db_Ddl_Table::TYPE_LONGVARCHAR,
                null,
                array(),
                $this->_formatField($field)
            );
        }
        $table->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            11,
            array(),
            'entity_id'
        );
        $table->addColumn(
            '_is_new',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            1,
            array('default' => 0),
            '_is_new'
        );
        if ($unique) {
            $table->addIndex(
                'UNQ_PIMGENTO_CODE_' . strtoupper($name) . '_ENTITY_ID', array('entity_id'), array('type' => 'UNIQUE')
            );
        }

        $table->setOption('type', 'MYISAM');

        $adapter->createTable($table);

        return $this;
    }

    /**
     * Drop Table
     *
     * @param string $name
     *
     * @return $this;
     */
    public function dropTable($name)
    {
        $adapter = $this->_getWriteAdapter();

        $adapter->dropTable($this->getTableName($name));

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
        $adapter = $this->_getWriteAdapter();

        $adapter->delete($this->getTableName($name), array('code = ?' => ''));

        $adapter->query('
            UPDATE `' . $this->getTableName($name) . '` t
            SET `entity_id` = (
                SELECT `entity_id` FROM `' . $this->getTable('pimgento_core/code') . '` c
                WHERE ' . ($prefix ? 'CONCAT(t.`' . $prefix . '`,"_",t.`code`)' : 't.`code`') . ' = c.`code`
                    AND c.`import` = "' . $name . '"
            )
        ');

        if ($create) {

            $adapter->query('SET @id = ' . $this->_getIncrementId($entity));

            $values = array(
                'entity_id' => new Zend_Db_Expr('@id := @id + 1'),
                '_is_new'   => new Zend_Db_Expr('1'),
            );
            $adapter->update($this->getTableName($name), $values, 'entity_id IS NULL');

            $select = $adapter->select()
                ->from(
                    $this->getTableName($name),
                    array(
                        'import'     => new Zend_Db_Expr("'" . $name . "'"),
                        'code'       => $prefix ? new Zend_Db_Expr('CONCAT(`' . $prefix . '`,"_", `code`)') : 'code',
                        'entity_id'  => 'entity_id'
                    )
                );

            $insert = $adapter->insertFromSelect(
                $select,
                $this->getTable('pimgento_core/code'),
                array('import', 'code', 'entity_id'),
                2
            );

            $adapter->query($insert);

            $maxCode = $adapter->fetchOne('
                SELECT MAX(`entity_id`) FROM `' . $this->getTable('pimgento_core/code') . '` WHERE `import` = "' . $name . '"
            ');

            $maxEntity = $adapter->fetchOne('
                SELECT MAX(`' . $primaryKey . '`) FROM `' . $this->getTable($entity) . '`
            ');

            $adapter->changeTableAutoIncrement($this->getTable($entity), max((int) $maxCode, (int) $maxEntity) + 1);

        } else {

            $adapter->delete($this->getTableName($name), 'entity_id IS NULL');

        }

        return $this;
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
        $adapter = $this->_getWriteAdapter();

        foreach ($values as $code => $value) {

            if (($attribute = $this->getAttribute($code, $entityTypeId))) {

                if ($attribute['backend_type'] !== 'static') {

                    $select = $adapter->select()
                        ->from(
                            $this->getTableName($name),
                            array(
                                'entity_type_id' => new Zend_Db_Expr($entityTypeId),
                                'attribute_id'   => new Zend_Db_Expr($attribute['attribute_id']),
                                'store_id'       => new Zend_Db_Expr($storeId),
                                'entity_id'      => 'entity_id',
                                'value'          => $value
                            )
                        );

                    if ($this->columnExists($this->getTableName($name), $value)) {
                        $select->where('TRIM(`' . $value . '`) <> ?', new Zend_Db_Expr('""'));
                    }

                    $backendType = $attribute['backend_type'];

                    if ($code == 'url_key' && Mage::getEdition() == Mage::EDITION_ENTERPRISE) {
                        $backendType = 'url_key';
                    }

                    $insert = $adapter->insertFromSelect(
                        $select,
                        $this->getValueTable($entity, $backendType),
                        array('entity_type_id', 'attribute_id', 'store_id', 'entity_id', 'value'),
                        $mode
                    );

                    $adapter->query($insert);

                    if ($attribute['backend_type'] == 'datetime') {
                        $values = array(
                            'value' => new Zend_Db_Expr('NULL'),
                        );
                        $where = array(
                            'value = ?' => '0000-00-00 00:00:00'
                        );
                        $adapter->update($this->getValueTable($entity, $backendType), $values, $where);
                    }

                }

            }

        }

        return $this;
    }

    /**
     * Execute Load data infile
     *
     * @param $name
     * @param $file
     *
     * @return int
     */
    public function loadDataInfile($name, $file)
    {
        $loadInfile = Mage::getStoreConfig('pimdata/general/csv_load_data_infile');
        if (!$loadInfile) {
            $this->loadData($name, $file);
        } else {
            $fieldsTerminated = Mage::getStoreConfig('pimdata/general/csv_fields_terminated');
            $linesTerminated = Mage::getStoreConfig('pimdata/general/csv_lines_terminated');

            $query = "LOAD DATA INFILE '" . addslashes($file) . "' REPLACE
                  INTO TABLE " . $this->getTableName($name) . "
                  FIELDS TERMINATED BY '" . $fieldsTerminated . "'
                  OPTIONALLY ENCLOSED BY '\"'
                  LINES TERMINATED BY '" . $linesTerminated . "'
                  IGNORE 1 LINES;";

            $this->_query($query, array(PDO::MYSQL_ATTR_LOCAL_INFILE => 1));
        }
        $adapter = $this->_getReadAdapter();

        return $adapter->fetchOne(
            $adapter->select()
                ->from(
                    $this->getTableName($name),
                    array('count' => new Zend_Db_Expr('COUNT(*)'))
                )
        );
    }

    /**
     * Read in a file and process the lines.
     *
     * @param $name - destination table name
     * @param $file - csv file holding data
     *
     * @throws Exception
     *
     * @return int - rows imported
     */
    public function loadData($name, $file)
    {
        $file_handle = new SplFileObject($file);

        if (!$file_handle->isFile()) {
            $message = sprintf("%s is not a file.", $file);
            throw new Exception($message);
        }

        if (!$file_handle->isReadable()) {
            $message = sprintf("Unable to read %s.", $file);
            throw new Exception($message);
        }

        if ($file_handle->getSize() == 0) {
            return;
        }

        $fieldsTerminated = Mage::getStoreConfig('pimdata/general/csv_fields_terminated');

        $columnNames = [];

        $adapter = $this->_getWriteAdapter();
        $table = $this->getTableName($name);

        $row_count = 0;

        while (!$file_handle->eof()) {
            $csv_line = $file_handle->fgetcsv($fieldsTerminated);

            if (++$row_count == 1) {
                # Get column names as first row - assumes first row always has this data
                foreach ($csv_line as $key => $value) {
                    if ($value) {
                        array_push($columnNames, $value);
                    }
                }
                continue;
            }

            # Build column => value map for insert
            $columnValues = [];
            foreach ($csv_line as $key => $value) {
                if (isset($columnNames[$key])) {
                    $columnValues[$columnNames[$key]] = $value;
                }
            }

            # Insert our row into the tmp table
            $adapter->insert($table, $columnValues);
        }
    }

    /**
     * Retrieve table name
     *
     * @param string $name
     *
     * @return string
     */
    public function getTableName($name)
    {
        /* @var $coreResource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');

        return $resource->getTableName('tmp_pimgento_core_' . $name);
    }

    /**
     * Retrieve Adapter
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getAdapter()
    {
        return $this->_getWriteAdapter();
    }

    /**
     * Retrieve attribute
     *
     * @param string $code
     * @param int    $entityTypeId
     *
     * @return bool|array
     */
    public function getAttribute($code, $entityTypeId)
    {
        $adapter = $this->_getReadAdapter();

        $attribute = $adapter->fetchRow(
            $adapter->select()
                ->from($this->getTable('eav/attribute'), array('attribute_id', 'backend_type'))
                ->where('entity_type_id = ?', $entityTypeId)
                ->where('attribute_code = ?', $code)
                ->limit(1)
        );

        return count($attribute) ? $attribute : false;
    }

    /**
     * Retrieve codes
     *
     * @param string $import
     *
     * @return array
     */
    public function getCodes($import = null)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from($this->getTable('pimgento_core/code'), array('code', 'entity_id'));

        if ($import) {
            $select->where('import = ?', $import);
        }

        return $adapter->fetchPairs($select);
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
        $adapter = $this->_getReadAdapter();

        return $adapter->tableColumnExists($table, $column);
    }

    /**
     * Retrieve next entity id from entity table
     *
     * @param string $entity
     *
     * @return int
     */
    protected function _getIncrementId($entity)
    {
        $adapter = $this->_getReadAdapter();

        $result = $adapter->query('SHOW TABLE STATUS LIKE "' . $this->getTable($entity) . '"');
        $row = $result->fetch();

        return (int) $row['Auto_increment'] + 1;
    }

    /**
     * Format flied name
     *
     * @param string $field
     *
     * @return string
     */
    protected function _formatField($field)
    {
        return trim(str_replace(PHP_EOL, '', preg_replace('/\s+/', ' ', trim($field))),'"');
    }

    /**
     * Execute Query from command line
     *
     * @param string $query
     * @param array $options
     *
     * @return $this
     * @throws Exception
     */
    protected function _query($query, $options = array())
    {
        /* @var $connConfig Mage_Core_Model_Config_Element */
        $connConfig = Mage::getConfig()->getResourceConnectionConfig('core_write');

        if (!$connConfig || !$connConfig->is('active', 1)) {
            throw new Exception(Mage::helper('pimgento_core')->__('Connection to database is not active'));
        }

        $host = explode(':', $connConfig->host);
        $dsn  = 'mysql:host=' . $host[0] . ';dbname=' . $connConfig->dbname;
        if (isset($host[1])) {
            $dsn .= ';port=' . $host[1];
        }
        
        $pdo = new PDO($dsn, $connConfig->username, $connConfig->password, $options);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec($query);

        return $this;
    }

}
