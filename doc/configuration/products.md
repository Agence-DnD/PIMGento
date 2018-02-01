**Products**
===========

Configuration is available in Magento BackOffice under:
* System > Configuration > Catalogue > Pimgento > Products


| Configuration                                | Usage                                                                                                                    |
|----------------------------------------------|--------------------------------------------------------------------------------------------------------------------------|
| Enable Cron                                  | Enable or disable cron for products PIMGento import                                                                      |
| Cron expression                              | Cron configuration (when your file will be imported in PIMGento)                                                         |
| File                                         | CSV file you want to import in /var/import/                                                                              |
| Clear cache                                  | Which caches you want to clear after the products import                                                                 |
| Reindex Data                                 | Reindex data after the products import                                                                                   |
| PIM code exclusion                           | List of products codes from the file you don't want to import in Magento                                                 |
| Match attributes                             | Mapping between Akeneo attributes and Magento attributes                                                                 |
| Default Tax Class                            | Magento tax class used                                                                                                   |
| Add store to URL key                         | Add the store code to the product url key to avoid key duplication                                                       |
| Create configurable                          | Enable the creation of configurable products                                                                             |
| Configurable attributes                      | Attributes used for variation axis in configurable products if the variant import is disable                             |
| Default configurable attributes values       | Use the value column to force data for specific attribute. Leave empty to retrieve the value of the first simple product |
| Disable attributes updating for configurable | Attributes you want to disable the update in case you don't want to erase data