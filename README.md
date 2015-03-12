# PimGento
================
PimGento is a Magento extension that allows you to import your catalog from Akeneo CSV files into Magento.

## Requirements

* Magento >= 1.9 CE
* Magento >= 1.14 EE
* Set local_infile mysql variable to TRUE
* The PHP function system must be allowed on the server

Adaptations for other Magento versions are in development (see [roadmap](#roadmap))

## Install module in Magento

* Copy the folder app/code/community/Pimgento and paste it in the folder app/code/community
* Copy the folder app/design/adminhtml/default/default/template/pimgento and paste it in the folder app/design/adminhtml/default/default/template
* Copy the file app/design/adminhtml/default/default/layout/pimgento.xml and paste it in the folder app/design/adminhtml/default/default/layout
* Copier the file app/etc/modules/Pimgento_All.xml and paste it in the folder app/etc/modules
* Copy the folder skin/adminhtml/default/default/pimgento and paste it in the folder skin/adminhtml/default/default
* Clear the cache (System > Cache Management)
* Disconnect / reconnect to the Back Office
* Refresh Magento compilation (System > Tools > Compilation)

## CRONJOB

PimGento use Magento cronjob so you have nothing to add in your crontab.

## Roadmap

* Compatibility with Magento >= 1.4 CE
* Compatibility with Magento >= 1.8 EE
* Create this type of product : Bundle, packed, virtual and downloadable products.
* Think about a way to delete data
* Crossell management
