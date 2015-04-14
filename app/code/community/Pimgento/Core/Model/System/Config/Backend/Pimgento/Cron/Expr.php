<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Model_System_Config_Backend_Pimgento_Cron_Expr extends Mage_Core_Model_Config_Data
{

    /**
     * After save config
     *
     * @return Mage_Core_Model_Abstract|void
     * @throws Exception
     */
    protected function _afterSave()
    {
        preg_match('/^(.*)\/(?P<code>.*)\/(.*)$/', $this->getPath(), $match);

        if (isset($match['code'])) {

            $modelPath  = 'crontab/jobs/pimdata_' . $match['code'] . '_cron/run/model';
            $stringPath = 'crontab/jobs/pimdata_' . $match['code'] . '_cron/schedule/cron_expr';

            try {
                Mage::getModel('core/config_data')
                    ->load($stringPath, 'path')
                    ->setValue($this->getValue())
                    ->setPath($stringPath)
                    ->save();

                Mage::getModel('core/config_data')
                    ->load($modelPath, 'path')
                    ->setValue((string)Mage::getConfig()->getNode($modelPath))
                    ->setPath($modelPath)
                    ->save();

                Mage::app()->cleanCache(array('config'));

            } catch (Exception $e) {
                throw new Exception(Mage::helper('cron')->__('Can not save the cron expression'));
            }

        }

    }
}
