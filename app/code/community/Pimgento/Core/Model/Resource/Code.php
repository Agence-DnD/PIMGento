<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Model_Resource_Code extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('pimgento_core/code', 'id');
    }

    /**
     * Save Object code
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return $this
     */
    public function save(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getWriteAdapter();

        if ($object->getImport() && $object->getCode() && $object->getEntityId()) {

            $values = array(
                'import'    => $object->getImport(),
                'code'      => $object->getCode(),
                'entity_id' => $object->getEntityId(),
            );

            $adapter->insertOnDuplicate(
                $this->getMainTable(), $values, array('entity_id')
            );

        } else {
            Mage::throwException(Mage::helper('pimgento_core')->__('Error between PIM code and Magento entity'));
        }

        return $this;
    }

    /**
     * Delete Code by entity id and import type
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return $this
     */
    public function delete(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getWriteAdapter();

        if ($object->getImport() && $object->getEntityId()) {

            $values = array(
                'entity_id = ?' => $object->getEntityId(),
                'import = ?'    => $object->getImport(),
            );

            $adapter->delete(
                $this->getMainTable(), $values
            );

        }

        return $this;
    }

}
