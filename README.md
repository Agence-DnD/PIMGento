# PIMGento

![alt text][logo]
[logo]: http://i.imgur.com/0KabJ2e.png "PIMGento : "

PIMGento is a Magento extension that allows you to import your catalog from Akeneo CSV files into Magento.


## How it works

PIMGento reads CSV files from Akeneo and insert data directly in Magento database.

In this way, it makes imports very fast and doesn't disturb your e-commerce website.

With PIMGento, you can import :
* Categories
* Families
* Attributes
* Options
* Products
* Products positions by category
* Images
* Stocks

## Features

* **Plug and Play:** We payed attention to the code and followed the Magento code instructions so you can easily install this extension on your store without any troubleshooting (see [installation](#installation)).

* **Totally flexible:** We added plenty of configurations. We didn't write any specific value in our code : No hard coding.

* **User Friendly:** We thought the interface the easiest possible. We developed a dashboard where you can see your import running. We even added some colors :)

* **Manual or automatic:** Free to choose ! You can either upload and import your CSV files from the Magento back-office or simply let the automatics tasks run different import with CSV files located in a directory of your server.

* **SEO ready:** 404 pages, it's over ! With PIMGento, you don't loose SEO on your product and category pages. If their names change, PIMGento create automatically a rewrite from the old URL to the new one.

* **Synchronous** / Asynchronous: You can import right now your data in order to show them on the front-office or schedule a task in the coming days. Very useful to prepare a marketing operation for instance.

* **Fast import:** PIMGento is from 10 to 20 times faster than API and native Magento import/export system.

* **Performance:** Imports with PIMGento don't slow down the store. Besides, you can select which cache you want to clear at the end of your import.

* **Multi-X:** PIMGento supports all types of Magento structure. If you have multi-website and multi-store with lot of different languages, imports will still work well.

## Demo / POC
* Magento setup with Akeneo sample data in less than [4 minutes !] (https://www.youtube.com/watch?v=MpC01qVIVFA )

<a href="http://www.youtube.com/watch?feature=player_embedded&v=MpC01qVIVFA
" target="_blank"><img src="http://img.youtube.com/vi/MpC01qVIVFA/0.jpg" 
alt="Video PIMGento for Akeneo" width="240" height="180" border="10" /></a>

* Configuration options for the backend [(Screenshots)](http://imgur.com/a/OUnNl)

## Requirements

* Akeneo 1.3 and 1.4
* Magento >= 1.9 CE
* Magento >= 1.14 EE
* Set local_infile mysql variable to TRUE
* Database encoding must be UTF-8
* Flash Player

With Akeneo 1.3 or 1.4, you need to install this Bundle (https://github.com/akeneo-labs/EnhancedConnectorBundle/) in order to generate appropriate CSV files for Magento.

> Compatibility for previous Magento versions are in development (see [roadmap](#roadmap))

## Installation

### Manually
* Copy the folder _app/code/community/Pimgento_ and paste it in the folder _app/code/community_
* Copy the folder _app/design/adminhtml/default/default/template/pimgento_ and paste it in the folder _app/design/adminhtml/default/default/template_
* Copy the file _app/design/adminhtml/default/default/layout/pimgento.xml_ and paste it in the folder _app/design/adminhtml/default/default/layout_
* Copy the file _app/etc/modules/Pimgento_All.xml_ and paste it in the folder app/etc/modules
* Copy the folder _skin/adminhtml/default/default/pimgento_ and paste it in the folder _skin/adminhtml/default/default_
* Clear the cache (System > Cache Management)
* Disconnect / reconnect to the Back Office
* Refresh Magento compilation (System > Tools > Compilation)

### Via composer

- Add into in the `require` section:

`"agence-dnd/pimgento":"dev-master"`

- Add into the `repositories` section:

```
	{
		"type": "vcs",
		"url": "git@github.com:Agence-DnD/PIMGento.git"
	}
```

### Via modman

`modman clone git@github.com:Agence-DnD/PIMGento.git`

## Configuration and Usage

* Allow magento to follow symlinks in "System > Advanced > Developer > Templates Settings" (set to "yes")
* Configure your store language and currency before import
* After category import, set the "Root Category" for store in "System > Manage Store"
* After attributes import, set attributes used to create configurable products

> All PIMGento configurations can be found in the Magento Back-end at this path:
**System > Configuration > Catalog > Pimgento**

* **General**
  * **_Enable Log_:_** if set Yes, write everything happens during the import in a file.
  * **_Log file:_** Log file name in var/log directory.
  * **_CSV line ending:_** Choose the character used to make a carriage return.
  * **_CSV delimiter:_** Choose the delimiter of your CSV files.
  * **_Admin language:_** Default language for admin values (products, categories, attributes, options). Example: en_US, de_DE, fr_FR...
  * **_Add website mapping:_** Match Magento website with PIM channel

* **Categories**
  * **_Enable Cron:_** if set Yes, you can enable the automatic import.
    * **_CRON expression:_** Configure when the automatic import will execute.
    * **_File:_** The filename of the CSV file used for the automatic import.
  * **_Clear cache:_** Choose which cache you want to clear after the import.
  * **_PIM code exclusion:_** PIM codes not to add in Magento, comma separated. Example: CAT01,CAT18,CAT56
  * **_Category depth:_** Choose the depth of your e-commerce navigation.
  * **_Is anchor:_** If set yes, all categories will be created as an anchor category.
  * **_Update url key:_** If set yes, it will create automatically an URL rewrite if the category name changed.

* **Families**
  * **_Enable Cron:_** if set Yes, you can enable the automatic import.
    * **_CRON expression:_** Configure when the automatic import will execute.
    * **_File:_** The filename of the CSV file used for the automatic import.
  * **_Clear cache:_** Choose which cache you want to clear after the import.
  * **_PIM code exclusion:_** PIM codes not to add in Magento, comma separated. Example: FAM01,FAM18,FAM56

* **Attributes**
  * **_Enable Cron:_** if set Yes, you can enable the automatic import.
    * **_CRON expression:_** Configure when the automatic import will execute.
    * **_File:_** The filename of the CSV file used for the automatic import.
  * **_Clear cache:_** Choose which cache you want to clear after the import.
  * **_PIM code exclusion:_** PIM codes not to add in Magento, comma separated. Example: ATT01,ATT18,ATT56
  * **_Specific types:_** You can make a mapping between PIM and MAGENTO attributes types.

* **Options**
  * **_Enable Cron:_** if set Yes, you can enable the automatic import.
    * **_CRON expression:_** Configure when the automatic import will execute.
    * **_File:_** The filename of the CSV file used for the automatic import.
  * **_Clear cache:_** Choose which cache you want to clear after the import.
  * **_PIM code exclusion:_** PIM codes not to add in Magento, comma separated. Example: OPT01,OPT18,OPT56

* **Products**
  * **_Enable Cron:_** if set Yes, you can enable the automatic import.
    * **_CRON expression:_** Configure when the automatic import will execute.
    * **_File:_** The filename of the CSV file used for the automatic import.
  * **_Clear cache:_** Choose which cache you want to clear after the import.
  * **_PIM code exclusion:_** PIM codes not to add in Magento, comma separated. Example: SKU01,SKU18,SKU56
  * **_Match attributes:_** You can make a mapping between PIM and MAGENTO attributes for simple product.
  * **_Default tax class:_** Choose the default tax class for each product imported.
  * **_Create Configurable:_** If set yes, it will create configurable product from the simple product data.
    * **_Configurable attributes:_** attributes to use for create configurable products
    * **_Configurable values:_** You can make a mapping between PIM and MAGENTO attributes for configurable product.

* **Images**
  * **_Enable Cron:_** if set Yes, you can enable the automatic import.
    * **_CRON expression:_** Configure when the automatic import will execute.
    * **_File:_** The filename of the CSV file used for the automatic import.
  * **_Delete image:_** If set Yes, delete all images used for the import.
  
* **Stock**
  * **_Enable Cron:_** if set Yes, you can enable the automatic import.
    * **_CRON expression:_** Configure when the automatic import will execute.
    * **_File:_** The filename of the CSV file used for the automatic import.

* **Product position**
  * **_Enable Cron:_** if set Yes, you can enable the automatic import.
    * **_CRON expression:_** Configure when the automatic import will execute.
    * **_File:_** The filename of the CSV file used for the automatic import.

> **NB**: PIMGento uses native Magento Cronjob, so you have nothing to add in your Crontab.

## Roadmap

* Compatibility with Magento >= 1.6 CE
* Compatibility with Magento >= 1.10 EE
* Create this type of product : Bundle, packed, virtual and downloadable products.
* Think about a way to delete data

## About us

Founded by lovers of innovation and design, [Agence Dn'D] (http://www.dnd.fr) assists companies for 11 years in the creation and development of customized digital (open source) solutions for web and E-commerce.
