#Creating a Package

TwistPHP comes with some integrated packages which are part of the frameworks core, if you want to add some functionality to the framework to use again or even resell here is how the guide.
Packages can extend the framework in one or many ways, the options are framework functionality, additional controllers, pre-configured set of routes or resources.

## Create you package folder

In /packages create a folder with your desired package name, in this example we will be calling our package Twitter. The contents of the package folder must contain some required files and folders, below is a manifest of what is optional and required.

| Folders         | Required      | Description                                                               |
| --------------- |:-------------:| ------------------------------------------------------------------------- |
| /info.json      | **required**  | Contains key information about the package                                |
| /install.php    | **required**  | Script called upon installation of the package                            |
| /uninstall.php  | **required**  | Script called upon un-installation of the package                         |
| /blocks.php     | optional      | Register blocks into the framework                                        |
| /extend.php     | optional      | Register extensions to other packages or Twist                            |
| /resource.json  | optional      | Manifest of all the resources (CSS,JS,Images) provided by this package    |
| /route.php      | optional      | Pre-configured routes for the creation of an interface                    |
| /controllers    | optional      | Folder to contain PHP controllers                                         |
| /models         | optional      | Folder to contain PHP models                                              |
| /resources      | optional      | Folder to contain all the CSS, JS, Images and other resources             |
| /classes        | optional      | Folder for miscellaneous classes that can be used to support your package |
| /thirdparty     | optional      | Folder for 3rd party code such as pre-written classes or scripts          |
| /views          | optional      | Folder for views that can be output by controllers                        |

## Creating the required files

There are some required files that all packages need in order to work within TwistPHP (marked as **required** in above table).
First create the file /packages/Twitter/info.json in your package folder and fill in the JSON fields as required, all fields are required even if left blank.

```json
{
    "name": "Twitter",
    "description": "Connection to twitter using the Twitter API",
    "version": "1.0.0",
    "author": {
        "name": "Joe Blogs",
        "website": "https://twistphp.com",
        "email": "contact@twistphp.com"
    },
    "thirdparty": {
        "info": "Thanks to twitter for the API example code used in this package",
        "website": "https://twitter.com"
    }
}
```

Next create the file /packages/Twitter/install.php in your package folder, this file will be called upon installation of your package.

```php
<?php

	Twist::framework()->package()->install();
```

Further code can be placed in this file, this code will be run upon installation of the package. For example you might want to create some database tables or folders.

Next create the file /packages/Twitter/uninstall.php in your package folder, this file will be called upon removal of your package.

```php
<?php

	Twist::framework()->package()->uninstall();
```

Additional code can be place in this file, this code will be run upon uninstalling the package. For example you might want to remove some database tables or back up the data contained in them.

## Creating the optional package files

Adding functionality into the package is simple, you can add any combination of the below options. Each of the options you choose will then become available in your application once the package has been installed.
For more detail on any of the below options view the more detail documentation for the required option.

### Blocks

Creating a block that can be placed in any view or rendered from a framework command. A block should contain the end-user view as well as views for the configuration and setup for the block.
First create the block controller, the controller must contain some required functions.

```php
<?php

	namespace Twist\Block;
	use Twist\Core\Classes\BaseBlock;

	class Twitter extends BaseBlock{

		public function render(){

			return '';
		}

		public function create(){

        	return '<form></form>';
        }

		public function postCreate(){

            return '';
        }

		public function remove(){

        	return '';
        }

		public function postRemove(){

        	return '';
        }

		public function details(){

        	return array();
        }
	}
```

The controller must now be registered as a functional block, to do this create the file /packages/Twitter/blocks.php and copy in the register command.

```php
<?php

	Twist::framework()->register()->block('Twitter');
```

### Extend

Extending core and custom packages can be achieved by creating the file /packages/Twitter/extend.php

```php
<?php

	Twist::framework()->register()->extend('View','Twitter');
```

### Resources

Adding additional resources into the frameworks {resource:} tag, these resources can be unique for the package or for general use through out the developers app.
The resources are added as JSON in a json file and must follow the syntax laid out below.

```json
{
	"twitter": {
		"1.0.0": {
			"default": true,
			"css": ["css/base.css"],
			"js": []
		}
	}
}
```

Now we need to create the Twitter CSS file /packages/Twitter/resources/twitter/css/base.css and place the styles for this package in the file.

```css

.twitterWindow{
	width: 260px;
	height: 260px;
	overflow:auto;
	padding:5px;
	border: 1px solid #666;
}

.twitterWindow .tweet{
	padding-bottom:5px;
	margin-bottom:5px;
	border-bottom:1px solid #CCC;
}

```

### Routes

Creating a set of pre-defined routes, all of these routes will be called through a single wildcard URI. Create the file /packages/Twitter/route.php in your package folder.

```php
<?php

	$resRoute = Twist::Route();

	$resRoute->controller('/%','Twitter');
	$resRoute->restrict('/%');
```

Create relevant controllers and views. -- todo finish writing