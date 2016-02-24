<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Stock_Model_Cron extends Pimgento_Core_Model_Cron
{

    /**
     * Cron job method
     *
     * @param Mage_Cron_Model_Schedule $schedule
     *
     * @return $this
     */
    public function run(Mage_Cron_Model_Schedule $schedule)
    {
        if (!Mage::getStoreConfig('pimdata/stock/cron_enabled')) {
            return $this;
        }

        $files = $this->getFiles('stock');

        if (count($files)) {
            foreach ($files as $key => $file) {
                $this->launch('update_stock', $file, ($key == count($files) - 1), $schedule);
            }
        }

        return $this;
    }

}