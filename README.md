# magento2-patches

This module makes it easier for a Magento 2 merchant to apply patches in a manner which adheres to standard Composer workflow and most deployment pipleline methodologies.

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

## Current Patches available
In order to apply the patch please run the above commands passing the patch name (in bold below) after the --patch= as shown above.

• __PRODSECBUG-2198__ https://magento.com/security/patches/magento-2.3.1-2.2.8-and-2.1.17-security-update


If you need to apply a Magento patch which is not currently listed please reach out at support@mdoq.io and we will promtly incorporate the patches into this module.





















ZERO-1 manage the applicable patches for free but feel free to submit comments or Pull Requests!
