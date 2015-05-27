<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * Retrieve Stores languages
     *
     * @return array
     */
    public function getStoresLang()
    {
        $stores = Mage::app()->getStores();

        $adminLang = Mage::getStoreConfig('pimdata/general/admin_lang');

        if (!$adminLang) {
            $adminLang = 'en_US';
        }

        $lang = array($adminLang => array(0));

        foreach ($stores as $store) {
            $local = Mage::getStoreConfig('general/locale/code', $store->getId());

            if (!isset($lang[$local])) {
                $lang[$local] = array();
            }

            $lang[$local][] = $store->getId();
        }

        return $lang;
    }

    /**
     * Retrieve Stores currency
     *
     * @return array
     */
    public function getStoresCurrency()
    {
        $stores = Mage::app()->getStores();

        $default = Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE, 0);

        if (!$default) {
            $default = 'USD';
        }

        $currencies = array(
            $default => array(
                array('id' => 0, 'code' => 'admin')
            )
        );

        foreach ($stores as $store) {
            $currency = Mage::getStoreConfig('currency/options/default', $store->getId());

            if (!isset($currencies[$currency])) {
                $currencies[$currency] = array();
            }

            $code = $this->getChannel($store->getWebsite()->getCode());

            $currencies[$currency][] = array(
                'id'   => $store->getId(),
                'code' => $code,
            );
        }

        return $currencies;
    }

    /**
     * Retrieve Websites and Stores
     *
     * @return array
     */
    public function getStoresWebsites()
    {
        $stores = Mage::app()->getStores();

        $websites = array();

        foreach ($stores as $store) {
            $code = $this->getChannel($store->getWebsite()->getCode());

            $local = Mage::getStoreConfig('general/locale/code', $store->getId());

            if (!isset($websites[$code])) {
                $websites[$code] = array();
            }
            if (!isset($websites[$local . '-' . $code])) {
                $websites[$local . '-' . $code] = array();
            }

            $websites[$code][] = $store->getId();
            $websites[$local . '-' . $code][] = $store->getId();
        }

        return $websites;
    }

    /**
     * Retrieve channel with website code
     *
     * @param string $code
     *
     * @return string
     */
    public function getChannel($code)
    {
        if (Mage::getStoreConfig('pimdata/general/websites')) {
            $websites = unserialize(Mage::getStoreConfig('pimdata/general/websites'));

            if (is_array($websites)) {
                foreach ($websites as $match) {
                    if ($code == $match['website']) {
                        $code = $match['channel'];
                        break;
                    }
                }
            }
        }

        return $code;
    }

    /**
     * Retrieve Tasks
     *
     * @return array
     */
    public function getTasks()
    {
        /* @var $task Pimgento_Core_Model_Task */
        $task = Mage::getSingleton('pimgento_core/task');

        return $task->getTasks();
    }

    /**
     * Retrieve allowed extensions for uploader
     *
     * @return array
     */
    public function getAllowedExtensions()
    {
        return array('csv', 'txt');
    }

    /**
     * Retrieve Log Directory
     *
     * @return string
     */
    public function getLogDir()
    {
        return $this->_createDirectory(
            Mage::getBaseDir('var') . DS . 'log' . DS
        );
    }

    /**
     * Retrieve File Cron Directory
     *
     * @return string
     */
    public function getCronDir()
    {
        return $this->_createDirectory(
            Mage::getBaseDir('var') . DS . 'import' . DS
        );
    }

    /**
     * Retrieve File Upload Directory
     *
     * @return string
     */
    public function getUploadDir()
    {
        return $this->_createDirectory(
            Mage::getBaseDir('var') . DS . 'import' . DS . 'pim' . DS
        );
    }

    /**
     * Create directory if not exists
     *
     * @param string $directory
     *
     * @return string
     */
    protected function _createDirectory($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $directory;
    }

}