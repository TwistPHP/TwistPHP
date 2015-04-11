#Creating a Package

TwistPHP comes with some integrated packages which are part of the frameworks core, if you want to add some functionality to the framework to use again or even resell here is how the guide.
Packages can extend the framework in one or many ways, the options are framework functionality, additional controllers, pre-configured set of routes or resources.

## Create you package folder

In /packages create a folder with your desired package name, in this example we will be calling our package Twitter. The contents of the package folder must contain some required files and folders, below is a manifest of what is optional and required.

| Folders         | Required      | Description                                                               |
| --------------- |:-------------:| ------------------------------------------------------------------------- |
| /info.json      | required      | Contains key information about the package                                |
| /install.php    | required      | Script called upon installation of the package                            |
| /uninstall.php  | required      | Script called upon un-installation of the package                         |
| /blocks.php     | optional      | Register blocks into the framework                                        |
| /extend.php     | optional      | Register extensions to other packages or Twist                            |
| /resource.json  | optional      | Manifest of all the resources (CSS,JS,Images) provided by this package    |
| /route.php      | optional      | Pre-configured routes for the creation of an interface                    |
| /controllers    | optional      | Folder to contain PHP controllers                                         |
| /models         | optional      | Folder to contain PHP models                                              |
| /resources      | optional      | Folder to contain all the CSS, JS, Images and other resources             |
| /classes        | optional      | Folder for miscellaneous classes that can be used to support your package |
| /thirdparty     | optional      | Folder for 3rd party code such as pre-written classes or scripts          |

## Creating the require files

There are some required files that all packages need in order to work within TwistPHP (marked as *required* in above table).
First create the file /packages/Twitter/info.json in your package folder and fill in the JSON fields as required, all fields are required even if left blank.

```json
{
	"name":"Twitter",
	"description":"Connection to twitter using the Twitter API",
	"version":"1.0.0",
	"author":{
		"name":"Joe Blogs",
		"website":"https://twistphp.com",
		"email":"contact@twistphp.com"
	},
	"thirdparty":{
		"info":"Thanks to twitter for the API example code used in this package"
		"website":"https://twitter.com"
	}
}
```

Next create the file /packages/Twitter/install.php in your package folder, this file will be called upon installation of your package.