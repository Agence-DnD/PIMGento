**Attributes**
===========

Configuration is available in Magento BackOffice under:
* System > Configuration > Catalogue > Pimgento > Attributes


| Configuration         | Usage                                                                                                          |
|-----------------------|----------------------------------------------------------------------------------------------------------------|
| Enable Cron           | Enable or disable cron for attributes PIMGento import                                                            |
| Cron expression       | Cron configuration (when your file will be imported in PIMGento)                                               |
| File                  | CSV file you want to import in /var/import/                                                                    |
| Clear cache           | Which caches you want to clear after the attributes import                                                     |
| Reindex Data          | Reindex data after the attributes import                                                                       |
| PIM code exclusion    | List of attributes codes from the file you don't want to import in Magento                                         |
| Specific types        | Mapping between Akeneo attributes type and Magento attributes type, in case of custom attribute type or others |
| Default Attribute Set | List of attributes set separated by comma in which you want to import attributes without Akeneo family         |