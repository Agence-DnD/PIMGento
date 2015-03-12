<?php
/**
 * @author    Agence Dn'D <magento@dnd.fr>
 * @copyright Copyright (c) 2015 Agence Dn'D (http://www.dnd.fr)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Pimgento_Image_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * Retrieve image directory
     *
     * @return string
     */
    public function getImageDir()
    {
        $directory = Mage::getBaseDir('media') . DS . 'import' . DS . 'files';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $directory;
    }

    /**
     * Retrieve product base media path
     *
     * @return string
     */
    public function getBaseMediaPath()
    {
        /* @var $media Mage_Catalog_Model_Product_Media_Config */
        $media = Mage::getSingleton('catalog/product_media_config');

        return $media->getBaseMediaPath();
    }

    /**
     * Count files recursively for given directory
     *
     * @param string $directory
     * @param array  $files
     * @param int    $level
     * @param string $sku
     *
     * @return array
     */
    public function getFiles($directory, $files = array(), $level = 0, $sku = '')
    {
        $level++;
        $handle = opendir($directory);

        while (($file = readdir($handle))) {
            if ($file != "." and $file != "..") {
                if (is_dir($directory . DS . $file)) {
                    if ($level == 1) {
                        $sku = $file;
                        $files[$sku] = array();
                    }
                    $files = $this->getFiles($directory . DS . $file, $files, $level, $sku);

                    if (isset($files[$sku])) {
                        if (!count($files[$sku])) {
                            unset($files[$sku]);
                        }
                    }

                } else {
                    $extension = pathinfo($directory . DS . $file, PATHINFO_EXTENSION);
                    $name      = strtolower($sku . '-' . (count($files[$sku]) + 1) . '.' . $extension);
                    $fileName  = Mage_Core_Model_File_Uploader::getCorrectFileName($name);
                    $path      = Mage_Core_Model_File_Uploader::getDispretionPath($fileName);
                    $fileName  = $path . DS . $fileName;

                    $files[$sku][] = array(
                        'directory' => $directory . DS,
                        'file'      => $file,
                        'name'      => $fileName,
                    );
                }
            }

        }

        closedir($handle);

        return $files;
    }

}