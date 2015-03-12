<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Attribute_Model_Import extends Pimgento_Core_Model_Import_Abstract
{

    /**
     * @var string
     */
    protected $_code = 'attribute';

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

        $this->getRequest()->createTableFromFile($this->getCode(), $file, 14);

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
                Mage::helper('pimgento_attribute')->__(
                    'No data to insert, verify the file is not empty or CSV configuration is correct'
                )
            );
        }

        $task->setMessage(
            Mage::helper('pimgento_attribute')->__('%s lines found', $lines)
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
        $adapter  = $this->getAdapter();
        $resource = $this->getResource();

        $select = $adapter->select()
            ->from(
                $resource->getTable('eav/attribute'),
                array(
                    'import'     => $this->_zde("'" . $this->getCode() . "'"),
                    'code'       => 'attribute_code',
                    'entity_id'  => 'attribute_id',
                )
            )
            ->where('entity_type_id = ?', 4);

        $insert = $adapter->insertFromSelect(
            $select,
            $resource->getTable('pimgento_core/code'),
            array('import', 'code', 'entity_id'),
            2
        );

        $adapter->query($insert);

        $this->getRequest()->matchEntity($this->getCode(), 'eav/attribute', 'attribute_id');

        return true;
    }

    /**
     * Match type with Magento logic (Step 4)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function matchType($task)
    {
        $adapter = $this->getAdapter();

        $adapter->addColumn($this->getTable(), 'backend_type',   'VARCHAR(255) NULL');
        $adapter->addColumn($this->getTable(), 'frontend_input', 'VARCHAR(255) NULL');
        $adapter->addColumn($this->getTable(), 'backend_model',  'VARCHAR(255) NULL');
        $adapter->addColumn($this->getTable(), 'source_model',   'VARCHAR(255) NULL');

        /* @var $helper Pimgento_Attribute_Helper_Data */
        $helper = Mage::helper('pimgento_attribute');

        $types = $helper->getTypes();

        $select = $adapter->select()
            ->from(
                $this->getTable(),
                array('entity_id', 'type', 'backend_type', 'frontend_input', 'backend_model', 'source_model')
            );
        $data = $adapter->fetchAssoc($select);

        foreach ($data as $id => $attribute) {
            $type = $types['default'];
            if (isset($types[$attribute['type']])) {
                $type = $types[$attribute['type']];
            }

            $values = array(
                'backend_type'   => $type['backend_type'],
                'frontend_input' => $type['frontend_input'],
                'backend_model'  => $type['backend_model'],
                'source_model'   => $type['source_model'],
            );

            $adapter->update($this->getTable(), $values, 'entity_id = ' . $id);
        }

        return true;
    }

    /**
     * Add attributes if not exists (Step 5)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function addAttributes($task)
    {
        $adapter  = $this->getAdapter();

        $attributes = $this->_getAttributes();

        $import = $adapter->select()->from($this->getTable());

        $query = $adapter->query($import);

        $count = 0;

        while (($row = $query->fetch())) {

            if (!in_array($row['entity_id'], $attributes)) {
                $this->_addAttribute($row);
                $count++;
            }

        }

        $task->setMessage(Mage::helper('pimgento_attribute')->__('%s attribute(s) added', $count));

        return true;
    }

    /**
     * Update attributes (Step 6)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateAttributes($task)
    {
        $adapter  = $this->getAdapter();

        $attributes = $this->_getAttributes();

        $import = $adapter->select()->from($this->getTable());

        $query = $adapter->query($import);

        $count = 0;

        while (($row = $query->fetch())) {

            if (in_array($row['entity_id'], $attributes)) {
                $this->_updateAttribute($row);
                $count++;
            }

        }

        $task->setMessage(Mage::helper('pimgento_attribute')->__('%s attribute(s) updated', $count));

        return true;
    }

    /**
     * Update family and group (Step 7)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateFamily($task)
    {
        $adapter  = $this->getAdapter();

        $attributes = $this->_getAttributes();

        $import = $adapter->select()->from($this->getTable());

        $query = $adapter->query($import);

        while (($row = $query->fetch())) {

            if (in_array($row['entity_id'], $attributes)) {
                $this->_updateFamily($row);
            }

        }

        return true;
    }

    /**
     * Drop table (Step 8)
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

    /**
     * Reindex (Step 9)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function reindex($task)
    {
        /* @var $indexer Mage_Index_Model_Indexer */
        $indexer = Mage::getSingleton('index/indexer');

        Mage::dispatchEvent('shell_reindex_init_process');

        $processes = array(
            'catalog_product_attribute',
            'catalog_product_flat',
        );

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

    /**
     * Update family
     *
     * @param array $data
     *
     * @return $this
     */
    protected function _updateFamily($data)
    {
        /* @var $model Mage_Catalog_Model_Resource_Eav_Attribute */
        $model = Mage::getModel('catalog/resource_eav_attribute');

        $model->load($data['entity_id']);

        if ($model->hasData()) {

            /* @var $familyModel Pimgento_Family_Model_Import */
            $familyModel = Mage::getModel('pimgento_family/import');

            $groups = $this->getRequest()->getCodes($familyModel->getCode());

            $families = explode(',', $data['families']);

            $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

            foreach ($families as $family) {
                if (isset($groups[$family])) {
                    $groupName = ucfirst($data['group']);

                    $setup->addAttributeGroup('catalog_product', $groups[$family], $groupName);
                    $id = $setup->getAttributeGroupId('catalog_product', $groups[$family], $groupName);

                    if ($id) {
                        $setup->addAttributeToSet('catalog_product', $groups[$family], $id, $model->getId());
                    }
                }
            }

        }

        return $this;
    }

    /**
     * Update Attribute
     *
     * @param array $data
     *
     * @return $this
     */
    protected function _updateAttribute($data)
    {
        /* @var $model Mage_Catalog_Model_Resource_Eav_Attribute */
        $model = Mage::getModel('catalog/resource_eav_attribute');

        $model->load($data['entity_id']);

        if ($model->hasData()) {

            $global = 1; // Global

            if ($data['scopable'] == 1) {
                $global = 2; // Website
            }

            if ($data['localizable'] == 1) {
                $global = 0; // Store View
            }

            $model->setIsGlobal($global);
            $model->setIsUnique($data['unique']);
            // $model->setIsFilterable($data['useable_as_grid_filter']);
            // $model->setIsFilterableInSearch($data['useable_as_grid_filter']);
            // $model->setUsedInProductListing($data['useable_as_grid_column']);

            /* @var $helper Pimgento_Core_Helper_Data */
            $helper = Mage::helper('pimgento_core');

            $stores = $helper->getStoresLang();

            $values = array();

            foreach ($stores as $local => $ids) {

                if ($this->getRequest()->columnExists($this->getTable(), 'label-' . $local)) {

                    foreach ($ids as $storeId) {
                        if ($storeId == 0) {
                            $model->setFrontendLabel($data['label-' . $local]);
                        } else {
                            $values[$storeId] = $data['label-' . $local];
                        }
                    }

                }

            }

            if (count($values)) {
                $model->setStoreLabels($values);
            }

            $model->save();

        }

        return $this;
    }

    /**
     * Add attribute
     *
     * @param array $data
     *
     * @return $this
     */
    protected function _addAttribute($data)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        $values = array(
            'attribute_id'   => $data['entity_id'],
            'entity_type_id' => 4
        );

        $adapter->insertIgnore($resource->getTable('eav/attribute'), $values);

        unset($values['entity_type_id']);

        $adapter->insertIgnore($resource->getTable('catalog/eav_attribute'), $values);

        /* @var $model Mage_Catalog_Model_Resource_Eav_Attribute */
        $model = Mage::getModel('catalog/resource_eav_attribute');

        $model->load($data['entity_id']);

        if ($model->hasData()) {

            $global = 1; // Global

            if ($data['scopable'] == 1) {
                $global = 2; // Website
            }

            if ($data['localizable'] == 1) {
                $global = 0; // Store View
            }

            /* eav_attribute */
            $model->setAttributeId($data['entity_id']);
            $model->setEntityTypeId(4);
            $model->setAttributeCode($data['code']);
            $model->setAttributeModel(null);
            $model->setBackendModel($data['backend_model']);
            $model->setBackendType($data['backend_type']);
            $model->setBackendTable(null);
            $model->setFrontendModel(null);
            $model->setFrontendInput($data['frontend_input']);
            $model->setFrontendLabel('Default');
            $model->setFrontendClass(null);
            $model->setSourceModel($data['source_model']);
            $model->setIsRequired(0);
            $model->setIsUserDefined(1);
            $model->setDefaultValue(null);
            $model->setIsUnique($data['unique']);
            $model->setNote(null);

            /* catalog_eav_attribute */
            $model->setFrontendInputRenderer(null);
            $model->setIsGlobal($global);
            $model->setIsVisible(1);
            $model->setIsSearchable(0);
            // $model->setIsFilterable($data['useable_as_grid_filter']);
            $model->setIsFilterable(0);
            $model->setIsComparable(0);
            $model->setIsVisibleOnFront(0);
            $model->setIsHtmlAllowedOnFront(0);
            $model->setIsUsedForPriceRules(0);
            // $model->setIsFilterableInSearch($data['useable_as_grid_filter']);
            // $model->setUsedInProductListing($data['useable_as_grid_column']);
            $model->setIsFilterableInSearch(0);
            $model->setUsedInProductListing(0);
            $model->setUsedForSortBy(0);
            $model->setIsConfigurable(0);
            $model->setApplyTo(null);
            $model->setIsVisibleInAdvancedSearch(0);
            $model->setPosition(0);
            $model->setIsWysiwygEnabled(0);
            $model->setIsUsedForPromoRules(0);

            $model->save();

        }

        return $this;
    }

    /**
     * Retrieve product attributes id
     *
     * @return array
     */
    protected function _getAttributes()
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        $select = $adapter->select()
            ->from(
                $resource->getTable('eav/attribute'),
                array(
                    'attribute_id',
                )
            )
            ->where('entity_type_id = ?', 4);

        return $adapter->fetchCol($select);
    }

}