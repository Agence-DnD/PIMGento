<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Asset_Model_System_Config_Source_Connexion
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
                'value' => 'ftp',
                'label' => 'FTP',
            ),
            array(
                'value' => 'sftp',
                'label' => 'SFTP',
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
            'ftp'  => 'FTP',
            'sftp' => 'SFTP',
        );

        return $options;
    }

}
