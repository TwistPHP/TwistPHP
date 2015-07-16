#Creating a Controller

TwistPHP uses controllers to help

## Create you controller file

Create a controller file in your app/controllers directory called 'Test.controller.php'. Your controller will be need to be in the namespace 'Twist/Controllers' and must extent the twist base controller 'BaseController'.

```php
namespace Twist\Controllers;
use Twist\Core\Classes\BaseController;

class Test extends BaseController{

	public function __construct(){

	}
}
```

### Adding a default response

You can now add a default response so that if the controller is called you will get back some data. There are two main functions '_fallback' and '_default', both of which will always return a 404 page unless you override their default actions by creating the functions in your controller.
Add the following '_default' function into your controller.

```php
public function _default(){
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

We will also need to create the view that will be output from the contact function. Create the view file in your app/views directory called 'contact.tpl' and paste in the below HTML.

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
Processing the POST data can be done using a model, which can be created in the app/model directory.

```php
namespace Twist\Models;
use Twist\Core\Classes\BaseModel;

class Contact extends BaseModel{

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

	$resContact = $this->_model('Contact');
	$resContact->send($_POST['name'],$_POST['email'],$_POST['message']);
	
	return '<h1>Thank you for message!</h1>';
}
```

Navigate to the new contact page of your website in your browser '/contact' and submit the complete form, all being well you should see the output for the new post function rather than the form.