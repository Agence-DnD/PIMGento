How to...
========
##### Q: How to install PIMGento ?
**A**: You can install PIMGento in three differents ways:

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

- In your ```composer.json```, add the following code:

```json
{
    "agence-dnd/pimgento":"1.3.2.b"
}
```

- Next, enter the following command line:
```console
$ php composer.phar require agence-dnd/pimgento
```
### Via modman
```
modman clone git@github.com:Agence-DnD/PIMGento.git
```

#### Q: How to configure PIMGento ?
**A**: Before starting to use PIMGento, few steps are require to set it right:
* Allow magento to follow symlinks in "System > Advanced > Developer > Templates Settings" (set to "yes")
* Configure your store language and currency before import
* After category import, set the "Root Category" for store in "System > Manage Store"
* After attributes import, set attributes used to create configurable products

...and you are good to go! Just check the [configuration](../configuration/configuration.md) to be ready to import your data the right way!

#### Q: How to import my data into PIMGento ?
**A**: You can import your data using two differents ways:
* Using the [interface](../functionnalities/pimgento_interface.md)
* Using [cron tasks](../functionnalities/pimgento_cron.md)

But before using one of these methods be sure to read this [quick guide](../functionnalities/pimgento_import.md) about the import system.


#### Q: How to create configurable products in PIMGento ?
**A**: A configurable product is made to manage a group of products that are reunited under the same name, and that will have several common attributes but some axis of variation. This will allow you to fill in only one product sheet for multiple skus, to manage them from one central place.

1. Go to System / Configuration. Within Catalog sub-section, please go to Catalog / Pimgento / Products. Before any import, you will have to manually set a few attributes as below in this section:
- enabled => status
- description => short_description
- sku => url_key

2. On the same page, make sure « Create configurable » field is set as « Yes ».

3. On the same page, make sure « Variant import for axis » is set as « No ».

4. Select the configurable attributes you want to use for configurable products in the dropdown list. To enable attributes in that list, go to Catalog / Attributes / Manage Attributes, select the attributes and set them as « Yes, I use to create configurable product ».

5. Under Catalog section within the dropdown menu in the top nav bar, click on Import / PIM Data. Now, select the files to upload in your browser and import each file one by one. And then, you’re all set!

#### Q: How to customize PIMGento ?
**A**: If even the multiple configuration of PIMGento doesn't suit your business logic, or if you want to have other possibilities in import, you can always override PIMGento as it is completly Open Source. Just keep in mind a few things before beginning to develop your own logic:
* Observers define each task for a given import, if you want to add a task you should declaring a new method in the corresponding Import class and adding to the Observer.
* One method in Import class = One task
* There is no data transfer between tasks

Note that if you judge your feature can be used by others, and if you respect this logic, we will be glad to add it to PIMGento: just make us a PR!

#### Q: How to contribute to PIMGento ?
**A**: You can contribute to PIMGento by submitting PR on Github. However, you need to respect a few criteria:
* Respect PIMGento logic and architecture
* Be sure to not break others features
* Submit a clean code
* Always update the documentation if you submit a new feature
