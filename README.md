# [TwistPHP](https://twistphp.com/) ![TwistPHP logo](http://static.twistphp.com/logo/square/32.png)

[![GitHub release](https://img.shields.io/github/release/TwistPHP/TwistPHP.svg)](https://github.com/TwistPHP/TwistPHP/releases?label=latest) [![GitHub license](https://img.shields.io/github/license/TwistPHP/TwistPHP.svg)](http://www.gnu.org/licenses/gpl-3.0.en.html) [![Development build status](https://img.shields.io/travis/TwistPHP/TwistPHP/development.svg?label=development)](https://travis-ci.org/TwistPHP/TwistPHP) [![Coveralls](https://img.shields.io/coveralls/TwistPHP/TwistPHP.svg)](https://coveralls.io/github/TwistPHP/TwistPHP) [![Join the chat at https://gitter.im/TwistPHP/TwistPHP](https://img.shields.io/gitter/room/TwistPHP/TwistPHP.svg)](https://gitter.im/TwistPHP/TwistPHP)

## A fresh, new open source PHP MVC micro framework

* Full MVC architecture
* Open source
* Completely bespoke
* Quick and memory-efficient
* Easily expandable without editing the core
* Loads of pre-built tools included

One line is all it takes:

```php
require_once( 'twist/framework.php' );
```

...or with Composer

```php
use Twist\framework;
```

## Vagrant

To get started with a Vagrant machine:

* Install [Vagrant](https://www.vagrantup.com/downloads.html)
* Install [VirtualBox](https://www.virtualbox.org/)
* Run `vagrant up`
* Load up [localhost:8080](http://localhost:8080/)
* You can SSH into the Vagrant server with `vagrant ssh`

## Documentation

Full documentation of the framework can be found on the [TwistPHP website](https://twistphp.com/docs).

Code examples can also be found [in the examples area](https://twistphp.com/examples).

## Issues

Please use the [GitHub's issue tracker](https://github.com/TwistPHP/TwistPHP/issues) to report any problems.

### Modifications

The CSS and JS resources for TwistPHP are compiled using Gulp. After cloning the repository, you can install the necessary packages with `npm install` and then compile using `gulp`. The source files for all the resources can be found in the `/src` directory.

### Branches

For branching, please use the 'development' branch. All updates apart from hotfixes will get pushed from the [development branch](https://github.com/TwistPHP/TwistPHP/tree/development) to the master.

## Licence

As of v3.0.4, TwistPHP is licenced with [GNU GPLv3](http://www.gnu.org/licenses/gpl-3.0.en.html).

Up to and including v3.0.3, the [GNU LGPLv3](http://www.gnu.org/licenses/lgpl-3.0.en.html) was used.
