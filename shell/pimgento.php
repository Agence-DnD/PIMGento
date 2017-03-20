<?php
/**
 * A shell script to run the Pimgento imports.  Largely lifted from code in Pimgento/Core/Model/Cron.php
 * Martin Hopkins - 23/6/2016
 */

require_once('abstract.php');

/**
 * Class Mage_Shell_DataflowExport
 */
class Mage_Shell_DataflowExport extends Mage_Shell_Abstract
{

    public function run() {
        $type = $this->getArg('type');
        if (!$this->getArg('type')) {
            usage();
            return;
        }
        if (!in_array ($type, array('product','variant','option','family','image', 'category', 'attribute'))) {
            echo 'Type "' . $type . '" is invalid' . newline();
            usage();
            return;
        }
        $file = $this->getArg('file');
        pimgentoImport($type, $file);
    }
}

$shell = new Mage_Shell_DataflowExport();
$shell->run();

/**
 * Function to perform Pimgento import tasks
 *
 * @param $type - file type to load, one of product, variant, option, image etc
 * @param $file
 */
function pimgentoImport($type, $file = null)
{
    $command = 'pimgento_' . $type;
    if (strcmp($type, 'image') !== 0 && empty($file)) {
        $file = Mage::getStoreConfig('pimdata/' . $type . '/cron_file');

        if (!$file) {
            echo 'No file configured in Pimgento cron option for ' . $type . ' - exiting' . newline();
            return;
        }
    }
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    set_time_limit(0);

    umask(0);

    $helper = Mage::helper('pimgento_core');

    try {
        /** @var Pimgento_Core_Model_Task $model */
        $model = Mage::getSingleton('pimgento_core/task');
        $task = $model->load($command);
        if (!empty($file)) {
            $task->setFile(null);
            $task->setFile($helper->getCronDir() . $file);

            if (!is_file($task->getFile())) {
                echo 'File ' . $task->getFile() . ' does not exist - exiting' . newline();
                return;
            }
            echo 'Processing File ' . $task->getFile() . newline();
        }
        $task->setNoReindex(false);
        echo 'Executing task to import ' . $command . newline();
        echo newline();
        while (!$task->taskIsOver()) {
            $task->execute();
            echo $task->getStepComment() . newline();
            echo $task->getMessage() . newline();
            $task->nextStep();
        }
        echo newline();

        echo "Task " . $command . " complete" . newline();
    } catch (Exception $e) {
        echo $e->getMessage() . newline();
    }
}

function newline() {
    return (PHP_SAPI === 'cli' ? "\r\n" : '<br/>');
}

function usage() {
    echo "Usage: php -f pimgentoImport.php -type type [-file filename]\r\n";
    echo "Where: type is 'product', 'image', 'variant', 'option', 'family', 'category'\r\n";
    echo "       file is the file to load\r\n";
}