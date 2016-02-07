# Handling POST data

While processing POST data can be done directly in a controller, is suggested that you instead utilise models.

## Create a model

Create a file called `Contact.model.php` in your `app/Models` directory.

```php
<?php

    /*
     * ================================
     * The PSR namespace for your app's
     * models
     * ================================
     */
    namespace App\Models;
    
    /*
     * ================================
     * The class name should be exactly
     * the same as the filename
     * ================================
     */
    class Contact {

        /*
         * ================================
         * This is a basic model so we only
         * need this one method
         * ================================
         */
        public function send( $strName, $strEmailAddress, $strMessage ) {
        
            /*
             * ================================
             * Create a simple email object and
             * send it using TwistPHP
             * ================================
             */
            $objEmail = \Twist::Email();
            $objEmail -> addTo( 'test@test.com' );
            $objEmail -> addSubject( 'Contact Form Submission' );
            $objEmail -> setPlainBody(
                sprintf(
                    "Form submission details:\nName: %s\nEmail: %s\nMessage: %s"
                    $strName,
                    $strEmailAddress,
                    $strMessage
                )
            );
            $objEmail -> setFrom( 'no-reply@mysite.com' );
            $objEmail -> send();
        }
    }
```

## Use the model in your controller

To capture POST data, create the following methods in your controller:

```php
<?php

    /*
     * ================================
     * The plain contact method will be
     * called for all HTTP verbs unless
     * overwritten
     * ================================
     */
    public function contact() {
        
        /*
         * ================================
         * Output a HTML form when the user
         * visits the page /contact
         * ================================
         */
        return '<form action="/contact" method="post">
            Name: <input type="text" name="name"><br>
            Email: <input type="email" name="email"><br>
            Message: <textarea name="message"></textarea><br>
            <button type="submit">Send</button>
        </form>';
        
    }
    
    /*
     * ================================
     * By prefixing any of your methods
     * with either 'POST', 'GET', 'PUT'
     * or 'DELETE' in uppercase you can
     * selectively respond only to HTTP
     * requests for that verb
     * ================================
     */
    public function POSTcontact() {
    
        /*
         * ================================
         * Get a new instance of your model
         * ================================
         */
        $resContact = new \App\Models\Contact();
        
        /*
         * ================================
         * Pass the POST data into the send
         * method of your object
         * ================================
         */
        $resContact -> send( $_POST['name'], $_POST['email'], $_POST['message'] );
        
        /*
         * ================================
         * Return a simple 'thanks' message
         * followed by the normal HTML form
         * from the main contact() method
         * ================================
         */
        return 'Thank you for message!' . $this -> contact();
        
    }
```

Navigate to the new `/contact` page of your website in your browser then complete and submit the form. Upon posting, you should see the thanks message followed by the form. If you change the email address in the `addTo()` method of your model then you should receive an email with the contact form details.