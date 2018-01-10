<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Asset_Model_Import extends Pimgento_Core_Model_Import_Abstract
{

    /**
     * @var string
     */
    protected $_code = 'asset';

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
                Mage::helper('pimgento_asset')->__(
                    'No data to insert, verify the file is not empty or CSV configuration is correct'
                )
            );
        }

        $task->setMessage(
            Mage::helper('pimgento_asset')->__('%s lines found', $lines)
        );

        return true;
    }

    /**
     * Update columns (Step 3)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateColumns($task)
    {
        $adapter  = $this->getAdapter();
        $resource = $this->getResource();

        /* Clean Up */
        $adapter->delete($this->getTable(), array('reference_file = ""'));

        /* Replace channel with website id */
        $websites = unserialize(Mage::getStoreConfig('pimdata/general/websites'));

        $codes = $adapter->fetchPairs(
            $adapter->select()->from($resource->getTable('core/website'), array('code', 'website_id'))
        );

        $adapter->addColumn($this->getTable(), 'website_id', 'INT(11) NULL');

        if (is_array($websites)) {
            foreach ($websites as $match) {
                if (isset($codes[$match['website']])) {
                    $adapter->update(
                        $this->getTable(),
                        array('website_id' => $codes[$match['website']]),
                        array('channel = ?' => $match['channel'])
                    );
                }
            }
        }

        /* Clean up */
        $adapter->delete($this->getTable(), array('website_id IS NULL'));

        /* Set store ids with local */
        $adapter->addColumn($this->getTable(), 'store_id', 'VARCHAR(255) NULL');

        $stores = $adapter->fetchPairs(
            $adapter->select()
                ->from(
                    $resource->getTable('core/store'),
                    array('website_id', $this->_zde('GROUP_CONCAT(`store_id` SEPARATOR ",")'))
                )
                ->where('website_id <> ?', 0)
                ->group('website_id')
        );

        foreach ($stores as $websiteId => $storeIds) {
            $adapter->update(
                $this->getTable(),
                array('store_id' => $storeIds),
                array('website_id = ?' => $websiteId, 'locale = ""')
            );
        }

        foreach ($stores as $websiteId => $storeIds) {
            $ids = explode(',', $storeIds);
            foreach ($ids as $storeId) {
                $local = $adapter->fetchOne(
                    $adapter->select()
                        ->from($resource->getTable('core/config_data'), array('value'))
                        ->where('path = ?', 'general/locale/code')
                        ->where('scope_id = "' . $storeId . '" OR scope_id = 0')
                        ->order('scope_id DESC')
                        ->limit(1)
                );
                $adapter->update(
                    $this->getTable(),
                    array('store_id' => $storeId),
                    array('website_id = ?' => $websiteId, 'locale = ?' => $local)
                );
            }
        }

        /* Clean up */
        $adapter->delete($this->getTable(), array('store_id IS NULL'));

        return true;
    }

    /**
     * Update table (Step 4)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function updateTable($task)
    {
        $adapter  = $this->getAdapter();

        /* @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');

        /* Clean up table */
        $adapter->truncateTable($resource->getTableName('pimgento_asset'));

        /* Insert data from tmp table */
        $select = $adapter->select()->from(
            $this->getTable(),
            array('asset', 'website_id', 'store_id', 'reference_file', 'variation_file')
        );

        $query = $adapter->query($select);

        while (($row = $query->fetch())) {

            $stores = explode(',', $row['store_id']);

            foreach ($stores as $storeId) {

                $pathInfo = pathinfo($row['reference_file']);
                $file = Mage_Core_Model_File_Uploader::getDispretionPath($pathInfo['basename']) . DS . $pathInfo['basename'];

                $data = array(
                    'asset'       => $row['asset'],
                    'website_id'  => $row['website_id'],
                    'store_id'    => $storeId,
                    'file'        => $row['reference_file'],
                    'variation'   => $row['variation_file'],
                    'image'       => $file,
                    'small_image' => $file,
                    'thumbnail'   => $file,
                );

                $adapter->insert($resource->getTableName('pimgento_asset'), $data);
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

        Mage::dispatchEvent('task_executor_drop_table_after', array('task' => $task));

        return true;
    }

    /**
     * Download Images (Step 6)
     *
     * @param Pimgento_Core_Model_Task $task
     *
     * @return bool
     */
    public function downloadImages($task)
    {
        $adapter  = $this->getAdapter();

        /* @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');

        try {
            $connexion = Mage::getStoreConfig('pimdata/asset/connexion');

            $select = $adapter->select()->from(
                $resource->getTableName('pimgento_asset'),
                array('file', 'image')
            );

            $query = $adapter->query($select);

            $directory = Mage::helper('pimgento_asset')->getBaseMediaPath();

            if ($connexion == 'ftp' || $connexion == 'sftp') {

                $ftp = null;

                if ($connexion == 'ftp') {
                    /* @var $ftp Varien_Io_Ftp */
                    $ftp = new Varien_Io_Ftp();

                    $config = array(
                        'host'     => Mage::getStoreConfig('pimdata/asset/host'),
                        'user'     => Mage::getStoreConfig('pimdata/asset/user'),
                        'password' => Mage::getStoreConfig('pimdata/asset/ftp_password'),
                    );

                    if (Mage::getStoreConfig('pimdata/asset/directory')) {
                        $config['path'] = Mage::getStoreConfig('pimdata/asset/directory');
                    }

                    if (Mage::getStoreConfig('pimdata/asset/passive')) {
                        $config['passive'] = true;
                    }

                    $ftp->open($config);
                }

                if ($connexion == 'sftp') {
                    /* @var $ftp Varien_Io_Sftp */
                    $ftp = new Varien_Io_Sftp();

                    $config = array(
                        'host'     => Mage::getStoreConfig('pimdata/asset/host'),
                        'username' => Mage::getStoreConfig('pimdata/asset/user'),
                        'password' => Mage::getStoreConfig('pimdata/asset/ftp_password'),
                    );

                    $ftp->open($config);

                    if (Mage::getStoreConfig('pimdata/asset/directory')) {
                        $ftp->cd(Mage::getStoreConfig('pimdata/asset/directory'));
                    }
                }

                if ($ftp) {
                    while (($row = $query->fetch())) {
                        if (is_file($directory . $row['image'])) {
                            continue;
                        }
                        $dir = dirname($directory . $row['image']);
                        if (!is_dir($dir)) {
                            mkdir($dir, 0777, true);
                        }
                        $ftp->read($row['file'], $directory . $row['image']);
                    }

                    $ftp->close();
                }

            } elseif ($connexion == 'scp') {
                $connexionType = Mage::getStoreConfig('pimdata/asset/connexion_type');

                if($connexionType == 'password'){
                    $connection = ssh2_connect(Mage::getStoreConfig('pimdata/asset/host'), 22);
                    ssh2_auth_password(
                        $connection,
                        Mage::getStoreConfig('pimdata/asset/user'),
                        Mage::getStoreConfig('pimdata/asset/scp_password')
                    );
                }else{
                    $connection = ssh2_connect(Mage::getStoreConfig('pimdata/asset/host'), 22, array('hostkey'=>'ssh-rsa'));
                    ssh2_auth_pubkey_file(
                        $connection,
                        Mage::getStoreConfig('pimdata/asset/user'),
                        Mage::getBaseDir('var') . DS . Mage::getStoreConfig('pimdata/asset/ssh_public_key'),
                        Mage::getBaseDir('var') . DS . Mage::getStoreConfig('pimdata/asset/ssh_private_key'),
                        Mage::getStoreConfig('pimdata/asset/ssh_passphrase')
                    );
                }

                while (($row = $query->fetch())) {
                    if (is_file($directory . $row['image'])) {
                        continue;
                    }
                    $dir = dirname($directory . $row['image']);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    ssh2_scp_recv(
                        $connection,
                        Mage::getStoreConfig('pimdata/asset/directory') . $row['file'],
                        $directory . $row['image']
                    );
                }

            } else {
                $task->error(
                    Mage::helper('pimgento_asset')->__(
                        'Connexion type %s is not authorised', $connexion
                    )
                );
            }

        } catch (Exception $e) {
            $task->error(Mage::helper('pimgento_asset')->__($e->getMessage()));
        }

        return true;
    }

}