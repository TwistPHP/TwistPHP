#Creating a Controller

TwistPHP uses controllers to help

## Create you controller file

Create a controller file in your app/Controllers directory called 'Test.controller.php'. Your controller will be need to be in the namespace 'App\Controllers' and must extent the twist base controller 'Base'.

```php
namespace App\Controllers;
use Twist\Core\Controllers\Base;

class Test extends Base{

	public function __construct(){

	}
}
```

### Adding a default response

You can now add a default/index response so that if the controller is called you will get back some data. There are two main functions '_fallback' and '_index', both of which will always return a 404 page unless you override their default actions by creating the functions in your controller.
Add the following '_index' function into your controller.

```php
public function _index(){
	return 'Hello World!';
}
```

### Using your controller with routes

You can now view the output of your controller, setup a route in your index file for the controller. Add the following line into your index.php file.

```php
Twist::Route()->controller('/%','Test');
```

Navigate to the home directory of your website in your browser, all being well you should now see "Hello World!"


### Adding a page

Now we can add an additional page to our controller, in this example we will add 'contact' as our page which will output a view. Add the contact function into your controller as shown in the below code.

```php
public function contact(){
	return $this->view('contact.tpl');
}
```

We will also need to create the view that will be output from the contact function. Create the view file in your app/Views directory called 'contact.tpl' and paste in the below HTML.

```html
<h1>Contact</h1>
<form action="/contact" method="post">
	<label>Name</label>
	<input type="text" name="name" value="">
	<label>Email</label>
    <input type="text" name="email" value="">
    <label>Message</label>
    <textarea name="message"></textarea>
    <button type="submit">Submit</button>
</form>
```

Navigate to the new contact page of your website in your browser '/contact', all being well you should now see contact form that has been output form the view file. Submitting the form will return you to the same page with post parameters.

### Capturing the POST data

To capture the POST data in the controller, create a new function called 'postContact' the function will only be called when you are on the '/contact' page with POST data.
Processing the POST data can be done using a model, which can be created in the app/Models directory.

```php
namespace App\Models;

class Contact{

	public function send($strName,$strEmailAddress,$strMessage){
		
		//We will sent the contact details in an email, replace <your-email-address> with your full email address.
		\Twist::Email()->addTo('<your-email-address>');
		\Twist::Email()->addSubject('Contact Form Submission');
		\Twist::Email()->setPlainBody(sprintf("Form submission details:\nName: %s\nEmail: %s\nMessage: %s",$strName,$strEmailAddress,$strMessage));
		\Twist::Email()->setFrom('no-reply@yoursite.com');
		\Twist::Email()->send();
	}
}
```

Add the below 'postContact' function into your controller.

```php
public function postContact(){

    $resContact = new \App\Models\Contact();
	$resContact->send($_POST['name'],$_POST['email'],$_POST['message']);
	
	return '<h1>Thank you for message!</h1>';
}
```

Navigate to the new contact page of your website in your browser '/contact' and submit the complete form, all being well you should see the output for the new post function rather than the form.