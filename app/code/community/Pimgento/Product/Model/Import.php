<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Product_Model_Import extends Pimgento_Core_Model_Import_Abstract
{

    /**
     * @var string
     */
    protected $_code = 'product';

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
                Mage::helper('pimgento_product')->__(
                    'No data to insert, verify the file is not empty or CSV configuration is correct'
                )
            );
        }

        $task->setMessage(
            Mage::helper('pimgento_product')->__('%s lines found', $lines)
        );

        return true;
    }

    /**
     * Insert data (Step 3)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateColumn($task)
    {
        $adapter = $this->getAdapter();

        $sku = 'sku';

        $transformer = Mage::helper('pimgento_product')->transformer();

        foreach ($transformer as $attribute => $match) {
            if ((in_array('sku', $match))) {
                $sku = $attribute;
            }
        }

        if (!$this->columnsRequired(array($sku), $task)) {
            $task->error(
                Mage::helper('pimgento_product')->__('Column "%s" not found', $sku)
            );
        }

        $adapter->changeColumn($this->getTable(), $sku, 'code', 'VARCHAR(255)');

        $this->deleteExclusion();

        $adapter->addColumn($this->getTable(), $sku, 'VARCHAR(255) NOT NULL default ""');
        $values = array(
            $sku => $this->_zde('`code`')
        );
        $adapter->update($this->getTable(), $values);

        $adapter->addColumn($this->getTable(), '_type_id', 'VARCHAR(255) NOT NULL default "simple"');
        $adapter->addColumn($this->getTable(), '_options_container', 'VARCHAR(255) NOT NULL default "container2"');

        $defaultTax = $this->getConfig('tax_default');

        $adapter->addColumn($this->getTable(), '_tax_class_id', 'INT(11) NOT NULL default "' . $defaultTax . '"');

        return true;
    }

    /**
     * Create configurable (Step 4)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function createConfigurable($task)
    {
        if (!$this->getConfig('configurable_enabled')) {
            $task->setMessage(
                Mage::helper('pimgento_product')->__('Configurable product creation is disabled')
            );
            return false;
        }

        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        if (!$this->columnsRequired(array('groups'), $task)) {
            return false;
        }

        $adapter->addColumn($this->getTable(), '_children',   'VARCHAR(255) NULL');
        $adapter->addColumn($this->getTable(), '_attributes', 'VARCHAR(255) NOT NULL DEFAULT ""');

        if ($adapter->isTableExists('pimgento_variant')) {
            $select = $adapter->select()
                ->from(false, array())
                ->joinInner(
                    array('v' => $adapter->getTableName('pimgento_variant')),
                    'p.groups = v.code',
                    array(
                        '_attributes' => 'v.axis'
                    )
                );

            $adapter->query(
                $adapter->updateFromSelect($select, array('p' => $this->getTable()))
            );
        }

        $attributes = explode(',', $this->getConfig('configurable_attributes'));

        if (!count($attributes)) {
            $task->setMessage(
                Mage::helper('pimgento_product')->__(
                    'No attribute selected in configuration, configurable products will not be created'
                )
            );
            return false;
        }

        $success = true;

        foreach ($attributes as $id) {
            $code = $adapter->fetchOne(
                $adapter->select()
                    ->from(
                        $resource->getTable('eav/attribute'),
                        array(
                            'attribute_code'
                        )
                    )
                    ->where('attribute_id = ?', $id)
                    ->limit(1)
            );

            if ($code) {
                if ($adapter->tableColumnExists($this->getTable(), $code)) {
                    $values = array(
                        '_attributes' => $this->_zde('TRIM(BOTH "," FROM CONCAT(`_attributes`, ",", "' . $id . '"))')
                    );

                    $variant = '';
                    if ($adapter->isTableExists('pimgento_variant')) {
                        $variantTable = $adapter->getTableName('pimgento_variant');
                        $variant = ' AND `groups` NOT IN (SELECT `code` FROM `' . $variantTable . '`)';
                    }

                    $adapter->update($this->getTable(), $values, '`' . $code . '` <> "" AND `groups` <> ""' . $variant);
                }
            }
        }

        $family = 'family';

        if (!$adapter->tableColumnExists($this->getTable(), 'family')) {
            /* @var $product Mage_Catalog_Model_Product */
            $product = Mage::getModel('catalog/product');

            $family = $this->_zde($product->getDefaultAttributeSetId());
        }

        $values = array(
            'code'               => 'groups',
            '_children'          => $this->_zde('GROUP_CONCAT(`code` SEPARATOR ",")'),
            '_attributes'        => '_attributes',
            '_type_id'           => $this->_zde('"configurable"'),
            'family'             => $family,
            '_options_container' => $this->_zde('"container1"'),
            'categories'         => 'categories',
        );

        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        $stores = $helper->getStoresCurrency();

        $transformer = Mage::helper('pimgento_product')->transformer();

        $price = 'price';
        $specialPrice = 'special_price';

        foreach ($transformer as $attribute => $match) {

            if (in_array('price', $match)) {
                $price = $attribute;
            }

            if (in_array('special_price', $match)) {
                $specialPrice = $attribute;
            }

        }

        foreach ($stores as $currency => $store) {

            foreach ($store as $data) {

                $columns = array(
                    'price'         => array(
                        $price . '-' . $currency, // price-USD
                        $price . '-' . $data['code'] . '-' . $currency, // price-website-USD
                        $price . '-' . $data['lang'] . '-' . $data['code'] . '-' . $currency, // price-en_US-website-USD
                    ),
                    'special_price' => array(
                        $specialPrice . '-' . $currency,
                        $specialPrice . '-' . $data['code'] . '-' . $currency,
                        $specialPrice . '-' . $data['lang'] . '-' . $data['code'] . '-' . $currency,
                    ),
                );

                foreach ($columns as $attribute => $cols) {
                    foreach ($cols as $column) {
                        if ($adapter->tableColumnExists($this->getTable(), $column)) {

                            $adapter->update(
                                $this->getTable(), array($column => $this->_zde('NULL')), '`' . $column . '` = ""'
                            );

                            $values[$column] = $this->_zde('MIN(`' . $column . '`)');
                        }
                    }
                }

            }

        }

        $matches = unserialize($this->getConfig('configurable_values'));

        foreach ($matches as $match) {
            if ($adapter->tableColumnExists($this->getTable(), $match['attribute'])) {
                $values[$match['attribute']] =
                    $match['value'] !== '' ? $this->_zde($match['value']) : $match['attribute'];
            } else {
                $success = false;
                $task->setMessage(
                    Mage::helper('pimgento_product')->__(
                        'Warning: %s column not found in CSV file', $match['attribute']
                    )
                );
            }
        }

        $select = $adapter->select()
            ->from($this->getTable(), $values)
            ->where('groups <> ?', '')
            ->where('_attributes <> ?', '')
            ->group('groups');

        $insert = $adapter->insertFromSelect($select, $this->getTable(), array_keys($values), 1);

        $adapter->query($insert);

        return $success;
    }

    /**
     * Match Entity with Code (Step 5)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function matchEntity($task)
    {
        $this->getRequest()->matchEntity($this->getCode(), 'catalog/product', 'entity_id');

        return true;
    }

    /**
     * Update product family (Step 6)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateFamily($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        /* @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');

        $defaultId = $product->getDefaultAttributeSetId();

        if (!$this->columnsRequired(array('family'), $task)) {
            $adapter->addColumn($this->getTable(), 'family', 'INT(11) NULL DEFAULT ' . $defaultId);
        }

        /* @var $family Pimgento_Family_Model_Import */
        $family = Mage::getModel('pimgento_family/import');

        $families = $adapter->select()
            ->from(false, array('family' => $this->_zde('IF(c.entity_id IS NULL,' . $defaultId . ',c.entity_id)')))
            ->joinLeft(
                array('c' => $resource->getTable('pimgento_core/code')),
                'p.family = c.code AND c.import = "' . $family->getCode() . '"',
                array()
            );

        $adapter->query(
            $adapter->updateFromSelect($families, array('p' => $this->getTable()))
        );

        return true;
    }

    /**
     * Update options values (Step 7)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateValues($task)
    {
        $file = $task->getFile();

        $request  = $this->getRequest();
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        /* @var $option Pimgento_Option_Model_Import */
        $option = Mage::getModel('pimgento_option/import');

        $columns = $request->getFirstLine($file);

        foreach ($columns as $column) {
            if ($adapter->tableColumnExists($this->getTable(), $column)) {

                $select = $adapter->select()
                    ->from(
                        array('p' => $this->getTable()),
                        array(
                            'entity_id' => 'p.entity_id'
                        )
                    )
                    ->distinct()
                    ->joinInner(
                        array(
                            'c' => $resource->getTable('pimgento_core/code')
                        ),
                        'FIND_IN_SET(REPLACE(`c`.`code`,"' . $column . '_",""), `p`.`' . $column . '`)
                        AND `c`.`import` = "' . $option->getCode() . '"',
                        array(
                            $column => new Zend_Db_Expr('GROUP_CONCAT(`c`.`entity_id` SEPARATOR ",")')
                        )
                    )
                    ->group('p.code');

                $insert = $adapter->insertFromSelect(
                    $select, $this->getTable(), array('entity_id', $column), 1
                );

                $adapter->query($insert);
            }
        }

        if ($this->getConfig('configurable_enabled')) {
            $disabled = unserialize($this->getConfig('configurable_update'));

            $exclude = array();
            foreach ($disabled as $pim) {
                if ($this->columnExists($pim['attribute'])) {
                    $exclude[$pim['attribute']] = $this->_zde('""');
                }
            }

            if (count($exclude)) {
                $adapter->update($this->getTable(), $exclude, '_type_id = "configurable" AND _is_new = 0');
            }
        }

        return true;
    }

    /**
     * Create category entities (Step 8)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function createEntities($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        $family = 'family';

        if (!$adapter->tableColumnExists($this->getTable(), 'family')) {
            /* @var $product Mage_Catalog_Model_Porduct */
            $product = Mage::getModel('catalog/product');

            $family = $this->_zde($product->getDefaultAttributeSetId());
        }

        $parents = $adapter->select()
            ->from(
                $this->getTable(),
                array(
                    'entity_id'        => 'entity_id',
                    'entity_type_id'   => $this->_zde(4),
                    'attribute_set_id' => $family,
                    'type_id'          => '_type_id',
                    'sku'              => 'code',
                    'has_options'      => $this->_zde(0),
                    'required_options' => $this->_zde(0),
                    'created_at'       => $this->_zde('now()'),
                    'updated_at'       => $this->_zde('now()'),
                )
            );

        $insert = $adapter->insertFromSelect(
            $parents, $resource->getTable('catalog/product'), array(), 1
        );

        $adapter->query($insert);

        return true;
    }

    /**
     * Set values to attributes (Step 9)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function setValues($task)
    {
        $adapter = $this->getAdapter();

        $file = $task->getFile();

        $values = array(
            'tax_class_id' => '_tax_class_id',
        );

        $this->getRequest()->setValues($this->getCode(), 'catalog/product', $values, 4, 0, 2);

        $values = array(
            'options_container'     => '_options_container',
            'enable_googlecheckout' => $this->_zde(0),
            'is_recurring'          => $this->_zde(0),
            'visibility'            => $this->_zde(4),
        );

        if ($this->getConfig('configurable_enabled')) {
            $values['visibility'] = $this->_zde('IF(`_type_id` = "simple" AND `groups` <> "", 1, 4)');
        }

        $this->getRequest()->setValues($this->getCode(), 'catalog/product', $values, 4, 0);

        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        $codes = array_merge(
            $helper->getStoresLang(),
            $helper->getStoresWebsites()
        );

        $columns = $this->getRequest()->getFirstLine($file);

        $transformer = Mage::helper('pimgento_product')->transformer();

        foreach ($columns as $column) {

            if (!$adapter->tableColumnExists($this->getTable(), $column)) {
                continue;
            }

            $translation = false;

            foreach ($codes as $code => $ids) {
                
                //check if index-key is a localized one -> e.g. en_US
                if (preg_match('/-[a-z]{2}_[A-Z]{2}$/', $column)) {

                    $translation = true;

                    if (preg_match('/^(?P<attribute>[^-]*)-' . $code . '$/', $column, $matches)) {

                        foreach ($ids as $key => $storeId) {

                            $values = array(
                                $matches['attribute'] => $column
                            );

                            if (isset($transformer[$matches['attribute']])) {

                                $match = $transformer[$matches['attribute']];

                                foreach ($match as $attribute) {
                                    $values[$attribute] = $column;
                                }
                            }

                            foreach ($values as $attribute => $column) {

                                if ($attribute == 'url_key') {
                                    $adapter->addColumn(
                                        $this->getTable(), $column . '_' . $key, 'VARCHAR(255) NOT NULL default ""'
                                    );
                                    $values = array(
                                        $column . '_' . $key => $this->_zde(
                                            'CONCAT(`' . $column . '`,"-' . $key . '")'
                                        ),
                                    );
                                    $adapter->update($this->getTable(), $values);

                                    $values[$attribute] = $column . '_' . $key;
                                }

                            }

                            $this->getRequest()->setValues(
                                $this->getCode(), 'catalog/product', $values, 4, $storeId
                            );
                        }

                    }

                }

            }

            if (!$translation) {
                $values = array(
                    $column => $column
                );

                if (isset($transformer[$column])) {

                    $match = $transformer[$column];

                    foreach ($match as $attribute) {
                        $values[$attribute] = $column;
                    }
                }

                $this->getRequest()->setValues(
                    $this->getCode(), 'catalog/product', $values, 4, 0
                );
            }

        }

        return true;
    }

    /**
     * Update configurable links (Step 10)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateConfigurable($task)
    {
        if (!$this->getConfig('configurable_enabled')) {
            $task->setMessage(
                Mage::helper('pimgento_product')->__('Configurable product creation is disabled')
            );
            return false;
        }

        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        if (!$this->columnsRequired(array('groups'), $task)) {
            return false;
        }

        $query = $adapter->query(
            $adapter->select()
            ->from(
                $this->getTable(),
                array(
                    'entity_id',
                    '_attributes',
                    '_children'
                )
            )
            ->where('_type_id = ?', 'configurable')
            ->where('_attributes <> ?', $this->_zde('""'))
        );

        $stores = Mage::app()->getStores();

        while (($row = $query->fetch())) {

            $attributes = explode(',', $row['_attributes']);

            foreach ($attributes as $id) {

                if (!$id) {
                    continue;
                }

                if (!is_numeric($id)) {
                    continue;
                }

                $values = array(
                    'product_id'   => $row['entity_id'],
                    'attribute_id' => $id,
                    'position'     => 1,
                );

                $adapter->insertIgnore($resource->getTable('catalog/product_super_attribute'), $values);

                $superAttributeId = $adapter->fetchOne(
                    $adapter->select()
                        ->from($resource->getTable('catalog/product_super_attribute'))
                        ->where('attribute_id = ?', $id)
                        ->where('product_id = ?', $row['entity_id'])
                        ->limit(1)
                );

                foreach ($stores as $store) {

                    $values = array(
                        'product_super_attribute_id' => $superAttributeId,
                        'store_id'                   => $store->getId(),
                        'use_default'                => 1,
                        'value'                      => ''
                    );

                    $adapter->insertIgnore($resource->getTable('catalog/product_super_attribute_label'), $values);
                }

                $children = explode(',', $row['_children']);

                foreach ($children as $child) {

                    $childId = $adapter->fetchOne(
                        $adapter->select()
                        ->from(
                            $resource->getTable('catalog/product'),
                            array(
                                'entity_id'
                            )
                        )
                        ->where('sku = ?', $child)
                        ->limit(1)
                    );

                    if ($childId) {

                        $values = array(
                            'parent_id' => $row['entity_id'],
                            'child_id'  => $childId,
                        );

                        $adapter->insertIgnore($resource->getTable('catalog/product_relation'), $values);

                        $values = array(
                            'product_id' => $childId,
                            'parent_id'  => $row['entity_id'],
                        );

                        $adapter->insertIgnore($resource->getTable('catalog/product_super_link'), $values);

                    }
                }

            }

        }

        return true;
    }

    /**
     * Update products websites (Step 11)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function setWebsites($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        $websites = Mage::app()->getWebsites();

        foreach ($websites as $website) {
            $select = $adapter->select()
                ->from(
                    $resource->getTable('catalog/product'),
                    array(
                        'product_id' => 'entity_id',
                        'website_id' => $this->_zde($website->getId())
                    )
                );

            $insert = $adapter->insertFromSelect(
                $select, $resource->getTable('catalog/product_website'), array('product_id', 'website_id'), 1
            );

            $adapter->query($insert);
        }

        return true;
    }

    /**
     * Set prices (Step 12)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function setPrices($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        $stores = $helper->getStoresCurrency();

        $transformer = Mage::helper('pimgento_product')->transformer();

        $price = 'price';
        $specialPrice = 'special_price';

        foreach ($transformer as $attribute => $match) {

            if (in_array('price', $match)) {
                $price = $attribute;
            }

            if (in_array('special_price', $match)) {
                $specialPrice = $attribute;
            }

        }

        foreach ($stores as $currency => $store) {

            foreach ($store as $data) {

                $values = array();

                if ($data['code'] == 'admin') {
                    $code = $adapter->fetchOne(
                        $adapter->select()
                            ->from(
                                $resource->getTable('core/website'),
                                array(
                                    'code' => 'code'
                                )
                            )
                            ->where('is_default = ?', 1)
                            ->limit(1)
                    );
                    if ($code) {
                        $data['code'] = $helper->getChannel($code);
                    }
                }

                $columns = array(
                    'price'         => array(
                        $price . '-' . $currency, // price-USD
                        $price . '-' . $data['code'] . '-' . $currency, // price-website-USD
                        $price . '-' . $data['lang'] . '-' . $data['code'] . '-' . $currency, // price-en_US-website-USD
                    ),
                    'special_price' => array(
                        $specialPrice . '-' . $currency,
                        $specialPrice . '-' . $data['code'] . '-' . $currency,
                        $specialPrice . '-' . $data['lang'] . '-' . $data['code'] . '-' . $currency,
                    ),
                );

                foreach ($columns as $attribute => $cols) {
                    foreach ($cols as $column) {
                        if ($adapter->tableColumnExists($this->getTable(), $column)) {
                            $values[$attribute] = new Zend_Db_Expr(
                                'IF(`' . $column . '` <> "",`' . $column . '`,NULL)'
                            );
                        }
                    }
                }

                if (count($values)) {
                    $this->getRequest()->setValues(
                        $this->getCode(), 'catalog/product', $values, 4, $data['id']
                    );
                }

            }

        }

        return true;
    }

    /**
     * Set configurable prices (Step 13)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function setConfigurablePrices($task)
    {
        if (!$this->getConfig('configurable_enabled')) {
            $task->setMessage(
                Mage::helper('pimgento_product')->__('Configurable product creation is disabled')
            );
            return false;
        }

        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        $priceId = $adapter->fetchOne(
            $adapter->select()
                ->from($resource->getTable('eav/attribute'), array('attribute_id'))
                ->where('entity_type_id = ?', 4)
                ->where('attribute_code = ?', 'price')
                ->limit(1));

        $specialPriceId = $adapter->fetchOne(
            $adapter->select()
                ->from($resource->getTable('eav/attribute'), array('attribute_id'))
                ->where('entity_type_id = ?', 4)
                ->where('attribute_code = ?', 'special_price')
                ->limit(1));

        $attributes = explode(',', $this->getConfig('configurable_attributes'));

        if (count($attributes) && $priceId && $specialPriceId) {

            $attributeId = end($attributes);

            $select = $adapter->select()
                ->from(
                    array(
                        'l' => $resource->getTable('catalog/product_super_link')
                    ),
                    array(
                        'product_super_attribute_id' => 'a.product_super_attribute_id',
                        'value_index'                => 'o.option_id',
                        'is_percent'                 => $this->_zde(0),
                        'pricing_value'              => $this->_zde(
                            'IF(
                            IF(d3.value, d3.value, d1.value) - IF(d4.value, d4.value, d2.value) >= 0,
                            IF(d3.value, d3.value, d1.value) - IF(d4.value, d4.value, d2.value),
                            0
                        )'
                        ),
                        'website_id'                 => $this->_zde(0),
                    )
                )->joinInner(
                    array('d1' => $resource->getValueTable('catalog/product', 'decimal')),
                    'd1.entity_id = l.product_id AND d1.attribute_id = ' . $priceId . ' AND d1.store_id = 0',
                    array()
                )->joinInner(
                    array('d2' => $resource->getValueTable('catalog/product', 'decimal')),
                    'd2.entity_id = l.parent_id AND d2.attribute_id = ' . $priceId . ' AND d2.store_id = 0',
                    array()
                )->joinLeft(
                    array('d3' => $resource->getValueTable('catalog/product', 'decimal')),
                    'd3.entity_id = l.product_id AND d3.attribute_id = ' . $specialPriceId . ' AND d3.store_id = 0',
                    array()
                )->joinLeft(
                    array('d4' => $resource->getValueTable('catalog/product', 'decimal')),
                    'd4.entity_id = l.parent_id AND d4.attribute_id = ' . $specialPriceId . ' AND d4.store_id = 0',
                    array()
                )->joinInner(
                    array('a' => $resource->getTable('catalog/product_super_attribute')),
                    'l.parent_id = a.product_id',
                    array()
                )->joinInner(
                    array('i' => $resource->getValueTable('catalog/product', 'int')),
                    'a.attribute_id = i.attribute_id AND i.entity_id = l.product_id',
                    array()
                )->joinInner(
                    array('o' => $resource->getTable('eav/attribute_option')),
                    'a.attribute_id = o.attribute_id AND o.attribute_id = ' . $attributeId . ' AND i.value = o.option_id',
                    array()
                )->having('pricing_value > 0');

            $insert = $adapter->insertFromSelect(
                $select,
                $resource->getTable('catalog/product_super_attribute_pricing'),
                array('product_super_attribute_id', 'value_index', 'is_percent', 'pricing_value', 'website_id'),
                1
            );

            $adapter->query($insert);

        }

        return true;
    }

    /**
     * Set categories (Step 14)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function setCategories($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        if (!$this->columnsRequired(array('categories'), $task)) {
            return false;
        }

        /* @var $category Pimgento_Category_Model_Import */
        $category = Mage::getModel('pimgento_category/import');

        $select = $adapter->select()
            ->from(
                array(
                    'c' => $resource->getTable('pimgento_core/code')
                ),
                array()
            )
            ->joinInner(
                array('p' => $this->getTable()),
                'FIND_IN_SET(`c`.`code`, `p`.`categories`) AND `c`.`import` = "' . $category->getCode() . '"',
                array(
                    'category_id' => 'c.entity_id',
                    'product_id'  => 'p.entity_id',
                    'position'    => $this->_zde(0)
                )
            )
            ->joinInner(
                array('e' => $resource->getTable('catalog/category')),
                'c.entity_id = e.entity_id',
                array()
            );

        $insert = $adapter->insertFromSelect(
            $select, $resource->getTable('catalog/category_product'), array('category_id', 'product_id', 'position'), 1
        );

        $adapter->query($insert);

        return true;
    }

    /**
     * Init Stock (Step 15)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function initStock($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        $select = $adapter->select()
            ->from(
                $resource->getTable('catalog/product'),
                array(
                    'product_id'                => 'entity_id',
                    'stock_id'                  => $this->_zde(1),
                    'qty'                       => $this->_zde(0),
                    'is_in_stock'               => $this->_zde(0),
                    'low_stock_date'            => $this->_zde('NULL'),
                    'stock_status_changed_auto' => $this->_zde(0),
                )
            );

        $insert = $adapter->insertFromSelect(
            $select,
            $resource->getTable('cataloginventory/stock_item'),
            array('product_id', 'stock_id', 'qty', 'is_in_stock', 'low_stock_date', 'stock_status_changed_auto'),
            2
        );

        $adapter->query($insert);

        return true;
    }

    /**
     * Set related, up-sell and cross-sell (Step 16)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function setRelated($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        $related = array();

        if ($this->columnExists('RELATED-products')) {
            $related[] = array(
                'type_id' => 1,
                'column'  => 'RELATED-products',
            );
        }

        if ($this->columnExists('UPSELL-products')) {
            $related[] = array(
                'type_id' => 4,
                'column'  => 'UPSELL-products',
            );
        }

        if ($this->columnExists('CROSSSELL-products')) {
            $related[] = array(
                'type_id' => 5,
                'column'  => 'CROSSSELL-products',
            );
        }

        foreach ($related as $type) {
            /* @var $product Pimgento_Product_Model_Import */
            $product = Mage::getModel('pimgento_product/import');

            $select = $adapter->select()
                ->from(
                    array(
                        'c' => $resource->getTable('pimgento_core/code')
                    ),
                    array()
                )
                ->joinInner(
                    array('p' => $this->getTable()),
                    'FIND_IN_SET(`c`.`code`, `p`.`' . $type['column'] . '`)
                        AND `c`.`import` = "' . $product->getCode() . '"',
                    array(
                        'product_id'        => 'p.entity_id',
                        'linked_product_id' => 'c.entity_id',
                        'link_type_id'      => $this->_zde($type['type_id'])
                    )
                )
                ->joinInner(
                    array('e' => $resource->getTable('catalog/product')),
                    'c.entity_id = e.entity_id',
                    array()
                );

            $insert = $adapter->insertFromSelect(
                $select,
                $resource->getTable('catalog/product_link'),
                array('product_id', 'linked_product_id', 'link_type_id'),
                1
            );

            $adapter->query($insert);
        }

        return true;
    }

    /**
     * Drop table (Step 17)
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
     * Reindex (Step 18)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function reindex($task)
    {
        if (!$this->getConfig('reindex')) {
            $task->setMessage(
                Mage::helper('pimgento_product')->__('Reindex is disabled')
            );
            return false;
        }

        /* @var $indexer Mage_Index_Model_Indexer */
        $indexer = Mage::getSingleton('index/indexer');

        Mage::dispatchEvent('shell_reindex_init_process');

        if ($this->isEnterprise()) {
            $processes = array(
                'catalog_product_flat',
                'cataloginventory_stock',
                'catalog_product_price',
                'catalog_url_product',
                'url_redirect',
                'catalog_category_product',
                'catalogsearch_fulltext',
                'catalog_product_attribute',
            );
        } else {
            $processes = array(
                'catalog_product_attribute',
                'catalog_product_flat',
                'catalog_product_price',
                'catalog_url',
                'catalog_category_product',
                'catalogsearch_fulltext',
                'cataloginventory_stock',
            );
        }

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

}