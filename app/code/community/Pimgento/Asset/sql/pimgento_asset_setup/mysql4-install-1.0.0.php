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

DROP TABLE IF EXISTS `{$this->getTable('pimgento_asset')}`;
CREATE TABLE `{$this->getTable('pimgento_asset')}` (
    `asset` VARCHAR(255) NOT NULL,
    `website_id` INT(11) NOT NULL DEFAULT 0,
    `store_id` INT(11) NOT NULL DEFAULT 0,
    `file` VARCHAR(255) NULL,
    `variation` VARCHAR(255) NULL,
    `image` VARCHAR(255) NULL,
    `small_image` VARCHAR(255) NULL,
    `thumbnail` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Pimgento Asset';

");

$installer->endSetup();