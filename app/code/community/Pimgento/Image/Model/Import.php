<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Image_Model_Import extends Pimgento_Core_Model_Import_Abstract
{

    /**
     * @var string
     */
    protected $_code = 'product';

    /**
     * Create Table (Step 1)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function createTable($task)
    {
        $this->getRequest()->createTable(
            $this->getCode(),
            array('code', 'image', 'small_image', 'thumbnail', 'gallery')
        );

        return true;
    }

    /**
     * Move images (Step 2)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function moveImages($task)
    {
        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        /* @var $helper Pimgento_Image_Helper_Data */
        $helper = Mage::helper('pimgento_image');

        $directory = $helper->getImageDir();

        $images = $helper->getFiles($directory);

        if (count($images) === 0) {
            $task->nextStep();
            $task->nextStep();
            $task->setMessage($helper->__('No image found'));

            return false;
        }

        /** @var $storageHelper Mage_Core_Helper_File_Storage_Database */
        $storageHelper = Mage::helper('core/file_storage_database');

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->setAllowCreateFolders(true);

        $destination = $helper->getBaseMediaPath();

        foreach ($images as $sku => $pictures) {

            $exists = $adapter->fetchOne(
                $adapter->select()
                    ->from($resource->getTable('catalog/product'), array($this->_zde(1)))
                    ->where('sku = ?', $sku)
                    ->limit(1)
            );

            if (!$exists) {
                continue;
            }

            $data    = array('code' => $sku);
            $gallery = array();

            foreach ($pictures as $key => $picture) {

                $ioAdapter->open(
                    array(
                        'path' => dirname($destination . $picture['name'])
                    )
                );

                if (Mage::getStoreConfig('pimdata/image/delete')) {
                    $ioAdapter->mv($picture['directory'] . $picture['file'], $destination . $picture['name']);
                } else {
                    $ioAdapter->cp($picture['directory'] . $picture['file'], $destination . $picture['name']);
                }
                $storageHelper->saveFile($destination . $picture['name']);

                if ($key === 0) {
                    $data['image'] = $picture['name'];
                    $data['small_image'] = $picture['name'];
                    $data['thumbnail'] = $picture['name'];
                }

                $gallery[] = $picture['name'];

            }

            $data['gallery'] = join(',', $gallery);

            $adapter->insert($this->getTable(), $data);
        }

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
        $this->getRequest()->matchEntity($this->getCode(), 'catalog/product', 'entity_id', null, false);

        return true;
    }

    /**
     * Set values (Step 4)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function setValues($task)
    {
        /* @var $helper Pimgento_Image_Helper_Data */
        $helper = Mage::helper('pimgento_image');

        $resource = $this->getResource();
        $adapter  = $this->getAdapter();

        $values = array(
            'image'       => 'image',
            'small_image' => 'small_image',
            'thumbnail'   => 'thumbnail',
        );

        $this->getRequest()->setValues($this->getCode(), 'catalog/product', $values, 4, 0);

        $attribute = $resource->getAttribute('media_gallery', 4);

        if (!$attribute) {
            $task->setMessage($helper->__('Attribute "media_gallery" not found'));
            return false;
        }

        $attributeId = $attribute['attribute_id'];

        $select = $adapter->select()->from($this->getTable(), array('entity_id', 'gallery'));

        $query = $adapter->query($select);

        while (($row = $query->fetch())) {

            $images = explode(',', $row['gallery']);

            foreach ($images as $image) {

                $values = array(
                   'attribute_id' => $attributeId,
                   'entity_id'    => $row['entity_id'],
                   'value'        => $image
                );

                $adapter->insertOnDuplicate(
                    $resource->getTable('catalog/product_attribute_media_gallery'), $values, array('value')
                );

            }

        }

        return true;
    }

    /**
     * Drop table (Step 5)
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
     * Reindex (Step 5)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function reindex($task)
    {
        if (!$this->getConfig('reindex')) {
            $task->setMessage(
                Mage::helper('pimgento_image')->__('Reindex is disabled')
            );
            return false;
        }

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

}