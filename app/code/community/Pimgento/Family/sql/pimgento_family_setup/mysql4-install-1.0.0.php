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

DROP TABLE IF EXISTS `{$this->getTable('pimgento_family_attribute_relations')}`;
CREATE TABLE `{$this->getTable('pimgento_family_attribute_relations')}` (
    `id`                INT(11) unsigned NOT NULL AUTO_INCREMENT,
    `family_code`       VARCHAR(255) NOT NULL,
    `attribute_code`    VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Pimgento family attribute relations';

");

$installer->endSetup();
