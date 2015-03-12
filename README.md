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

* Plug and Play : We payed attention to the code and followed the Magento code instructions so you can easly install this extension on your store without any troubleshooting (see [installation](#installation)).
* Totally flexible : We add plenty of configurations. We didn't write any specific value in our code : No hard coding.
* User Friendly : We think the interface the easiest possible. We developp a dashboard where you can see your import running. We even add some colors :)
* Manual or automatic : Free to choose ! You can either upload and import your CSV files from the Magento backoffice or simply let the automatics tasks run different import with CSV files located in a directory of your server.
* SEO ready : 404 pages, it's over ! With PimGento, you don't loose SEO on your product and category pages. If their names change, Pimgento create automatically a rewrite from the old URL to the new one.
* Synchrone / Asynchrone : We can import right now your data in order to show them on the fron-office or schedule a task in the coming days. Very usefull to prepare a marketing operation for instance.
* Fast import : PimGento is from 10 to 20 times faster than API or import system deliver with Magento.
* Performance : Imports with PimGento don't slow down the store. Besides, you can select which cache you want to clear at the end of your import.
* Multi-X : PimGento supports all types of Magento structure. If you had multi-website and multi-store with lot of different languages, imports still work well.

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

* PimGento use Magento cronjob so you have nothing to add in your crontab.

## Roadmap

* Compatibility with Magento >= 1.4 CE
* Compatibility with Magento >= 1.8 EE
* Create this type of product : Bundle, packed, virtual and downloadable products.
* Think about a way to delete data
* Crossell management