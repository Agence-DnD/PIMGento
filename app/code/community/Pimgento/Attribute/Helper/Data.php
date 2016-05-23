<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Attribute_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * Match Pim type with Magento attribute logic
     *
     * @return array
     */
    public function getTypes()
    {
        $types = array(
            'pim_catalog_identifier'   => $this->_types('text'),
            'pim_catalog_text'         => $this->_types('text'),
            'pim_catalog_metric'       => $this->_types('text'),
            'pim_catalog_number'       => $this->_types('text'),
            'pim_catalog_textarea'     => $this->_types('textarea'),
            'pim_catalog_date'         => $this->_types('date'),
            'pim_catalog_boolean'      => $this->_types('boolean'),
            'pim_catalog_simpleselect' => $this->_types('select'),
            'pim_catalog_multiselect'  => $this->_types('multiselect'),
            'default'                  => $this->_types('text'),
        );

        $specific = unserialize(Mage::getStoreConfig('pimdata/attribute/types'));

        foreach ($specific as $type) {
            $types[$type['pimgento_type']] = $this->_types($type['magento_type']);
        }

        return $types;
    }

    /**
     * Retrieve configuration type
     *
     * @param string $type
     *
     * @return array
     */
    protected function _types($type)
    {
        $types = array(
            'text' =>  array(
                'backend_type' => 'varchar',
                'frontend_input' => 'text',
                'backend_model' => NULL,
                'source_model' => NULL,
            ),
            'textarea' => array(
                'backend_type' => 'text',
                'frontend_input' => 'textarea',
                'backend_model' => NULL,
                'source_model' => NULL,
            ),
            'date' => array(
                'backend_type' => 'datetime',
                'frontend_input' => 'date',
                'backend_model' => 'eav/entity_attribute_backend_datetime',
                'source_model' => NULL,
            ),
            'boolean' => array(
                'backend_type' => 'int',
                'frontend_input' => 'boolean',
                'backend_model' => NULL,
                'source_model' => 'eav/entity_attribute_source_boolean',
            ),
            'multiselect' => array(
                'backend_type' => 'varchar',
                'frontend_input' => 'multiselect',
                'backend_model' => 'eav/entity_attribute_backend_array',
                'source_model' => NULL,
            ),
            'select' => array(
                'backend_type' => 'int',
                'frontend_input' => 'select',
                'backend_model' => NULL,
                'source_model' => 'eav/entity_attribute_source_table',
            ),
            'price' => array(
                'backend_type' => 'decimal',
                'frontend_input' => 'price',
                'backend_model' => 'catalog/product_attribute_backend_price',
                'source_model' => NULL,
            ),
            'default' => array(
                'backend_type' => 'varchar',
                'frontend_input' => 'text',
                'backend_model' => NULL,
                'source_model' => NULL,
            ),
        );

        return isset($types[$type]) ? $types[$type] : $types['default'];
    }

}