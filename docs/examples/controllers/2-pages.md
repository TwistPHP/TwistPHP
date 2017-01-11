# Pages

## Respond with a view

Create a template file called `viewtest.tpl` in your `app/Views` directory:

```html
<!DOCTYPE html>
<html>
    <head>
        <title>{data:title}</title>
    </head>
    <body>
        <p>A unique ID {data:uid}</p>
        <p>The date is {date:jS F Y}</p>
        <p>Your IP: {server:REMOTE_ADDR}</p>
        <p>Your user agent string: <input value="{escape[server:HTTP_USER_AGENT]}"></p>
    </body>
</html>
```

To output the view to the user, add the following method to your controller:

```php
<?php

    public function viewtest() {
    
        /*
         * ================================
         * Set up an array of the data that
         * you want to pass into your view
         * ================================
         */
        $arrData = array(
            'title' => 'My test view',
            'uid' => uniqid()
        );
        
        /*
         * ================================
         * Pass the array into the standard
         * view method and return it to the
         * user
         * ================================
         */
        return $this -> _view( 'viewtest.tpl', $arrData );
        
    }
```

After registering the controller, visiting the page `/viewtest` in your browser should now display the above template, populated with your data.