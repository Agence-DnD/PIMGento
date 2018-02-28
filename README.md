# PIMGento

PIMGento is a Magento extension that allows you to import your catalog from Akeneo CSV files into Magento.

You can discover PIMGento on the official website (https://www.pimgento.com/docs/v1.0/).

## Documentation

PIMGento complete documentation is available [here](doc/summary.md).

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
* Variants
* Stocks
* Assets (Akeneo E.E >= 1.5)

## Features

* **Plug and Play:** We payed attention to the code and followed the Magento code instructions so you can easily install this extension on your store without any troubleshooting.

* **Totally flexible:** We added plenty of configurations. We didn't write any specific value in our code : No hard coding.

* **User Friendly:** We thought the interface the easiest possible. We developed a dashboard where you can see your import running. We even added some colors :)

* **Manual or automatic:** Free to choose ! You can either upload and import your CSV files from the Magento back-office or simply let the automatics tasks run different import with CSV files located in a directory of your server.

* **SEO ready:** 404 pages, it's over ! With PIMGento, you don't loose SEO on your product and category pages. If their names change, PIMGento create automatically a rewrite from the old URL to the new one.

* **Synchronous** / Asynchronous: You can import right now your data in order to show them on the front-office or schedule a task in the coming days. Very useful to prepare a marketing operation for instance.

* **Fast import:** PIMGento is from 10 to 20 times faster than API and native Magento import/export system.

* **Performance:** Imports with PIMGento don't slow down the store. Besides, you can select which cache you want to clear at the end of your import.

* **Multi-X:** PIMGento supports all types of Magento structure. If you have multi-website and multi-store with lot of different languages, imports will still work well.

## Demo / POC
* Magento setup with Akeneo sample data in less than [4 minutes !](https://www.youtube.com/watch?v=MpC01qVIVFA)

<a href="http://www.youtube.com/watch?feature=player_embedded&v=MpC01qVIVFA
" target="_blank"><img src="http://img.youtube.com/vi/MpC01qVIVFA/0.jpg" 
alt="Video PIMGento for Akeneo" width="240" height="180" border="10" /></a>

* Configuration options for the backend [(Screenshots)](http://imgur.com/a/OUnNl)

## Requirements

* Akeneo PIM >= 1.3 (CE & EE)
* Akeneo Enhanced Connector
* Magento >= 1.9 CE
* Magento >= 1.14 EE
* Set local_infile mysql variable to TRUE
* Database encoding must be UTF-8
* Flash Player

## Installation, Configuration and Usage

If you want to know how to install, configure or use PIMGento, please check [how to...](doc/important_stuff/how_to.md) section. We advise you to start here!

## Roadmap

We have updated our roadmap. Just go [here](doc/important_stuff/roadmap.md).

## About us

Founded by lovers of innovation and design, [Agence Dn'D] (http://www.dnd.fr) assists companies for 11 years in the creation and development of customized digital (open source) solutions for web and E-commerce.
