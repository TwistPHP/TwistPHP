# TwistPHP AJAX

AJAX requests can be made really easily - we have even included a JavaScript class to help you.

## An AJAX controller

In TwistPHP, an AJAX controller works in much the same way as a normal controller but with a few more methods specifically for responding to AJAX requests.

```php
<?php

    /*
     * --------------------------------
     * So far we've got the same as one
     * of our normal controllers...
     * --------------------------------
     */
    namespace App\Controllers;
    
    /*
     * --------------------------------
     * ...but this time we will want to
     * use the BaseAJAX controller
     * --------------------------------
     */
    use Twist\Core\Controllers\BaseAJAX;
    
    /*
     * --------------------------------
     * Our class name is again the same
     * as the filename but this time it
     * extends BaseAJAX
     * --------------------------------
     */
    class AJAX123 extends BaseAJAX {
    
        /*
         * --------------------------------
         * Just as with normal controllers,
         * URIs can be changed by using the
         * helpfule inherited _replaceURI()
         * and _aliasURI() methods
         * --------------------------------
         */
        public function __construct() {
            $this -> _aliasURI( 'knock-knock', 'knockknock' );
        }
        
        /*
         * --------------------------------
         * Respond to a URI with the status
         * and data that you need which can
         * also be prefixed with one of the
         * HTTP verbs, this method could be
         * called either GETknockknock() or
         * POSTknockknock() which will then
         * only respond to those types
         * --------------------------------
         */
        public function knockknock() {
            /*
             * --------------------------------
             * By default, the AJAX response is
             * "successful" but we can return a
             * "failed" response along with the
             * data
             * --------------------------------
             */
			//$this -> _ajaxFail();
			
            /*
             * --------------------------------
             * The message field can be used to
             * describe the returned data or as
             * a "title" field in the JS
             * --------------------------------
             */
	        $this -> _ajaxMessage( 'Who\'s there?' );
	        
            /*
             * --------------------------------
             * This is constructing the data to
             * be returned to the user
             * --------------------------------
             */
	        $objResponse = array(
	            'whosthere' => 'Doctor',
	            'humor' => 0.19,
	            'originality' => 0.04
	        );
	       
            /*
             * --------------------------------
             * Respond to the request with some
             * data
             * --------------------------------
             */
			return $this -> _ajaxRespond( $objResponse );
        }
        
        /*
         * --------------------------------
         * This will be used later...
         * --------------------------------
         */
        public function POSTcontact() {
            $email = new \Twist::Email() -> create();
            $email -> setTo( 'me@myemailaddress.com' );
            $email -> setFrom( $_POST['email'] );
            $email -> setSubject( 'Message from ' . $_POST['name'] );
            $email -> setBodyHTML( '<p>' . $_POST['message'] . '</p>' );
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
     * --------------------------------
     * Here we will register the URI of
     * /ajax to forward all requests on
     * to our new AJAX123 controller
     * --------------------------------
     */
    Twist::Route() -> ajax( '/my-first-ajax/%', 'AJAX123' );
    
    Twist::Route() -> serve();
```

## JavaScript

First, you can include the AJAX JavaScript class with a simple view tag:

```html
{resource:twist/ajax}
```

```js
/*
 * --------------------------------
 * ...then create a new instance of
 * the AJAX object that is directed
 * at your registered controller
 * --------------------------------
 */
var myAJAX = new twistajax( '/my-first-ajax' );
```

The parameters of the object (with the exception of the first) are all optional and any can be omitted, so long as the remainder are passed in the correct order.

| Param | Name                  | Description                                                          | Type     | Default           |
| ----- | --------------------- | -------------------------------------------------------------------- | -------- | ----------------- |
| 1     | BaseURI               | The URI to post AJAX requests to                                     | string   | `null` (required) |
| 2     | MasterCallbackSuccess | Function to call after every request that returns a succeeded status | function | `function() {}`   |
| 3     | MasterCallbackFailure | Function to call after every request that returns a failed status    | function | `function() {}`   |
| 4     | DefaultData           | An object of data to post along with every request                   | object   | `{}`              |
| 5     | MasterTimeout         | The default timeout (in milliseconds) of all requests                | integer  | `10000`           |
| 6     | LoaderSize            | The class to add to the loader element                               | string   | `'medium'`        |

### Make requests

Once you have an instance, you can start making AJAX calls using the returned object. Any of the parameters for the `get()`, `post()`, `put()`, `patch()` and `delete()` methods of the AJAX instance can be omitted, just as the main object method, as long as they are passed in the correct order.

| Param | Name    | Description                       | Type     | Default                                        |
| ----- | ------- | --------------------------------- | -------- | ---------------------------------------------- |
| 1     | Method  | The last part of the URI          | string   | `null` (required)                              |
| 2     | Data    | Data to post to the controller    | object   | `{}` plus default data set on init of `myAJAX` |
| 3     | Timeout | The timeout only for this request | integer  | Default timeout set on init of `myAJAX`        |
| 4     | Success | Function to call on success       | function | `function() {}`                                |
| 5     | Failure | Function to call on failure       | function | `function() {}`                                |

```js
/*
 * --------------------------------
 * Do a GET request and console log
 * out some of the returned data
 * --------------------------------
 */
myAJAX.get(
    'knock-knock',
    function() {
        console.log( this.data.whosthere );
    },
    function() {
        console.error( 'Sorry, I didn\'t hear you knock' );
    }
);
```

### Contact example

Now we can use that contact method we wrote into our controller to handle data.

```js
/*
 * --------------------------------
 * POST some data to the registered
 * contact method and call feedback
 * methods on success and failure
 * --------------------------------
 */
myAJAX.post(
    'contact',
    {
        name: 'Riddick',
        email: 'riddick@furya.',
        message: 'Richard B. Riddick. Escaped convict. Murderer.'
    },
    function() {
        alert( 'Thank you' );
    },
    function() {
        alert( 'Your message failed to send' );
    }
);
```

Optionally, if the `<input>` and/or `<textarea>` elements are given the correct names and are used within a `<form>`, you can simply pass in either the form as a jQuery object or just a jQuery selector.

```js
/*
 * --------------------------------
 * POST the entire form to /contact
 * using a jQuery object
 * --------------------------------
 */
myAJAX.post(
    'contact',
    $( 'form' )
);

/*
 * --------------------------------
 * ...or an element selected by the
 * selector $( '#myForm' )
 * --------------------------------
 */
myAJAX.post(
    'contact',
    '#myForm'
);
```

#### Options

You can use the following verbs to split up and call various methods in your AJAX controller: `get()`, `post()`, `put()`, `patch()` and `delete()`.

To disable the HTML loader element Twist adds, you can call `myAJAX.disableLoader()`.

By default, AJAX requests are not cached. To enable the browser to cache them (by requesting non-unique URIs), simple call `myAJAX.enableCache()`. Re-disable with `myAJAX.disableCache()`;