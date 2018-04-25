Compatibility with...
=====================

Akeneo
------

* PIMGento is compatible with the [Enhanced Connector Bundle](https://github.com/akeneo-labs/EnhancedConnectorBundle) for the following versions of Akeneo:
    + v1.7.*
    + v1.6.*
    + v1.5.*
    + v1.4.*
    + v1.3.*


* Among Akeneo versions, Enhanced Connector Bundle files can be integrated natively. For example, if your PIM is in 1.7, the Enhanced Connector Bundle only export family and attribute, but don't worry it's because others files are now natively compatible with PIMGento, so you can just use natives exports!

* PIMGento **is not compatible** with the [Inner Variation Bundle](https://marketplace.akeneo.com/package/inner-variation-bundle-ee-only).

* PIMGento is compatible with both Akeneo CE and EE.

Magento
-------

* PIMGento is compatible with Magento following this schema:

| Magento Version | PIMGento Version |
|-----------------|------------------|
| < 1.9.3         | 1.X.X.b          |
| >= 1.9.3        | 1.X.X.a          |

__Note__: Magento 1.9.3 dropped Flash support in Mage_Uploader module which have been used by PIMGento. That's why we have two versions of the module (for PIMGento 1.3.2.b you need to enable Flash in your browser).
