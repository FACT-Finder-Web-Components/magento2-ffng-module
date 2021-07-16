# DEPRECATED
[Version 2](https://github.com/FACT-Finder-Web-Components/magento2-module/tree/release/2.x) of Magento2 module is comatible with FACT-Finder® NG and this sub-module is no longer required.

Since the support of [version 1](https://github.com/FACT-Finder-Web-Components/magento2-module/tree/release/1.x) will be ceased soon, this NG sub-module is also deprecated. If you still use version 1, please consider updating to the version 2.

# FACT-Finder® NG Web Components for Magento 2

This module ensures the compatibility of our Magento 2 module with FACT-Finder® NG.

## Requirements

This module supports:

- Magento 2 v2.2 and higher
- PHP version 7.1 and higher. **Warning**: PHP 7.0 is not supported

## Installation

Before installation, open your terminal and register this repository:

    composer config repositories.factfinder-ng vcs https://github.com/FACT-Finder-Web-Components/magento2-ffng-module.git

Now you can install the module by running:

    composer require omikron/magento2-factfinder-ng

## Module activation

From the root of your Magento 2 project, run these commands in sequence:

    bin/magento module:enable Omikron_FactfinderNG
    bin/magento setup:upgrade

## License
FACT-Finder® Web Components License. For more information see the [LICENSE](LICENSE) file.
