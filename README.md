#TwistPHP

##A fresh, new PHP 5.3+ micro framework

We looked around for a PHP micro framework that suited our needs and found nothing that we were completely happy with. So we made one.

TwistPHP is built from ground up to support the way you already work and was based on the already fantastic Shadow framework.

###Code your way, just faster

We didn't like being tied into a particular software pattern so rather than make yet another MVC patterned framework, Twist was designed to be as open and expandable as we could make it.

Every function has been painstakingly developed to create the most intuitive structure possible with sensible function names and a well thought-out structure.

Making new modules is as easy as using existing ones and we've given you a good start with the ability to download a bunch of powerful pre-built modules.

###Templating made easier

Allowing templating should be at the core of every framework. Twist features a unique templating system which was inspired by CodeIgnitor.

```html
{template:includes/header.tpl}
<h1>Hello {session:user/name}</h1>
{element:navigation/menu.php}
<p>Right now, it is {date:F jS, Y}</p>
<p>Your IP address is {server:remote_addr}</p>
<h2>Log in to your account</h2>
{user:login_form}
```

###Helping you with databases

The database class was our first so we wanted to make it special. You have the ability to use full-fat SQL queries if you like or you can stick with our quick and easy OO approach.

```php
$newFruit = Twist::Database() -> createRecord( 'fruit' );
$newFruit -> set( 'name', 'Apple' );
$newFruit -> set( 'colour', 'green' );
$newFruit -> commit();

$fruit = Twist::Database() -> getAll( 'fruit' );
```

###Simplified routing

Routing is something we saw in a lot of other frameworks and wanted it to be at the heart of ours. Once you outgrow it, we have a structure module waiting in the wings to jump in and create you a fantastic semantically-structured site complete with a migration facility to get you there.

```php
Twist::Route() -> baseTemplate( '_base.tpl' );

Twist::Route() -> template( '/', 'pages/home.tpl' );
Twist::Route() -> element( '/contact/%', 'contact-form.php' );
Twist::Route() -> redirect( '/about/%', 'http://facebook.com/Me' );
Twist::Route() -> restrict( '/account/%', '/login' );
Twist::Route() -> template( '/login', 'pages/login.tpl' );

Twist::Route() -> serve();
```

###No more command line

Twist comes with its own web-based manager that allows you to update your framework, install new modules and change your sites settings.

We also built in a setup wizard with a GUI to help you get started even faster. All of this makes Twist ideal for use on shared hosting where you may not have access to the command line.
One line is all it takes...

```php
<php
    require_once 'twist/framework.php';
```

####Please Note:
TwistPHP is currently a private project
