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

DROP TABLE IF EXISTS `{$installer->getTable('pimgento_core/code')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('pimgento_core/code')}` (
  `id`        INT(11) unsigned NOT NULL AUTO_INCREMENT,
  `import`    VARCHAR(255) NOT NULL default '',
  `code`      VARCHAR(255) NOT NULL default '',
  `entity_id` INT(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNQ_PIMGENTO_CORE_CODE_IMPORT_ENTITY` (`code`, `import`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE INDEX IDX_PIMGENTO_CORE_CODE ON `{$installer->getTable('pimgento_core/code')}` (`code`);

");

$installer->endSetup();