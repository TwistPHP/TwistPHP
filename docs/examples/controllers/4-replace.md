# Method replacement

If you would like to use hyphens in your URIs, simply adding a method to controller won't work, as PHP doesn't support functions with non-alphanumeric characters.

## Replace

In order to allow URIs with other characters, we have to replace the method with a registered string using the `_replaceURI()` method. Replacing a method will mean that you won't be able to return requests from the original method name, only from its replacement.

```php
<?php

    namespace App\Controllers;
    
    use Twist\Core\Controllers\Base;
    
    class Replace extends Base {
    
        public function __construct() {
            /*
             * ================================
             * In the construct method when the
             * class is initialised, we can set
             * a replacement URI for any method
             * which would ignore requests that
             * go directly to that method
             * ================================
             */
            $this -> _replaceURI( 'search-your-feelings-you-know-it-to-be-true', 'searchyourfeelingsyouknowittobetrue' );
        }
    
        /*
         * ================================
         * This URI would look a tad insane
         * and a bit confusing for our poor
         * user to decypher
         * ================================
         */
        public function searchyourfeelingsyouknowittobetrue() {
            return 'Noooooooooooooooooooooooo!';
        }
        
    }
```