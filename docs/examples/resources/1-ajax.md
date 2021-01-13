# TwistPHP AJAX

AJAX requests can be made really easily - we have even included a JavaScript class to help you.

## An AJAX controller

In TwistPHP, an AJAX controller works in much the same way as a normal controller but with a few more methods specifically for responding to AJAX requests.

```php
<?php

    /*
     * ================================
     * So far we've got the same as one
     * of our normal controllers...
     * ================================
     */
    namespace App\Controllers;
    
    /*
     * ================================
     * ...but this time we will want to
     * use the BaseAJAX controller
     * ================================
     */
    use Twist\Core\Controllers\BaseAJAX;
    
    /*
     * ================================
     * Our class name is again the same
     * as the filename but this time it
     * extends BaseAJAX
     * ================================
     */
    class AJAX123 extends BaseAJAX {
    
        /*
         * ================================
         * Just as with normal controllers,
         * URIs can be changed by using the
         * helpfule inherited _replaceURI()
         * and _aliasURI() methods
         * ================================
         */
        public function __construct() {
            $this -> _aliasURI( 'knock-knock', 'knockknock' );
        }
        
        /*
         * ================================
         * Respond to a URI with the status
         * and data that you need which can
         * also be prefixed with one of the
         * HTTP verbs, this method could be
         * called either GETknockknock() or
         * POSTknockknock() which will then
         * only respond to those types
         * ================================
         */
        public function knockknock() {
            /*
             * ================================
             * By default, the AJAX response is
             * "successful" but we can return a
             * "failed" response along with the
             * data
             * ================================
             */
			//$this -> _ajaxFail();
			
            /*
             * ================================
             * The message field can be used to
             * describe the returned data or as
             * a "title" field in the JS
             * ================================
             */
	        $this -> _ajaxMessage( 'Who\'s there?' );
	        
            /*
             * ================================
             * This is constructing the data to
             * be returned to the user
             * ================================
             */
	        $objResponse = array(
	            'whosthere' => 'Doctor',
	            'humor' => 0.19,
	            'originality' => 0.04
	        );
	       
            /*
             * ================================
             * Respond to the request with some
             * data
             * ================================
             */
			return $this -> _ajaxRespond( $objResponse );
        }
        
        /*
         * ================================
         * This will be used later...
         * ================================
         */
        public function POSTcontact() {
            $email = new \Twist::Email() -> create();
            $email -> setTo( 'me@myemailaddress.com' );
            $email -> setFrom( $this -> _posted( 'email' ) );
            $email -> setSubject( 'Message from ' . $this -> _posted( 'name' ) );
            $email -> setBodyHTML( '<p>' . $this -> _posted( 'message' ) . '</p>' );
            $email -> send();
        }
        
    }
```

## Register the route

Register the route to the controller in the normal manner, but using the `ajax()` method instead. This method differs from the usual `controller()` method in that loading up URIs normally in a browser will result in a "403 - Unsupported HTTP protocol used to request this URI" response. These URIs can only be requested with certain headers provided by the accompanying JavaScript class.

```php
<?php

    require_once( 'twist/framework.php' );

    /*
     * ================================
     * Here we will register the URI of
     * /ajax to forward all requests on
     * to our new AJAX123 controller
     * ================================
     */
    Twist::Route() -> ajax( '/my-first-ajax/%', 'AJAX123' );
    
    Twist::Route() -> serve();
```

## JavaScript

First, you can include the AJAX JavaScript class with a simple view tag:

```html
{resource:twist/ajax}
```

For older browsers, you may also have to include an ES6 polyfill:

```html
{resource:babel,polyfill}
```

```js
/*
 * ================================
 * ...then create a new instance of
 * the AJAX object that is directed
 * at your registered controller
 * ================================
 */
var myAJAX = new twistajax( '/my-first-ajax' );
```

### Properties

Any of the properties can be set like so:

```js
myAJAX.debug = true;
```

| Property | Description                      | Type     | Default |
| -------- | -------------------------------- | -------- | ------- |
| uri      | The URI to make AJAX requests to | string   | `''`    |
| cache    | Cache all requests               | boolean  | `false` |
| requests | The number of active requests    | integer  | `0`     |
| debug    | The status of debugging          | boolean  | `false` |
| events   | Registered events                | object   | `{}`    |

### Make requests

Once you have an instance, you can start making AJAX calls using the returned object. There are methods for all major HTTP verbs (`get()`, `post()`, `put()`, `patch()` and `delete()`), all of which return promises.

```js
/*
 * ================================
 * Do a GET request and console log
 * out some of the returned data
 * ================================
 */
myAJAX.get( 'knock-knock' )
    .then( response => {
        console.log( response.whosthere );
    } )
    .catch( e => {
        console.error( 'Sorry, I didn\'t hear you knock because: ' + e );
    } );
```

### Contact example

Now we can use that contact method we wrote into our controller to handle data.

```js
/*
 * ================================
 * POST some data to the registered
 * contact method and use a promise
 * to handle the returned data
 * ================================
 */
myAJAX.post( 'contact', {
        name: 'Riddick',
        email: 'riddick@furya.',
        message: 'Richard B. Riddick. Escaped convict. Murderer.'
    } )
    .then( response => {
        alert( 'Thank you', response );
    } )
    .catch( e => {
        alert( 'Your message failed to send' );
    } );
```

You can post an entire HTML form by using the `postForm` method. Pass in an element selector for the form as the second parameter.

```js
/*
 * ================================
 * Post the HTML form element which
 * has an ID of contact-form to the
 * /contact method and then use the
 * response in some promises
 * ================================
 */
myAJAX.postForm( 'contact', '#contact-form' )
    .then( response => {
        console.log( response );
    } )
    .catch( e => {
        console.error( 'Something broke', e );
    } );
```