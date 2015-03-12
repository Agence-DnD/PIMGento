<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Model_Resource_Code_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * Initialize collection
     */
    public function _construct()
    {
        $this->_init('pimgento_core/code');
    }

    /**
     * Add import filter
     *
     * @param string $import
     *
     * @return $this
     */
    public function addImportFilter($import)
    {
        $this->addFilter('import', array('in' => $import));

        return $this;
    }

    /**
     * Convert collection to array with code key
     *
     * @return array
     */
    public function toArrayCode()
    {
        $arrItems = array();
        $arrItems['totalRecords'] = $this->getSize();

        $arrItems['items'] = array();
        foreach ($this as $item) {
            $arrItems['items'][$item->getCode()] = $item->getEntityId();
        }

        return $arrItems;
    }

    /**
     * Convert collection to array with entity key
     *
     * @return array
     */
    public function toArrayEntity()
    {
        $arrItems = array();
        $arrItems['totalRecords'] = $this->getSize();

        $arrItems['items'] = array();
        foreach ($this as $item) {
            $arrItems['items'][$item->getEntityId()] = $item->getCode();
        }

        return $arrItems;
    }

}
