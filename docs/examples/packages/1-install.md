# Installing a Package

Packages can be found on the packages page in your framework manager. All packages come with a one click install option, package files can be manually placed in the package folder.
 
## Install using the package manager

In the package manager you will first see featured packages listed with a download button next to each. Clicking the download button will download the code to the packages folder but will not install the package.
The package will not appear in the installed packages list at the top of the page, here you will be able to install/uninstall the package with a single click.

## Manually install a package

If you have a package that is not available in the package manager or need to manually install the it for any reason you will need to copy the package into your packages folder.

`public_html/packages`

Once you have placed your package in the packages folder you can either install the package through the manager or install using the below code.

```php

		Twist::framework()->
```