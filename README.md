# magento2-patches

This module makes it easier for a Magento 2 merchant to apply patches in a manner which adheres to standard Composer workflow and many deploymenty pipleline methodologies.

Core Magento Security patches are split and re-defined so that they can be applied properly using Composer Install.

### Installation Instructions
```
composer require zero1limited/magento2-patches
composer require cweagans/composer-patches:^1.6.5
php bin/magento module:enable Zero1_Patches
```

### Installing/Applying a Patch
```
php bin/magento patch:list
php bin/magento patch:add --patch=PRODSECBUG-2198
composer install
```

### Patches Currently Supported
• PRODSECBUG-2198



## Current Patches available





















ZERO-1 manage the applicable patches for free but feel free to submit comments or Pull Requests!
