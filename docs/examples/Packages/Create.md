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
| /blocks         | optional      | Folder to contain system blocks                                           |
| /blocks.php     | optional      | Register blocks into the framework                                        |
| /classes        | optional      | Folder for miscellaneous classes that can be used to support your package |
| /controllers    | optional      | Folder to contain PHP controllers                                         |
| /extend.php     | optional      | Register extensions to other packages or Twist                            |
| /models         | optional      | Folder to contain PHP models                                              |
| /resources      | optional      | Folder to contain all the CSS, JS, Images and other resources             |
| /resource.json  | optional      | Manifest of all the resources (CSS,JS,Images) provided by this package    |
| /routes         | optional      | Folder to contain all the pre-configured routes                           |
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
        "email": "contact@twistphp.com",
		"git": "",
		"bugs": ""
    },
    "thirdparty": {
        "info": "Thanks to twitter for the API example code used in this package",
        "website": "https://twitter.com"
    }
}
```

Next create the file /packages/Twitter/install.php in your package folder, this file will be called upon installation of your package. In this example we are also creating a database table and adding two new settings.

```php
<?php

	Twist::framework()->package()->install();
	
	//Optional Line: Add this line if you are adding database tables
	Twist::framework()->package()->importSQL('install/twitter.sql');

	//Optional Line: Add this line if you are adding framework settings
	Twist::framework()->package()->importSettings('install/settings.json');
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

	namespace Twist\Packages\Twitter\Block;
	use Twist\Core\Classes\BaseBlock;

	class TwitterView extends BaseBlock{

		public function render(){
		
			$arrTags = array('tweets' => '');
		
			$resTwitter = new \Twist\Packages\Twitter\Model\Twitter();
			$resTwitter->getTweets(5);
		
			foreach($resTwitter->getTweets(5) as $arrEachTweet){
				$arrTags['tweets'] .= $this->view('block/tweet.tpl');
			}
		
			return $this->view('block/render.tpl',$arrTags);
		}

		public function create(){
        	return $this->view('block/create.tpl');
        }

		public function postCreate(){
			$arrOptions = $_POST['options']
			return $objBlock->create('Twitter',$_POST['slug'],$arrOptions);
        }

		public function remove(){
        	return $this->view('block/remove.tpl');
        }

		public function postRemove(){
			return $objBlock->remove('TwitterView',$_POST['slug']);
        }

		public function details(){
        	return array();
        }
	}
```

The controller must now be registered as a functional block, to do this create the file /packages/Twitter/blocks.php and copy in the register command.

```php
<?php

	Twist::framework()->register()->block('TwitterView');
```

Next create the views required to make the block work, we need to create a view for render, tweet, create and remove. Example files are below:

**Create /packages/Twitter/views/block/render.tpl**
```html
{resource:twitter}
<div class="twitterWindow">
	{data:tweets}
</div>
```

**Create /packages/Twitter/views/block/tweet.tpl**
```html
<div class="tweet">
	{data:message}
</div>
```

**Create /packages/Twitter/views/block/create.tpl**
```html
<form action="." method="post" class="inline">
	<label>Block Slug</label>
	<input type="text" name="block_slug" value="">
	
	<label>Display Tweets</label>
    <input type="text" name="display" value="5">
	
	<button type="submit">Create</button>
</form>
```

**Create /packages/Twitter/views/block/remove.tpl**
```html
<h3>Remove Twitter Block</h3>
<p>You are about to remove the twitter block, if you are still using the block on your site it will be replaced with a holding message.</p>
<button type="submit">Remove Block</button>
```

### Extend

Extending core and custom packages can be achieved by creating the file /packages/Twitter/extend.php and add the following line of code.

```php
<?php

	Twist::framework()->register()->extend('View','twitter',json_decode(file_get_contents('./info.json'),true));
```

### Resources

Adding additional resources into the frameworks {resource:} tag, these resources can be unique for the package or for general use through out the developers app.
The resources are added as JSON in a json file /packages/Twitter/resources.json and must follow the syntax laid out below.

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

Including this CSS file in any views throughout the package or your app use the new view tag show below.

```html
{resource:twitter}
```

### Routes

Creating a set of pre-defined routes, all of these routes will be called through a single wildcard URI. Create the file /packages/Twitter/routes/Admin.php in your package folder.

```php
<?php

	namespace Twist\Package\Twitter\Route;
	use Twist\Core\Classes\BaseRoute;
	
	class Admin extends BaseRoute{
	
		public function load(){
        
			$this->baseURI('/');

			$this->controller('/%','Twist\Packages\Twitter\Controllers\Admin');
			$this->restrict('/%','/login');
		}
	}
```

Create the admin controller that will allow the user to edit the settings for the twitter module. Create the file /packages/Twitter/controllers/Admin.controller.php in your package folder.

```php
<?php

	namespace Twist\Packages\Twitter\Controller;
	use Twist\Core\Classes\BaseController;

	class Admin extends BaseController{

		public function _default(){
			return $this->view('settings.tpl');
		}

		public function login(){
        	return $this->view('login.tpl');
        }

		public function postSave(){

			//Save the settings and redirect back to _default

            \Twist::redirect('./');
        }
	}
```