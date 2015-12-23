#[TwistPHP](https://twistphp.com/) ![TwistPHP logo](http://static.twistphp.com/logo/square/32.png)

[![Build Status](https://travis-ci.org/TwistPHP/TwistPHP.svg?branch=travis-ci)](https://travis-ci.org/TwistPHP/TwistPHP) [![Join the chat at https://gitter.im/TwistPHP/TwistPHP](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/TwistPHP/TwistPHP?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

##A fresh, new open source PHP MVC micro framework

We looked around for a PHP framework that suited our needs and found nothing that we were completely happy with. So we made one.

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

Twist::Route() -> view( '/', 'pages/home.tpl' );

Twist::Route() -> view( '/languages', 'languages.php' );
Twist::Route() -> getView( '/contact', 'contact-form.tpl' );
Twist::Route() -> postView( '/contact', 'send-contact.php' );

Twist::Route() -> redirect( '/about', 'http://facebook.com/Me' );

Twist::Route() -> controller( '/portfolio/%', 'Portfolio' );

Twist::Route() -> restrict( '/account/%', '/login' );
Twist::Route() -> getView( '/login', 'pages/login.tpl' );

Twist::Route() -> serve();
```

###Restful controllers

You can process GET, POST, DELETE and PUT requests in your controllers using a simple and easy syntax. When extended, the BaseController class allows you some additional functionality to catch unexpected requests.

```php
namespace App\Controllers;
use Twist\Core\Classes\BaseController;
use Twist\Core\Classes\Error;

class Users extends BaseController {

	public function _index(){
		Error::errorPage( 404 );
		return false;
	}

	public function member() {
		throw new \Exception( 'No member selected' );
	}

	public function getMember() {
		$arrData = $this->_route();
		$arrUser = \Twist::User -> get( $arrData['parts'][0] );
		return $this->_view( 'user_details.tpl', $arrUser );
	}

	public function postMember() {
		$resUser = \Twist::User -> create();
		$resUser -> firstname( $_POST['first_name'] );
		$resUser -> surname( $_POST['surname'] );
		$resUser -> email( $_POST['email'] );
		$resUser -> commit();
		$resUser -> sendWelcomeEmail();
	}

	public function deleteMember() {
		$arrData = $this->_route();
		$resUser = \Twist::User -> get( $arrData['parts'][0] );
		$resUser -> delete();
		Twist::redirect('/users');
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

This framework includes [basic documentation](docs/README.md), including examples.

Full documentation of the framework can be found on the [TwistPHP site](https://twistphp.com/docs).

##Issues

Please use the [GitHub's issue tracker](https://github.com/TwistPHP/TwistPHP/issues) to report any problems.

###Branches

For branching, please use the 'development' branch. All updates get pushed from the [development branch](https://github.com/Shadow-Technologies/TwistPHP/tree/development) to the master.
