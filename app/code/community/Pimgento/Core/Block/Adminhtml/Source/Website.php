<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Block_Adminhtml_Source_Website extends Mage_Adminhtml_Block_Abstract
{

    /**
     * @var string
     */
    protected $_template = 'pimgento/core/website.phtml';

    /**
     * Retrieve websites
     *
     * @return array
     */
    public function getWebsites()
    {
        return Mage::app()->getWebsites();
    }

}