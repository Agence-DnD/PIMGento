<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$installer->getTable('eav/attribute_option_value')}`
ADD UNIQUE INDEX IDX_UNIQUE_PIMGENTO_OPTION_STORE (`option_id`, `store_id`)

");

$installer->endSetup();