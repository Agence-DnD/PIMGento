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

DROP TABLE IF EXISTS `{$this->getTable('pimgento_variant')}`;
CREATE TABLE `{$this->getTable('pimgento_variant')}` (
    `code` VARCHAR(255) NOT NULL,
    `axis` VARCHAR(255) NOT NULL DEFAULT '',
    UNIQUE KEY `IDX_UNQ_PIMGENTO_VARIANT_CODE` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Pimgento Variant';

");

$installer->endSetup();