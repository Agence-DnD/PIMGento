<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

abstract class Pimgento_Core_Model_Import_Abstract
{

    /**
     * @var string
     */
    protected $_code;

    /**
     * @var array
     */
    protected $_entity_type_ids;

    /**
     * Constructor
     */
    public function _construct()
    {
        if (!$this->getCode()) {
            throw new Exception(
                Mage::helper('pimgento_core')->__('%s must have an import code', get_class($this))
            );
        }
    }

    /**
     * Retrieve Import code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Clean Cache
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function cleanCache($task)
    {
        $tags = $this->getConfig('cache');
        if ($tags) {
            Mage::app()->cleanCache(explode(',', $tags));

            $task->setMessage(
                Mage::helper('pimgento_core')->__('Cache cleaned for: %s', $tags)
            );
        } else {
            $task->setMessage(
                Mage::helper('pimgento_core')->__('No cache cleaned')
            );
        }

        return true;
    }

    /**
     * Remove exclusion from config
     *
     * @return bool
     */
    public function deleteExclusion()
    {
        $exclusions = $this->getConfig('exclusions');

        if ($exclusions) {
            $exclusions = explode(',', $exclusions);
            foreach ($exclusions as $code) {
                $this->getAdapter()->delete($this->getTable(), array('code = ?' => $code));
            }
        }

        return true;
    }

    /**
     * Retrieve config data
     *
     * @param string $option
     *
     * @return string
     */
    public function getConfig($option)
    {
        return Mage::getStoreConfig('pimdata/' . $this->getCode() . '/' . $option);
    }

    /**
     * Retrieve Request Model
     *
     * @return Pimgento_Core_Model_Request
     */
    protected function getRequest()
    {
        return Mage::getModel('pimgento_core/request');
    }

    /**
     * Retrieve resource
     *
     * @return Pimgento_Core_Model_Resource_Request
     */
    protected function getResource()
    {
        return $this->getRequest()->getResource();
    }

    /**
     * Retrieve adapter
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function getAdapter()
    {
        return $this->getResource()->getAdapter();
    }

    /**
     * Retrieve temporary table name
     *
     * @return string
     */
    protected function getTable()
    {
        return $this->getResource()->getTableName($this->getCode());
    }

    /**
     * Check if column exists
     *
     * @param string $column
     *
     * @return bool
     */
    protected function columnExists($column)
    {
        return $this->getAdapter()->tableColumnExists($this->getTable(), $column);
    }

    /**
     * Check required columns
     *
     * @param array $columns
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    protected function columnsRequired($columns, $task)
    {
        foreach ($columns as $column) {
            if (!$this->columnExists($column)) {
                $task->setMessage(
                    Mage::helper('pimgento_product')->__('Column %s not found, step ignored', $column)
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Check current version is Enterprise Edition
     *
     * @return bool
     */
    protected function isEnterprise()
    {
        return Mage::getEdition() == Mage::EDITION_ENTERPRISE;
    }

    /**
     * Get Zend db Expr
     *
     * @param $value
     *
     * @return Zend_Db_Expr
     */
    protected function _zde($value)
    {
        return new Zend_Db_Expr($value);
    }

    /**
     * Get entity_type_id for a specified entity_type_code
     *
     * @param $type_code
     *
     * @return string
     */
    protected function _getEntityTypeId($type_code)
    {
        if (!isset($this->_entity_type_ids)) {
             $this->_entity_type_ids = array();
             $entity_types = Mage::getModel('eav/entity_type')->getCollection();
             foreach ($entity_types as $type) {
                 $this->_entity_type_ids[$type->getEntityTypeCode()] = $type->getId();
             }

        }
        return $this->_entity_type_ids[$type_code];
    }

}
