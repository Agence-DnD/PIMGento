<?php
/**
 * @author    Rico Neitzel, Büro 71a <info@buro71a.de>
 * @copyright Copyright (c) 2017 Büro 71a, Neitzel und Klose GbR (buro71a.de)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Asset_Model_System_Config_Source_Connexion_Type
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array(
                'value' => 'password',
                'label' => Mage::helper('core')->__('Password'),
            ),
            array(
                'value' => 'key',
                'label' => Mage::helper('core')->__('Key file'),
            ),
        );

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = array(
            'password'  => Mage::helper('core')->__('Password'),
            'key' => Mage::helper('core')->__('Key file'),
        );

        return $options;
    }

}
