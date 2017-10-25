# Magento2 Top Products API

This is a module that extends Magento2 API to get list of TOP products by next filters:
 - TOP selling products
 - TOP Free products
 - TOP Rated products
 
Top selling and free products are fetching from Magento2 reports.

Top rated products are fetching from Rates module and results are based on aggregated data.
 
## Install
```bash
$ composer require oxcom/magento2-top-products
$ bin/magento module:enable OxCom_MagentoTopProducts
$ bin/magento setup:upgrade
$ bin/magento setup:di:compile
```

## API requests
```GET /V1/products/top/{type}``` - Get list of top products by type.
Where ```type``` can be:
- **selling** - TOP selling products
- **free** - TOP Free products
- **rated** - TOP Rated products

###### Search criteria params
**pageSize** - Page size

**currentPage** - Current page

**period** - filter by period. This options is related only for ```selling``` or ```free``` type. Possible values are:
- yearly - annual report
- monthly - monthly report
- daily - daily report

**ratingCode** - filter by rating type. This options is related ony for ```rated``` type. Possible values can be found in ```rating``` table.

## Dependencies
This module is using exists functionality of next modules:
- **magento/module-catalog**
- **magento/module-review**
- **magento/module-sales**

## Bugs and Issues
Please, if You found a bug or something, that is not working properly, contact me and tell what's wrong. It's nice to have an example how to reproduce a bug, or any idea how to fix it in Your request. I'll take care about it ASAP.
