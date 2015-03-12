<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Core_Block_Adminhtml_Options extends Mage_Adminhtml_Block_Template
{

    /**
     * Retrieve task comment
     *
     * @return string
     */
    public function getComment()
    {
        $tasks   = $this->getTasks();
        $command = $this->getCommand();

        if (isset($tasks[$command]['comment'])) {
            return $tasks[$command]['comment'];
        }

        return '';
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    public function getOptions()
    {
        $tasks   = $this->getTasks();
        $command = $this->getCommand();

        if (isset($tasks[$command]['options'])) {
            return $tasks[$command]['options'];
        }

        return array();
    }

    /**
     * Retrieve All Tasks
     *
     * @return array
     */
    public function getTasks()
    {
        /* @var $helper Pimgento_Core_Helper_Data */
        $helper = Mage::helper('pimgento_core');

        return $helper->getTasks();
    }

}