#[TwistPHP](http://twistphp.com/)

##A fresh, new PHP 5.3+ micro framework

We looked around for a PHP micro framework that suited our needs and found nothing that we were completely happy with. So we made one.

Twist is built from ground up to support the way you already work. With an naming convention that's easy to grasp, the learning curve is kept to a minimum.

###Code your way, just faster

We didn't like being tied into a particular software pattern so rather than make yet another MVC patterned framework, Twist was designed to be as open and expandable as we could make it.

Every function has been painstakingly developed to create the most intuitive structure possible with sensible function names and a well thought-out structure.

Making new modules is as easy as using existing ones and we've given you the best start with the ability to download a bunch of powerful pre-built modules.

###Templating made easier

Templating should be at the core of every framework. Twist features a unique templating system inspired by CodeIgnitor which provides you with a whole heap of functionality to keep your PHP and HTML separate.

```tpl
{template:includes/header.tpl}
{element:navigation/menu.php}
<h1>Hello {session:user/name}</h1>
<p>Good {date:G<12?'morning':'afternoon'} - the date is {date:F jS, Y}</p>
<p>Your IP is {server:remote_addr}</p>
<p>Simple PHP commands
	<input value="{escape[post:email]}" data-checksum="{md5[post:email]}"></p>
{user:login_form}
```

###Helping you with databases

The database class was our first so we wanted to make it special. You have the ability to use full-fat SQL queries if you like or you can stick with our quick and easy object-oriented approach.

```php
$newFruit = Twist::Database() -> createRecord( 'fruit' );
$newFruit -> set( 'name', 'apple' );
$newFruit -> set( 'colour', 'green' );
$fruitID = $newFruit -> commit();

$apple = Twist::Database() -> getRecord( 'fruit', $fruitID );
$apple -> set( 'colour', 'red' );
$apple -> commit();

$allFruit = Twist::Database() -> getAll( 'fruit' );
$greenFruit = Twist::Database() -> find( 'fruit', 'green', 'colour' );
```

###Simplified routing

Routing is something we saw in a lot of other frameworks and wanted it to be at the heart of ours. Once you outgrow it, we have a structure module waiting in the wings to jump in and create you a fantastic semantically-structured site complete with a migration facility to get you there.

```php
Twist::Route() -> baseTemplate( '_base.tpl' );

Twist::Route() -> template( '/', 'pages/home.tpl' );

Twist::Route() -> element( '/languages', 'languages.php' );
Twist::Route() -> getTemplate( '/contact', 'contact-form.tpl' );
Twist::Route() -> postElement( '/contact', 'send-contact.php' );

Twist::Route() -> redirect( '/about', 'http://facebook.com/Me' );

Twist::Route() -> controller( '/portfolio/%', 'Portfolio' );

Twist::Route() -> restrict( '/account/%', '/login' );
Twist::Route() -> getTemplate( '/login', 'pages/login.tpl' );

Twist::Route() -> serve();
```

###Restful controllers

You can process GET, POST, DELETE and PUT requests in your controllers using a simple and easy syntax. When extended, the BaseController class allows you some additional functionality to catch unexpected requests.

```php
namespace TwistController;

class Users extends BaseController {
    public function _default(){
        \TwistPHP\Error::errorPage( 404 );
        return false;
    }

	public function member() {
		throw new \Exception( 'No member selected' );
	}

	public function getMember() {
		$user = \Twist::User -> get( $_SERVER['TWIST_ROUTE_PARTS'][0] );
		return \Twist::Template -> build( 'user_details.tpl', $user );
	}

	public function postMember() {
		$user = \Twist::User -> create();
		$user -> firstname( $_POST['first_name'] );
		$user -> surname( $_POST['surname'] );
		$user -> email( $_POST['email'] );
		$user -> commit();
		$user -> sendWelcomeEmail();
	}

	public function deleteMember() {
		$user = \Twist::User -> get( $_SERVER['TWIST_ROUTE_PARTS'][0] );
		$user -> delete();
		header( 'Location: /users' );
	}

	public function putMember() {
		/*
		 * ...does anyone still use PUT?
		 */
	}
}
```

###No more command line

Twist has the option of a web-based manager that allows you to update your framework, install new modules and change your sites settings without having to fumble around in the command line or mess around with an FTP client.

We also built in a setup wizard with a GUI to help you get started even faster. All of this makes Twist ideal for use on shared hosting where you may not have root access to your server.

One line is all it takes:

```php
require_once 'twist/framework.php';
```

##Documentation

Full documentation of the framework can be found on the [TwistPHP site](http://twistphp.com/docs).

##Issues

Please use the [GitHub's issue tracker](https://github.com/Shadow-Technologies/TwistPHP/issues) to report any problems.