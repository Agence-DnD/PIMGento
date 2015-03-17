# PimGento

PimGento is a Magento extension that allows you to import your catalog from Akeneo CSV files into Magento.

## How it works

Pimgento reads CSV files from Akeneo and insert data directly in Magento database.
In this way, it makes imports very fast and doesn't disturb your e-commerce website.
With PimGento, you can import these informations :
* Categories
* Families
* Attributes
* Options
* Products
* Products positions by category
* Images
* Stocks

## Features

* Plug and Play : We payed attention to the code and followed the Magento code instructions so you can easily install this extension on your store without any troubleshooting (see [installation](#installation)).
* Totally flexible : We added plenty of configurations. We didn't write any specific value in our code : No hard coding.
* User Friendly : We thought the interface the easiest possible. We developed a dashboard where you can see your import running. We even added some colors :)
* Manual or automatic : Free to choose ! You can either upload and import your CSV files from the Magento back-office or simply let the automatics tasks run different import with CSV files located in a directory of your server.
* SEO ready : 404 pages, it's over ! With PimGento, you don't loose SEO on your product and category pages. If their names change, Pimgento create automatically a rewrite from the old URL to the new one.
* Synchrone / Asynchrone : You can import right now your data in order to show them on the front-office or schedule a task in the coming days. Very usefull to prepare a marketing operation for instance.
* Fast import : PimGento is from 10 to 20 times faster than API and native Magento import/export system.
* Performance : Imports with PimGento don't slow down the store. Besides, you can select which cache you want to clear at the end of your import.
* Multi-X : PimGento supports all types of Magento structure. If you have multi-website and multi-store with lot of differents languages, imports will still work well.

## Requirements

* Magento >= 1.9 CE
* Magento >= 1.14 EE
* Set local_infile mysql variable to TRUE
* The PHP function system must be allowed on the server

Adaptations for other Magento versions are in development (see [roadmap](#roadmap))

## Installation

* Copy the folder app/code/community/Pimgento and paste it in the folder app/code/community
* Copy the folder app/design/adminhtml/default/default/template/pimgento and paste it in the folder app/design/adminhtml/default/default/template
* Copy the file app/design/adminhtml/default/default/layout/pimgento.xml and paste it in the folder app/design/adminhtml/default/default/layout
* Copier the file app/etc/modules/Pimgento_All.xml and paste it in the folder app/etc/modules
* Copy the folder skin/adminhtml/default/default/pimgento and paste it in the folder skin/adminhtml/default/default
* Clear the cache (System > Cache Management)
* Disconnect / reconnect to the Back Office
* Refresh Magento compilation (System > Tools > Compilation)

## Configuration and Usage

All PimGento configurations can be found in the Magento back-office at this path :
System>Configuration>Catalog>PIM

* General
  * Active Log : if set Yes, write everything happens during the import in a file.
  * CSV lines terminated by : Choose the character used to make a carriage return.
  * CSV fields terminated by : Choose the delimiter of your CSV files.

* Categories
  * Active cron : if set Yes, you can enable the automatic import.

**NB** : PimGento use Magento cronjob so you have nothing to add in your crontab.

## Roadmap

* Compatibility with Magento >= 1.4 CE
* Compatibility with Magento >= 1.8 EE
* Create this type of product : Bundle, packed, virtual and downloadable products.
* Think about a way to delete data
* Crossell and upsell management