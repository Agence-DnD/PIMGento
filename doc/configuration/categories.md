**Categories**
===========

Configuration is available in Magento BackOffice under:
* System > Configuration > Catalogue > Pimgento > Categories

| Configuration                | Usage                                                                                                                                                                                        |
|------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Enable Cron                  | Enable or disable cron for categories PIMGento import                                                                                                                                        |
| Cron expression              | Cron configuration (when your file will be imported in PIMGento)                                                                                                                             |
| File                         | CSV file you want to import in /var/import/                                                                                                                                                  |
| Clear cache                  | Which caches you want to clear after the categories import                                                                                                                                   |
| Reindex Data                 | Reindex data after the categories import                                                                                                                                                     |
| PIM code exclusion           | List of category codes from the file you don't want to import in Magento                                                                                                                     |
| Maximum depth for categories | Categories in the file that are in a superior depth than set in this field will not be imported                                                                                              |
| Is anchor                    | Default is to set the anchor attribute of categories to "Yes", allowing the use of filters on all categories pages. The parent categories will display all products in the child categories. |
| Update URL Key               | Update URL key when category name is updated, a redirect from the old url is created                                                                                                         |